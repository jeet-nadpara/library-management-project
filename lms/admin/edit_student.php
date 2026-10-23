<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
requireAdmin();
$pageTitle = 'Edit Student';

$id = (int)($_GET['id'] ?? 0);
$student = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM students WHERE id=$id"));
if (!$student) {
    setFlash('danger', 'Student not found.');
    redirect('students.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = clean($conn, $_POST['name']);
    $email = clean($conn, $_POST['email']);
    $phone = clean($conn, $_POST['phone']);
    $address = clean($conn, $_POST['address']);
    $status = $_POST['status'] === 'inactive' ? 'inactive' : 'active';
    $newPassword = $_POST['password'];

    if ($name === '') $errors[] = 'Name is required.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'A valid email is required.';

    $dupCheck = mysqli_query($conn, "SELECT id FROM students WHERE email='$email' AND id != $id");
    if (mysqli_num_rows($dupCheck) > 0) $errors[] = 'Another student already uses this email.';

    if (empty($errors)) {
        if (!empty($newPassword)) {
            if (strlen($newPassword) < 6) {
                $errors[] = 'New password must be at least 6 characters.';
            } else {
                $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
                $stmt = mysqli_prepare($conn, "UPDATE students SET name=?, email=?, phone=?, address=?, status=?, password=? WHERE id=?");
                mysqli_stmt_bind_param($stmt, "ssssssi", $name, $email, $phone, $address, $status, $hashed, $id);
            }
        } else {
            $stmt = mysqli_prepare($conn, "UPDATE students SET name=?, email=?, phone=?, address=?, status=? WHERE id=?");
            mysqli_stmt_bind_param($stmt, "sssssi", $name, $email, $phone, $address, $status, $id);
        }

        if (empty($errors)) {
            mysqli_stmt_execute($stmt);
            setFlash('success', 'Student updated successfully.');
            redirect('students.php');
        }
    }

    $student = array_merge($student, $_POST);
}

include '../includes/admin_header.php';
?>

<h4 class="section-title"><i class="bi bi-pencil me-2"></i>Edit Student</h4>

<div class="card p-4" style="max-width:700px;">
  <?php foreach ($errors as $e): ?>
    <div class="alert alert-danger py-2"><?= htmlspecialchars($e) ?></div>
  <?php endforeach; ?>

  <form method="POST" class="needs-validation" novalidate>
    <div class="mb-3">
      <label class="form-label">Student Code</label>
      <input type="text" class="form-control" value="<?= htmlspecialchars($student['student_code']) ?>" disabled>
    </div>
    <div class="mb-3">
      <label class="form-label">Full Name *</label>
      <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($student['name']) ?>">
    </div>
    <div class="row">
      <div class="col-md-6 mb-3">
        <label class="form-label">Email *</label>
        <input type="email" name="email" class="form-control" required value="<?= htmlspecialchars($student['email']) ?>">
      </div>
      <div class="col-md-6 mb-3">
        <label class="form-label">Phone</label>
        <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($student['phone']) ?>">
      </div>
    </div>
    <div class="mb-3">
      <label class="form-label">Address</label>
      <textarea name="address" class="form-control" rows="2"><?= htmlspecialchars($student['address']) ?></textarea>
    </div>
    <div class="row">
      <div class="col-md-6 mb-3">
        <label class="form-label">Status</label>
        <select name="status" class="form-select">
          <option value="active" <?= $student['status']==='active'?'selected':'' ?>>Active</option>
          <option value="inactive" <?= $student['status']==='inactive'?'selected':'' ?>>Inactive</option>
        </select>
      </div>
      <div class="col-md-6 mb-3">
        <label class="form-label">Reset Password</label>
        <input type="password" name="password" class="form-control" placeholder="Leave blank to keep current password">
      </div>
    </div>

    <div class="d-flex gap-2">
      <button type="submit" class="btn btn-primary"><i class="bi bi-check2-circle me-1"></i>Update Student</button>
      <a href="students.php" class="btn btn-outline-secondary">Cancel</a>
    </div>
  </form>
</div>

<?php include '../includes/footer.php'; ?>
