<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
requireStudent();
$pageTitle = 'My Fines';
$studentId = $_SESSION['student_id'];

$fines = mysqli_query($conn, "
  SELECT f.*, b.title
  FROM fines f
  JOIN issued_books ib ON ib.id = f.issued_book_id
  JOIN books b ON b.id = ib.book_id
  WHERE f.student_id = $studentId
  ORDER BY f.id DESC
");

$totalPending = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COALESCE(SUM(amount),0) c FROM fines WHERE student_id=$studentId AND paid=0"))['c'];

include '../includes/student_header.php';
?>

<h4 class="section-title"><i class="bi bi-cash-coin me-2"></i>My Fines</h4>

<div class="card stat-card bg-grad-red p-3 mb-3" style="max-width:320px;">
  <i class="bi bi-exclamation-triangle stat-icon"></i>
  <div class="small">Total Pending Fine</div>
  <h2 class="mb-0"><?= formatCurrency($totalPending) ?></h2>
</div>

<div class="card p-3">
  <div class="table-responsive">
    <table class="table table-hover align-middle">
      <thead><tr><th>Book</th><th>Amount</th><th>Status</th><th>Paid Date</th></tr></thead>
      <tbody>
      <?php if (mysqli_num_rows($fines) === 0): ?>
        <tr><td colspan="4" class="text-center text-muted py-4">No fines on record. Keep returning books on time!</td></tr>
      <?php endif; ?>
      <?php while ($f = mysqli_fetch_assoc($fines)): ?>
        <tr>
          <td><?= htmlspecialchars($f['title']) ?></td>
          <td><?= formatCurrency($f['amount']) ?></td>
          <td>
            <?php if ($f['paid']): ?>
              <span class="badge bg-success">Paid</span>
            <?php else: ?>
              <span class="badge bg-danger">Unpaid</span>
            <?php endif; ?>
          </td>
          <td><?= formatDate($f['paid_date']) ?></td>
        </tr>
      <?php endwhile; ?>
      </tbody>
    </table>
  </div>
  <p class="text-muted small mt-2 mb-0"><i class="bi bi-info-circle me-1"></i>Please pay any outstanding fines at the library counter. Admin will mark them as paid.</p>
</div>

<?php include '../includes/footer.php'; ?>
