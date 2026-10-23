<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
requireStudent();
$pageTitle = 'Dashboard';
$studentId = $_SESSION['student_id'];

$issuedCount = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM issued_books WHERE student_id=$studentId AND status='issued'"))['c'];
$overdueCount = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM issued_books WHERE student_id=$studentId AND status='issued' AND due_date < CURDATE()"))['c'];
$returnedCount = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM issued_books WHERE student_id=$studentId AND status='returned'"))['c'];
$pendingFines = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COALESCE(SUM(amount),0) c FROM fines WHERE student_id=$studentId AND paid=0"))['c'];

$myBooks = mysqli_query($conn, "
  SELECT ib.*, b.title, b.author
  FROM issued_books ib JOIN books b ON b.id = ib.book_id
  WHERE ib.student_id=$studentId AND ib.status='issued'
  ORDER BY ib.due_date ASC
");

include '../includes/student_header.php';
?>

<h4 class="section-title"><i class="bi bi-speedometer2 me-2"></i>Welcome, <?= htmlspecialchars($_SESSION['student_name']) ?></h4>

<div class="row g-3 mb-4">
  <div class="col-6 col-md-3">
    <div class="card stat-card bg-grad-blue p-3">
      <i class="bi bi-journal-arrow-up stat-icon"></i>
      <div class="small">Books Issued</div>
      <h2 class="mb-0"><?= $issuedCount ?></h2>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card stat-card bg-grad-red p-3">
      <i class="bi bi-exclamation-triangle stat-icon"></i>
      <div class="small">Overdue</div>
      <h2 class="mb-0"><?= $overdueCount ?></h2>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card stat-card bg-grad-green p-3">
      <i class="bi bi-check2-circle stat-icon"></i>
      <div class="small">Books Returned</div>
      <h2 class="mb-0"><?= $returnedCount ?></h2>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card stat-card bg-grad-orange p-3">
      <i class="bi bi-cash-coin stat-icon"></i>
      <div class="small">Pending Fines</div>
      <h2 class="mb-0"><?= formatCurrency($pendingFines) ?></h2>
    </div>
  </div>
</div>

<div class="card p-3">
  <h6 class="fw-bold mb-3"><i class="bi bi-journal-bookmark me-1"></i>Currently Issued Books</h6>
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead><tr><th>Title</th><th>Author</th><th>Issue Date</th><th>Due Date</th><th>Status</th></tr></thead>
      <tbody>
      <?php if (mysqli_num_rows($myBooks) === 0): ?>
        <tr><td colspan="5" class="text-center text-muted py-3">You have no books issued right now. <a href="search_books.php">Browse the library</a>.</td></tr>
      <?php endif; ?>
      <?php while ($b = mysqli_fetch_assoc($myBooks)):
          $overdue = daysOverdue($b['due_date']);
      ?>
        <tr>
          <td><?= htmlspecialchars($b['title']) ?></td>
          <td><?= htmlspecialchars($b['author']) ?></td>
          <td><?= formatDate($b['issue_date']) ?></td>
          <td><?= formatDate($b['due_date']) ?></td>
          <td>
            <?php if ($overdue > 0): ?>
              <span class="badge badge-status-overdue"><?= $overdue ?> days overdue</span>
            <?php else: ?>
              <span class="badge badge-status-issued">On time</span>
            <?php endif; ?>
          </td>
        </tr>
      <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include '../includes/footer.php'; ?>
