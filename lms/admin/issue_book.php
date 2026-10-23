<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
requireAdmin();
$pageTitle = 'Issue Book';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bookId = (int)$_POST['book_id'];
    $studentId = (int)$_POST['student_id'];

    $book = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM books WHERE id=$bookId"));
    $student = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM students WHERE id=$studentId"));

    if (!$book) $errors[] = 'Please select a valid book.';
    if (!$student) $errors[] = 'Please select a valid student.';
    if ($book && $book['available_copies'] < 1) $errors[] = 'This book has no available copies right now.';

    // Prevent issuing the same book twice to the same student before it's returned
    if ($book && $student) {
        $dup = mysqli_query($conn, "SELECT id FROM issued_books WHERE book_id=$bookId AND student_id=$studentId AND status='issued'");
        if (mysqli_num_rows($dup) > 0) $errors[] = 'This student already has this book issued and not yet returned.';
    }

    if (empty($errors)) {
        $issueDate = date('Y-m-d');
        $dueDate = date('Y-m-d', strtotime("+" . ISSUE_PERIOD_DAYS . " days"));

        mysqli_begin_transaction($conn);
        try {
            $stmt = mysqli_prepare($conn, "INSERT INTO issued_books (book_id, student_id, issue_date, due_date, status) VALUES (?,?,?,?, 'issued')");
            mysqli_stmt_bind_param($stmt, "iiss", $bookId, $studentId, $issueDate, $dueDate);
            mysqli_stmt_execute($stmt);

            mysqli_query($conn, "UPDATE books SET available_copies = available_copies - 1 WHERE id = $bookId");

            mysqli_commit($conn);
            setFlash('success', "\"{$book['title']}\" issued to {$student['name']}. Due on " . formatDate($dueDate) . ".");
            redirect('issue_book.php');
        } catch (Exception $e) {
            mysqli_rollback($conn);
            $errors[] = 'Something went wrong. Please try again.';
        }
    }
}

$availableBooks = mysqli_query($conn, "SELECT * FROM books WHERE available_copies > 0 ORDER BY title");
$activeStudents = mysqli_query($conn, "SELECT * FROM students WHERE status='active' ORDER BY name");

// Currently issued list for reference
$currentIssues = mysqli_query($conn, "
  SELECT ib.id, b.title, s.name, ib.issue_date, ib.due_date
  FROM issued_books ib JOIN books b ON b.id=ib.book_id JOIN students s ON s.id=ib.student_id
  WHERE ib.status='issued' ORDER BY ib.id DESC LIMIT 8
");

include '../includes/admin_header.php';
?>

<h4 class="section-title"><i class="bi bi-journal-arrow-up me-2"></i>Issue Book</h4>

<div class="row g-3">
  <div class="col-lg-5">
    <div class="card p-4">
      <?php foreach ($errors as $e): ?>
        <div class="alert alert-danger py-2"><?= htmlspecialchars($e) ?></div>
      <?php endforeach; ?>

      <form method="POST" class="needs-validation" novalidate>
        <div class="mb-3">
          <label class="form-label">Select Book *</label>
          <select name="book_id" class="form-select" required>
            <option value="">-- Choose a book --</option>
            <?php while ($b = mysqli_fetch_assoc($availableBooks)): ?>
              <option value="<?= $b['id'] ?>"><?= htmlspecialchars($b['title']) ?> (<?= $b['available_copies'] ?> available)</option>
            <?php endwhile; ?>
          </select>
          <div class="invalid-feedback">Please select a book.</div>
        </div>
        <div class="mb-3">
          <label class="form-label">Select Student *</label>
          <select name="student_id" class="form-select" required>
            <option value="">-- Choose a student --</option>
            <?php while ($s = mysqli_fetch_assoc($activeStudents)): ?>
              <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['name']) ?> (<?= htmlspecialchars($s['student_code']) ?>)</option>
            <?php endwhile; ?>
          </select>
          <div class="invalid-feedback">Please select a student.</div>
        </div>
        <div class="alert alert-info small">
          <i class="bi bi-info-circle me-1"></i>Issue date will be today. Due date will automatically be set to <strong><?= ISSUE_PERIOD_DAYS ?> days</strong> from today.
          A fine of <strong><?= formatCurrency(FINE_PER_DAY) ?>/day</strong> applies after the due date.
        </div>
        <button type="submit" class="btn btn-primary w-100"><i class="bi bi-journal-arrow-up me-1"></i>Issue Book</button>
      </form>
    </div>
  </div>

  <div class="col-lg-7">
    <div class="card p-3">
      <h6 class="fw-bold mb-3">Recently Issued (currently out)</h6>
      <div class="table-responsive">
        <table class="table table-sm table-hover align-middle mb-0">
          <thead><tr><th>Book</th><th>Student</th><th>Issued</th><th>Due</th></tr></thead>
          <tbody>
          <?php if (mysqli_num_rows($currentIssues) === 0): ?>
            <tr><td colspan="4" class="text-center text-muted py-3">No books currently issued.</td></tr>
          <?php endif; ?>
          <?php while ($r = mysqli_fetch_assoc($currentIssues)): ?>
            <tr>
              <td><?= htmlspecialchars($r['title']) ?></td>
              <td><?= htmlspecialchars($r['name']) ?></td>
              <td><?= formatDate($r['issue_date']) ?></td>
              <td><?= formatDate($r['due_date']) ?></td>
            </tr>
          <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php include '../includes/footer.php'; ?>
