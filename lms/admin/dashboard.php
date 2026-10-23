<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
requireAdmin();

$pageTitle = 'Dashboard';

// ---- Statistics ----
$totalBooks   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM books"))['c'];
$totalStudents= mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM students"))['c'];
$totalCategories = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM categories"))['c'];
$issuedCount  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM issued_books WHERE status='issued'"))['c'];
$overdueCount = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM issued_books WHERE status='issued' AND due_date < CURDATE()"))['c'];
$totalAvailable = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(available_copies) c FROM books"))['c'] ?? 0;
$pendingFines = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COALESCE(SUM(amount),0) c FROM fines WHERE paid=0"))['c'];
$pendingRequests = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM book_requests WHERE status='pending'"))['c'];

// ---- Recent issues ----
$recent = mysqli_query($conn, "
  SELECT ib.id, b.title, s.name, ib.issue_date, ib.due_date, ib.status
  FROM issued_books ib
  JOIN books b ON b.id = ib.book_id
  JOIN students s ON s.id = ib.student_id
  ORDER BY ib.id DESC LIMIT 6
");

include '../includes/admin_header.php';
?>

<h4 class="section-title"><i class="bi bi-speedometer2 me-2"></i>Dashboard Overview</h4>

<div class="row g-3 mb-4">
  <div class="col-6 col-md-4 col-xl-2">
    <div class="card stat-card bg-grad-blue p-3">
      <i class="bi bi-book stat-icon"></i>
      <div class="small">Total Books</div>
      <h2 class="mb-0"><?= $totalBooks ?></h2>
    </div>
  </div>
  <div class="col-6 col-md-4 col-xl-2">
    <div class="card stat-card bg-grad-green p-3">
      <i class="bi bi-check2-circle stat-icon"></i>
      <div class="small">Available Copies</div>
      <h2 class="mb-0"><?= $totalAvailable ?></h2>
    </div>
  </div>
  <div class="col-6 col-md-4 col-xl-2">
    <div class="card stat-card bg-grad-purple p-3">
      <i class="bi bi-people stat-icon"></i>
      <div class="small">Students</div>
      <h2 class="mb-0"><?= $totalStudents ?></h2>
    </div>
  </div>
  <div class="col-6 col-md-4 col-xl-2">
    <div class="card stat-card bg-grad-orange p-3">
      <i class="bi bi-journal-arrow-up stat-icon"></i>
      <div class="small">Issued Books</div>
      <h2 class="mb-0"><?= $issuedCount ?></h2>
    </div>
  </div>
  <div class="col-6 col-md-4 col-xl-2">
    <div class="card stat-card bg-grad-red p-3">
      <i class="bi bi-exclamation-triangle stat-icon"></i>
      <div class="small">Overdue Books</div>
      <h2 class="mb-0"><?= $overdueCount ?></h2>
    </div>
  </div>
  <div class="col-6 col-md-4 col-xl-2">
    <div class="card stat-card bg-grad-blue p-3">
      <i class="bi bi-cash-coin stat-icon"></i>
      <div class="small">Pending Fines</div>
      <h2 class="mb-0"><?= formatCurrency($pendingFines) ?></h2>
    </div>
  </div>
</div>

<div class="row g-3">
  <div class="col-lg-8">
    <div class="card p-3">
      <h6 class="fw-bold mb-3"><i class="bi bi-clock-history me-1"></i>Recent Issue Activity</h6>
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead><tr><th>Book</th><th>Student</th><th>Issue Date</th><th>Due Date</th><th>Status</th></tr></thead>
          <tbody>
          <?php if (mysqli_num_rows($recent) === 0): ?>
            <tr><td colspan="5" class="text-center text-muted py-3">No issue records yet.</td></tr>
          <?php endif; ?>
          <?php while ($r = mysqli_fetch_assoc($recent)): 
              $isOverdue = $r['status']==='issued' && strtotime($r['due_date']) < strtotime(date('Y-m-d'));
          ?>
            <tr>
              <td><?= htmlspecialchars($r['title']) ?></td>
              <td><?= htmlspecialchars($r['name']) ?></td>
              <td><?= formatDate($r['issue_date']) ?></td>
              <td><?= formatDate($r['due_date']) ?></td>
              <td>
                <?php if ($isOverdue): ?>
                  <span class="badge badge-status-overdue">Overdue</span>
                <?php elseif ($r['status']==='returned'): ?>
                  <span class="badge badge-status-returned">Returned</span>
                <?php else: ?>
                  <span class="badge badge-status-issued">Issued</span>
                <?php endif; ?>
              </td>
            </tr>
          <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div class="col-lg-4">
    <div class="card p-3">
      <h6 class="fw-bold mb-3"><i class="bi bi-lightning-charge me-1"></i>Quick Actions</h6>
      <div class="d-grid gap-2">
        <a href="requests.php" class="btn btn-outline-warning">
          <i class="bi bi-inbox me-1"></i>Pending Requests
          <?php if ($pendingRequests > 0): ?><span class="badge bg-danger ms-1"><?= $pendingRequests ?></span><?php endif; ?>
        </a>
        <a href="add_book.php" class="btn btn-outline-primary"><i class="bi bi-plus-circle me-1"></i>Add New Book</a>
        <a href="add_student.php" class="btn btn-outline-primary"><i class="bi bi-person-plus me-1"></i>Add New Student</a>
        <a href="issue_book.php" class="btn btn-outline-primary"><i class="bi bi-journal-arrow-up me-1"></i>Issue a Book</a>
        <a href="return_book.php" class="btn btn-outline-primary"><i class="bi bi-journal-arrow-down me-1"></i>Return a Book</a>
        <a href="reports.php" class="btn btn-outline-primary"><i class="bi bi-graph-up me-1"></i>View Reports</a>
      </div>
      <hr>
      <div class="small text-muted">
        <i class="bi bi-info-circle me-1"></i>Categories in system: <strong><?= $totalCategories ?></strong>
      </div>
    </div>
  </div>
</div>

<?php include '../includes/footer.php'; ?>
