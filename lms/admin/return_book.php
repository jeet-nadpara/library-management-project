<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
requireAdmin();
$pageTitle = 'Return Book';

$message = '';

// Handle the return action
if (isset($_GET['return_id'])) {
    $issueId = (int)$_GET['return_id'];
    $issue = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM issued_books WHERE id=$issueId AND status='issued'"));

    if ($issue) {
        $returnDate = date('Y-m-d');
        $fine = calculateFine($issue['due_date'], $returnDate);

        mysqli_begin_transaction($conn);
        try {
            $stmt = mysqli_prepare($conn, "UPDATE issued_books SET return_date=?, status='returned', fine_amount=? WHERE id=?");
            mysqli_stmt_bind_param($stmt, "sdi", $returnDate, $fine, $issueId);
            mysqli_stmt_execute($stmt);

            mysqli_query($conn, "UPDATE books SET available_copies = available_copies + 1 WHERE id = {$issue['book_id']}");

            // If overdue, log a fine record
            if ($fine > 0) {
                $stmt2 = mysqli_prepare($conn, "INSERT INTO fines (issued_book_id, student_id, amount, paid) VALUES (?,?,?,0)");
                mysqli_stmt_bind_param($stmt2, "iid", $issueId, $issue['student_id'], $fine);
                mysqli_stmt_execute($stmt2);
            }

            mysqli_commit($conn);
            $msg = $fine > 0
                ? "Book returned. A fine of " . formatCurrency($fine) . " has been recorded for late return."
                : "Book returned on time. No fine applied.";
            setFlash($fine > 0 ? 'warning' : 'success', $msg);
            redirect('return_book.php');
        } catch (Exception $e) {
            mysqli_rollback($conn);
            setFlash('danger', 'Something went wrong while processing the return.');
            redirect('return_book.php');
        }
    } else {
        setFlash('danger', 'Invalid or already-returned record.');
        redirect('return_book.php');
    }
}

$search = isset($_GET['search']) ? clean($conn, $_GET['search']) : '';
$sql = "
  SELECT ib.*, b.title, b.book_code, s.name AS student_name, s.student_code
  FROM issued_books ib
  JOIN books b ON b.id = ib.book_id
  JOIN students s ON s.id = ib.student_id
  WHERE ib.status = 'issued'
";
if ($search !== '') {
    $sql .= " AND (b.title LIKE '%$search%' OR s.name LIKE '%$search%' OR s.student_code LIKE '%$search%')";
}
$sql .= " ORDER BY ib.due_date ASC";
$issued = mysqli_query($conn, $sql);

include '../includes/admin_header.php';
?>

<h4 class="section-title"><i class="bi bi-journal-arrow-down me-2"></i>Return Book</h4>

<div class="card p-3 mb-3">
  <form method="GET" class="row g-2">
    <div class="col-md-10">
      <input type="text" name="search" class="form-control" placeholder="Search by book title, student name or code..." value="<?= htmlspecialchars($search) ?>">
    </div>
    <div class="col-md-2 d-grid">
      <button type="submit" class="btn btn-secondary"><i class="bi bi-search me-1"></i>Search</button>
    </div>
  </form>
</div>

<div class="card p-3">
  <div class="table-responsive">
    <table class="table table-hover align-middle">
      <thead><tr><th>Book</th><th>Student</th><th>Issue Date</th><th>Due Date</th><th>Status</th><th>Est. Fine</th><th>Action</th></tr></thead>
      <tbody>
      <?php if (mysqli_num_rows($issued) === 0): ?>
        <tr><td colspan="7" class="text-center text-muted py-4">No books currently issued.</td></tr>
      <?php endif; ?>
      <?php while ($r = mysqli_fetch_assoc($issued)):
          $overdueDays = daysOverdue($r['due_date']);
          $estFine = calculateFine($r['due_date']);
      ?>
        <tr>
          <td><?= htmlspecialchars($r['title']) ?> <span class="text-muted small">(<?= htmlspecialchars($r['book_code']) ?>)</span></td>
          <td><?= htmlspecialchars($r['student_name']) ?> <span class="text-muted small">(<?= htmlspecialchars($r['student_code']) ?>)</span></td>
          <td><?= formatDate($r['issue_date']) ?></td>
          <td><?= formatDate($r['due_date']) ?></td>
          <td>
            <?php if ($overdueDays > 0): ?>
              <span class="badge badge-status-overdue"><?= $overdueDays ?> day(s) overdue</span>
            <?php else: ?>
              <span class="badge badge-status-issued">On time</span>
            <?php endif; ?>
          </td>
          <td><?= $estFine > 0 ? formatCurrency($estFine) : '-' ?></td>
          <td>
            <a href="?return_id=<?= $r['id'] ?>" class="btn btn-sm btn-primary confirm-delete" data-confirm="Confirm this book has been returned?">
              <i class="bi bi-check2-circle me-1"></i>Mark Returned
            </a>
          </td>
        </tr>
      <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include '../includes/footer.php'; ?>
