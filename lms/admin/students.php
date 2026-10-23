<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
requireAdmin();
$pageTitle = 'Manage Students';

$search = isset($_GET['search']) ? clean($conn, $_GET['search']) : '';

$sql = "SELECT * FROM students WHERE 1=1";
if ($search !== '') {
    $sql .= " AND (name LIKE '%$search%' OR email LIKE '%$search%' OR student_code LIKE '%$search%')";
}
$sql .= " ORDER BY id DESC";
$students = mysqli_query($conn, $sql);

include '../includes/admin_header.php';
?>

<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
  <h4 class="section-title mb-0"><i class="bi bi-people me-2"></i>Manage Students</h4>
  <a href="add_student.php" class="btn btn-primary"><i class="bi bi-person-plus me-1"></i>Add New Student</a>
</div>

<div class="card p-3 mb-3">
  <form method="GET" class="row g-2">
    <div class="col-md-10">
      <input type="text" name="search" class="form-control" placeholder="Search by name, email or student code..." value="<?= htmlspecialchars($search) ?>">
    </div>
    <div class="col-md-2 d-grid">
      <button type="submit" class="btn btn-secondary"><i class="bi bi-search me-1"></i>Search</button>
    </div>
  </form>
</div>

<div class="card p-3">
  <div class="table-responsive">
    <table class="table table-hover align-middle">
      <thead><tr><th>Code</th><th>Name</th><th>Email</th><th>Phone</th><th>Status</th><th>Actions</th></tr></thead>
      <tbody>
      <?php if (mysqli_num_rows($students) === 0): ?>
        <tr><td colspan="6" class="text-center text-muted py-4">No students found.</td></tr>
      <?php endif; ?>
      <?php while ($s = mysqli_fetch_assoc($students)): ?>
        <tr>
          <td><span class="badge bg-secondary"><?= htmlspecialchars($s['student_code']) ?></span></td>
          <td><?= htmlspecialchars($s['name']) ?></td>
          <td><?= htmlspecialchars($s['email']) ?></td>
          <td><?= htmlspecialchars($s['phone']) ?></td>
          <td>
            <?php if ($s['status']==='active'): ?>
              <span class="badge bg-success">Active</span>
            <?php else: ?>
              <span class="badge bg-secondary">Inactive</span>
            <?php endif; ?>
          </td>
          <td class="text-nowrap">
            <a href="edit_student.php?id=<?= $s['id'] ?>" class="btn btn-sm btn-outline-primary" title="Edit"><i class="bi bi-pencil"></i></a>
            <a href="delete_student.php?id=<?= $s['id'] ?>" class="btn btn-sm btn-outline-danger confirm-delete" data-confirm="Delete this student?" title="Delete"><i class="bi bi-trash"></i></a>
          </td>
        </tr>
      <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include '../includes/footer.php'; ?>
