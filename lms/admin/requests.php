<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
requireAdmin();
$pageTitle = 'Book Requests';

// ---- Approve request: creates an issued_books record (like Issue Book) ----
if (isset($_GET['approve'])) {
    $reqId = (int)$_GET['approve'];
    $req = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM book_requests WHERE id=$reqId AND status='pending'"));

    if ($req) {
        $book = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM books WHERE id={$req['book_id']}"));
        if (!$book || $book['available_copies'] < 1) {
            setFlash('danger', 'Cannot approve: no available copies left.');
        } else {
            mysqli_begin_transaction($conn);
            try {
                $issueDate = date('Y-m-d');
                $dueDate = date('Y-m-d', strtotime("+" . ISSUE_PERIOD_DAYS . " days"));
                $stmt = mysqli_prepare($conn, "INSERT INTO issued_books (book_id, student_id, issue_date, due_date, status) VALUES (?,?,?,?, 'issued')");
                mysqli_stmt_bind_param($stmt, "iiss", $req['book_id'], $req['student_id'], $issueDate, $dueDate);
                mysqli_stmt_execute($stmt);

                mysqli_query($conn, "UPDATE books SET available_copies = available_copies - 1 WHERE id = {$req['book_id']}");
                mysqli_query($conn, "UPDATE book_requests SET status='approved' WHERE id=$reqId");

                mysqli_commit($conn);
                setFlash('success', 'Request approved and book issued to the student.');
            } catch (Exception $e) {
                mysqli_rollback($conn);
                setFlash('danger', 'Something went wrong approving the request.');
            }
        }
    } else {
        setFlash('danger', 'Request not found or already processed.');
    }
    redirect('requests.php');
}

// ---- Reject request ----
if (isset($_GET['reject'])) {
    $reqId = (int)$_GET['reject'];
    mysqli_query($conn, "UPDATE book_requests SET status='rejected' WHERE id=$reqId AND status='pending'");
    setFlash('warning', 'Request rejected.');
    redirect('requests.php');
}

$requests = mysqli_query($conn, "
  SELECT br.*, b.title, b.available_copies, s.name AS student_name, s.student_code
  FROM book_requests br
  JOIN books b ON b.id = br.book_id
  JOIN students s ON s.id = br.student_id
  WHERE br.status = 'pending'
  ORDER BY br.id ASC
");

include '../includes/admin_header.php';
?>

<h4 class="section-title"><i class="bi bi-inbox me-2"></i>Pending Book Requests</h4>

<div class="card p-3">
  <div class="table-responsive">
    <table class="table table-hover align-middle">
      <thead><tr><th>Student</th><th>Book</th><th>Requested On</th><th>Available Copies</th><th>Actions</th></tr></thead>
      <tbody>
      <?php if (mysqli_num_rows($requests) === 0): ?>
        <tr><td colspan="5" class="text-center text-muted py-4">No pending requests.</td></tr>
      <?php endif; ?>
      <?php while ($r = mysqli_fetch_assoc($requests)): ?>
        <tr>
          <td><?= htmlspecialchars($r['student_name']) ?> <span class="text-muted small">(<?= htmlspecialchars($r['student_code']) ?>)</span></td>
          <td><?= htmlspecialchars($r['title']) ?></td>
          <td><?= formatDate($r['request_date']) ?></td>
          <td><?= $r['available_copies'] ?></td>
          <td class="text-nowrap">
            <a href="?approve=<?= $r['id'] ?>" class="btn btn-sm btn-success"><i class="bi bi-check2"></i> Approve</a>
            <a href="?reject=<?= $r['id'] ?>" class="btn btn-sm btn-outline-danger confirm-delete" data-confirm="Reject this request?"><i class="bi bi-x"></i> Reject</a>
          </td>
        </tr>
      <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include '../includes/footer.php'; ?>
