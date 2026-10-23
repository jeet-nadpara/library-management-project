<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
requireAdmin();
$pageTitle = 'Reports';

$reportType = $_GET['type'] ?? 'issued'; // issued | returned | overdue

$baseSql = "
  SELECT ib.*, b.title, b.book_code, s.name AS student_name, s.student_code
  FROM issued_books ib
  JOIN books b ON b.id = ib.book_id
  JOIN students s ON s.id = ib.student_id
";

switch ($reportType) {
    case 'returned':
        $sql = $baseSql . " WHERE ib.status='returned' ORDER BY ib.return_date DESC";
        break;
    case 'overdue':
        $sql = $baseSql . " WHERE ib.status='issued' AND ib.due_date < CURDATE() ORDER BY ib.due_date ASC";
        break;
    default:
        $reportType = 'issued';
        $sql = $baseSql . " WHERE ib.status='issued' ORDER BY ib.issue_date DESC";
}

$records = mysqli_query($conn, $sql);

include '../includes/admin_header.php';
?>

<h4 class="section-title"><i class="bi bi-graph-up me-2"></i>Reports</h4>

<div class="card p-3 mb-3">
  <div class="btn-group flex-wrap">
    <a href="?type=issued" class="btn btn-outline-secondary <?= $reportType=='issued'?'active':'' ?>"><i class="bi bi-journal-arrow-up me-1"></i>Currently Issued</a>
    <a href="?type=returned" class="btn btn-outline-secondary <?= $reportType=='returned'?'active':'' ?>"><i class="bi bi-journal-arrow-down me-1"></i>Returned</a>
    <a href="?type=overdue" class="btn btn-outline-secondary <?= $reportType=='overdue'?'active':'' ?>"><i class="bi bi-exclamation-triangle me-1"></i>Overdue</a>
  </div>
</div>

<div class="card p-3">
  <div class="d-flex justify-content-between align-items-center mb-2">
    <h6 class="fw-bold mb-0 text-capitalize"><?= htmlspecialchars($reportType) ?> Books Report</h6>
    <button class="btn btn-sm btn-outline-primary d-print-none" onclick="window.print()"><i class="bi bi-printer me-1"></i>Print</button>
  </div>
  <div class="table-responsive">
    <table class="table table-hover align-middle">
      <thead>
        <tr>
          <th>Book</th><th>Student</th><th>Issue Date</th><th>Due Date</th>
          <?php if ($reportType === 'returned'): ?><th>Return Date</th><th>Fine</th><?php endif; ?>
          <?php if ($reportType === 'overdue'): ?><th>Days Overdue</th><th>Est. Fine</th><?php endif; ?>
        </tr>
      </thead>
      <tbody>
      <?php if (mysqli_num_rows($records) === 0): ?>
        <tr><td colspan="6" class="text-center text-muted py-4">No records found for this report.</td></tr>
      <?php endif; ?>
      <?php while ($r = mysqli_fetch_assoc($records)): ?>
        <tr>
          <td><?= htmlspecialchars($r['title']) ?> <span class="text-muted small">(<?= htmlspecialchars($r['book_code']) ?>)</span></td>
          <td><?= htmlspecialchars($r['student_name']) ?> <span class="text-muted small">(<?= htmlspecialchars($r['student_code']) ?>)</span></td>
          <td><?= formatDate($r['issue_date']) ?></td>
          <td><?= formatDate($r['due_date']) ?></td>
          <?php if ($reportType === 'returned'): ?>
            <td><?= formatDate($r['return_date']) ?></td>
            <td><?= $r['fine_amount'] > 0 ? formatCurrency($r['fine_amount']) : '-' ?></td>
          <?php endif; ?>
          <?php if ($reportType === 'overdue'): ?>
            <td><span class="badge badge-status-overdue"><?= daysOverdue($r['due_date']) ?> days</span></td>
            <td><?= formatCurrency(calculateFine($r['due_date'])) ?></td>
          <?php endif; ?>
        </tr>
      <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include '../includes/footer.php'; ?>
