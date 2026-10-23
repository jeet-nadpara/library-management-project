<?php
/**
 * Student Self-Registration
 * Lets a new student create their own account, then sends them to login.
 */
require_once 'config/db.php';
require_once 'includes/functions.php';

// Already logged in? send them onward instead of showing registration.
if (isset($_SESSION['admin_id'])) redirect('admin/dashboard.php');
if (isset($_SESSION['student_id'])) redirect('student/dashboard.php');

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = clean($conn, $_POST['name']);
    $email = clean($conn, $_POST['email']);
    $phone = clean($conn, $_POST['phone']);
    $address = clean($conn, $_POST['address']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];

    if ($name === '') $errors[] = 'Full name is required.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'A valid email is required.';
    if (strlen($password) < 6) $errors[] = 'Password must be at least 6 characters.';
    if ($password !== $confirmPassword) $errors[] = 'Passwords do not match.';

    if (empty($errors)) {
        $check = mysqli_query($conn, "SELECT id FROM students WHERE email='$email'");
        if (mysqli_num_rows($check) > 0) {
            $errors[] = 'An account with this email already exists. Please login instead.';
        } else {
            $studentCode = generateStudentCode($conn);
            $hashed = password_hash($password, PASSWORD_DEFAULT); // real bcrypt hash
            $stmt = mysqli_prepare($conn, "INSERT INTO students (student_code, name, email, phone, password, address) VALUES (?,?,?,?,?,?)");
            mysqli_stmt_bind_param($stmt, "ssssss", $studentCode, $name, $email, $phone, $hashed, $address);
            if (mysqli_stmt_execute($stmt)) {
                $success = true;
            } else {
                $errors[] = 'Database error: ' . mysqli_error($conn);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Student Registration - Library Management System</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="login-wrapper">
  <div class="card login-card" style="max-width:480px;">
    <div class="card-header">
      <i class="bi bi-person-plus-fill"></i>
      <h4 class="mt-2 mb-0">Student Registration</h4>
      <small class="text-white-50">Create your library account</small>
    </div>
    <div class="card-body p-4">

      <?php if ($success): ?>
        <div class="alert alert-success">
          <i class="bi bi-check2-circle me-1"></i>Account created successfully! You can now log in.
        </div>
        <a href="index.php" class="btn btn-primary w-100" style="background:var(--brand-primary); border:none;">
          <i class="bi bi-box-arrow-in-right me-1"></i>Go to Login
        </a>
      <?php else: ?>

        <?php foreach ($errors as $e): ?>
          <div class="alert alert-danger py-2"><?= htmlspecialchars($e) ?></div>
        <?php endforeach; ?>

        <form method="POST" class="needs-validation" novalidate>
          <div class="mb-3">
            <label class="form-label">Full Name *</label>
            <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
            <div class="invalid-feedback">Please enter your full name.</div>
          </div>
          <div class="mb-3">
            <label class="form-label">Email *</label>
            <input type="email" name="email" class="form-control" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            <div class="invalid-feedback">Please enter a valid email.</div>
          </div>
          <div class="mb-3">
            <label class="form-label">Phone</label>
            <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
          </div>
          <div class="mb-3">
            <label class="form-label">Address</label>
            <textarea name="address" class="form-control" rows="2"><?= htmlspecialchars($_POST['address'] ?? '') ?></textarea>
          </div>
          <div class="row">
            <div class="col-6 mb-3">
              <label class="form-label">Password *</label>
              <input type="password" name="password" class="form-control" required minlength="6">
              <div class="invalid-feedback">At least 6 characters.</div>
            </div>
            <div class="col-6 mb-3">
              <label class="form-label">Confirm Password *</label>
              <input type="password" name="confirm_password" class="form-control" required minlength="6">
              <div class="invalid-feedback">Please confirm your password.</div>
            </div>
          </div>
          <button type="submit" class="btn btn-primary w-100" style="background:var(--brand-primary); border:none;">
            <i class="bi bi-person-check me-1"></i>Create Account
          </button>
          <p class="text-center small mt-3 mb-0">Already have an account? <a href="index.php">Login here</a></p>
        </form>

      <?php endif; ?>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/script.js"></script>
</body>
</html>
