<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
requireAdmin();
$pageTitle = 'Fines';

// Mark a fine as paid
if (isset($_GET['pay'])) {
    $fineId = (int)$_GET['pay'];
    $stmt = mysqli_prepare($conn, "UPDATE fines SET paid=1, paid_date=? WHERE id=?");
    $today = date('Y-m-d');
    mysqli_stmt_bind_param($stmt, "si", $today, $fineId);
    mysqli_stmt_execute($stmt);
    setFlash('success', 'Fine marked as paid.');
    redirect('fines.php');
}

$filter = $_GET['filter'] ?? 'all'; // all | paid | unpaid

$sql = "
  SELECT f.*, s.name AS student_name, s.student_code, b.title
  FROM fines f
  JOIN students s ON s.id = f.student_id
  JOIN issued_books ib ON ib.id = f.issued_book_id
  JOIN books b ON b.id = ib.book_id
  WHERE 1=1
";
if ($filter === 'paid') $sql .= " AND f.paid = 1";
if ($filter === 'unpaid') $sql .= " AND f.paid = 0";
$sql .= " ORDER BY f.id DESC";
$fines = mysqli_query($conn, $sql);

$totalCollected = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COALESCE(SUM(amount),0) c FROM fines WHERE paid=1"))['c'];
$totalPending = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COALESCE(SUM(amount),0) c FROM fines WHERE paid=0"))['c'];

include '../includes/admin_header.php';
?>

<h4 class="section-title"><i class="bi bi-cash-coin me-2"></i>Fine Management</h4>

<div class="row g-3 mb-3">
  <div class="col-md-6">
    <div class="card stat-card bg-grad-green p-3">
      <i class="bi bi-check2-circle stat-icon"></i>
      <div class="small">Total Collected</div>
      <h2 class="mb-0"><?= formatCurrency($totalCollected) ?></h2>
    </div>
  </div>
  <div class="col-md-6">
    <div class="card stat-card bg-grad-red p-3">
      <i class="bi bi-exclamation-triangle stat-icon"></i>
      <div class="small">Pending Fines</div>
      <h2 class="mb-0"><?= formatCurrency($totalPending) ?></h2>
    </div>
  </div>
</div>

<div class="card p-3 mb-3">
  <div class="btn-group">
    <a href="?filter=all" class="btn btn-outline-secondary <?= $filter=='all'?'active':'' ?>">All</a>
    <a href="?filter=unpaid" class="btn btn-outline-secondary <?= $filter=='unpaid'?'active':'' ?>">Unpaid</a>
    <a href="?filter=paid" class="btn btn-outline-secondary <?= $filter=='paid'?'active':'' ?>">Paid</a>
  </div>
</div>

<div class="card p-3">
  <div class="table-responsive">
    <table class="table table-hover align-middle">
      <thead><tr><th>Student</th><th>Book</th><th>Amount</th><th>Status</th><th>Paid Date</th><th>Action</th></tr></thead>
      <tbody>
      <?php if (mysqli_num_rows($fines) === 0): ?>
        <tr><td colspan="6" class="text-center text-muted py-4">No fine records found.</td></tr>
      <?php endif; ?>
      <?php while ($f = mysqli_fetch_assoc($fines)): ?>
        <tr>
          <td><?= htmlspecialchars($f['student_name']) ?> <span class="text-muted small">(<?= htmlspecialchars($f['student_code']) ?>)</span></td>
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
          <td>
            <?php if (!$f['paid']): ?>
              <a href="?pay=<?= $f['id'] ?>&filter=<?= $filter ?>" class="btn btn-sm btn-success confirm-delete" data-confirm="Mark this fine as paid?">
                <i class="bi bi-cash me-1"></i>Mark Paid
              </a>
            <?php else: ?>
              <span class="text-muted small">—</span>
            <?php endif; ?>
          </td>
        </tr>
      <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include '../includes/footer.php'; ?>
