<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
requireStudent();
$pageTitle = 'My Issued Books';
$studentId = $_SESSION['student_id'];

$issued = mysqli_query($conn, "
  SELECT ib.*, b.title, b.author, b.book_code
  FROM issued_books ib JOIN books b ON b.id = ib.book_id
  WHERE ib.student_id = $studentId
  ORDER BY ib.id DESC
");

$requests = mysqli_query($conn, "
  SELECT br.*, b.title
  FROM book_requests br JOIN books b ON b.id = br.book_id
  WHERE br.student_id = $studentId AND br.status='pending'
  ORDER BY br.id DESC
");

include '../includes/student_header.php';
?>

<h4 class="section-title"><i class="bi bi-journal-bookmark me-2"></i>My Issued Books</h4>

<?php if (mysqli_num_rows($requests) > 0): ?>
<div class="card p-3 mb-3">
  <h6 class="fw-bold mb-2"><i class="bi bi-hourglass-split me-1"></i>Pending Requests</h6>
  <ul class="list-group list-group-flush">
    <?php while ($r = mysqli_fetch_assoc($requests)): ?>
      <li class="list-group-item d-flex justify-content-between">
        <?= htmlspecialchars($r['title']) ?>
        <span class="badge bg-warning text-dark">Awaiting admin approval</span>
      </li>
    <?php endwhile; ?>
  </ul>
</div>
<?php endif; ?>

<div class="card p-3">
  <div class="table-responsive">
    <table class="table table-hover align-middle">
      <thead><tr><th>Book</th><th>Issue Date</th><th>Due Date</th><th>Return Date</th><th>Status</th><th>Fine</th></tr></thead>
      <tbody>
      <?php if (mysqli_num_rows($issued) === 0): ?>
        <tr><td colspan="6" class="text-center text-muted py-4">No issue history yet. <a href="search_books.php">Search books</a> to get started.</td></tr>
      <?php endif; ?>
      <?php while ($b = mysqli_fetch_assoc($issued)):
          $overdue = $b['status']==='issued' ? daysOverdue($b['due_date']) : 0;
          $estFine = $b['status']==='issued' ? calculateFine($b['due_date']) : $b['fine_amount'];
      ?>
        <tr>
          <td><?= htmlspecialchars($b['title']) ?> <span class="text-muted small">(<?= htmlspecialchars($b['book_code']) ?>)</span></td>
          <td><?= formatDate($b['issue_date']) ?></td>
          <td><?= formatDate($b['due_date']) ?></td>
          <td><?= formatDate($b['return_date']) ?></td>
          <td>
            <?php if ($b['status']==='returned'): ?>
              <span class="badge badge-status-returned">Returned</span>
            <?php elseif ($overdue > 0): ?>
              <span class="badge badge-status-overdue">Overdue (<?= $overdue ?>d)</span>
            <?php else: ?>
              <span class="badge badge-status-issued">Issued</span>
            <?php endif; ?>
          </td>
          <td><?= $estFine > 0 ? formatCurrency($estFine) : '-' ?></td>
        </tr>
      <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include '../includes/footer.php'; ?>
