<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
requireAdmin();
$pageTitle = 'Add Student';

$errors = [];
$studentCode = generateStudentCode($conn);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = clean($conn, $_POST['name']);
    $email = clean($conn, $_POST['email']);
    $phone = clean($conn, $_POST['phone']);
    $address = clean($conn, $_POST['address']);
    $password = $_POST['password'];

    if ($name === '') $errors[] = 'Name is required.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'A valid email is required.';
    if (strlen($password) < 6) $errors[] = 'Password must be at least 6 characters.';

    if (empty($errors)) {
        $check = mysqli_query($conn, "SELECT id FROM students WHERE email='$email'");
        if (mysqli_num_rows($check) > 0) {
            $errors[] = 'A student with this email already exists.';
        } else {
            $studentCode = generateStudentCode($conn);
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = mysqli_prepare($conn, "INSERT INTO students (student_code, name, email, phone, password, address) VALUES (?,?,?,?,?,?)");
            mysqli_stmt_bind_param($stmt, "ssssss", $studentCode, $name, $email, $phone, $hashed, $address);
            if (mysqli_stmt_execute($stmt)) {
                setFlash('success', "Student \"$name\" added successfully with code $studentCode.");
                redirect('students.php');
            } else {
                $errors[] = 'Database error: ' . mysqli_error($conn);
            }
        }
    }
}

include '../includes/admin_header.php';
?>

<h4 class="section-title"><i class="bi bi-person-plus me-2"></i>Add New Student</h4>

<div class="card p-4" style="max-width:700px;">
  <?php foreach ($errors as $e): ?>
    <div class="alert alert-danger py-2"><?= htmlspecialchars($e) ?></div>
  <?php endforeach; ?>

  <form method="POST" class="needs-validation" novalidate>
    <div class="mb-3">
      <label class="form-label">Student Code (auto-generated)</label>
      <input type="text" class="form-control" value="<?= htmlspecialchars($studentCode) ?>" disabled>
    </div>
    <div class="mb-3">
      <label class="form-label">Full Name *</label>
      <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
      <div class="invalid-feedback">Please enter the student's name.</div>
    </div>
    <div class="row">
      <div class="col-md-6 mb-3">
        <label class="form-label">Email *</label>
        <input type="email" name="email" class="form-control" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
        <div class="invalid-feedback">Please enter a valid email.</div>
      </div>
      <div class="col-md-6 mb-3">
        <label class="form-label">Phone</label>
        <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
      </div>
    </div>
    <div class="mb-3">
      <label class="form-label">Address</label>
      <textarea name="address" class="form-control" rows="2"><?= htmlspecialchars($_POST['address'] ?? '') ?></textarea>
    </div>
    <div class="mb-3">
      <label class="form-label">Password *</label>
      <input type="password" name="password" class="form-control" required minlength="6">
      <div class="invalid-feedback">Password must be at least 6 characters.</div>
    </div>

    <div class="d-flex gap-2">
      <button type="submit" class="btn btn-primary"><i class="bi bi-check2-circle me-1"></i>Save Student</button>
      <a href="students.php" class="btn btn-outline-secondary">Cancel</a>
    </div>
  </form>
</div>

<?php include '../includes/footer.php'; ?>
