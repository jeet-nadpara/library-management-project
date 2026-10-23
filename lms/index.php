<?php
/**
 * Login Page
 * Handles login for both Admin and Student roles using a tab switcher.
 */
require_once 'config/db.php';
require_once 'includes/functions.php';

// If already logged in, redirect straight to the right dashboard
if (isset($_SESSION['admin_id'])) {
    redirect('admin/dashboard.php');
}
if (isset($_SESSION['student_id'])) {
    redirect('student/dashboard.php');
}

$error = '';
$activeTab = 'admin';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $role = $_POST['role'] ?? 'admin';
    $activeTab = $role;

    if ($role === 'admin') {
        $username = clean($conn, $_POST['username']);
        $password = $_POST['password'];

        $stmt = mysqli_prepare($conn, "SELECT id, username, password, full_name FROM admins WHERE username = ?");
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($row = mysqli_fetch_assoc($result)) {
            if (password_verify($password, $row['password'])) {
                $_SESSION['admin_id'] = $row['id'];
                $_SESSION['admin_name'] = $row['full_name'];
                redirect('admin/dashboard.php');
            } else {
                $error = 'Invalid username or password.';
            }
        } else {
            $error = 'Invalid username or password.';
        }
    } else {
        // Student login
        $email = clean($conn, $_POST['email']);
        $password = $_POST['password'];

        $stmt = mysqli_prepare($conn, "SELECT id, name, email, password, status FROM students WHERE email = ?");
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($row = mysqli_fetch_assoc($result)) {
            if ($row['status'] !== 'active') {
                $error = 'Your account has been deactivated. Please contact the library admin.';
            } elseif (password_verify($password, $row['password'])) {
                $_SESSION['student_id'] = $row['id'];
                $_SESSION['student_name'] = $row['name'];
                redirect('student/dashboard.php');
            } else {
                $error = 'Invalid email or password.';
            }
        } else {
            $error = 'Invalid email or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login - Library Management System</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="login-wrapper">
  <div class="card login-card">
    <div class="card-header">
      <i class="bi bi-mortarboard-fill"></i>
      <h4 class="mt-2 mb-0">Library Management System</h4>
      <small class="text-white-50">Sign in to continue</small>
    </div>
    <div class="card-body p-4">

      <ul class="nav nav-pills nav-justified mb-4" id="loginTabs">
        <li class="nav-item">
          <button class="nav-link <?= $activeTab=='admin'?'active':'' ?>" data-bs-toggle="pill" data-bs-target="#adminPane" type="button">
            <i class="bi bi-shield-lock me-1"></i>Admin
          </button>
        </li>
        <li class="nav-item">
          <button class="nav-link <?= $activeTab=='student'?'active':'' ?>" data-bs-toggle="pill" data-bs-target="#studentPane" type="button">
            <i class="bi bi-person me-1"></i>Student
          </button>
        </li>
      </ul>

      <?php if ($error): ?>
        <div class="alert alert-danger py-2"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <div class="tab-content">
        <!-- Admin Login -->
        <div class="tab-pane fade <?= $activeTab=='admin'?'show active':'' ?>" id="adminPane">
          <form method="POST" class="needs-validation" novalidate>
            <input type="hidden" name="role" value="admin">
            <div class="mb-3">
              <label class="form-label">Username</label>
              <input type="text" name="username" class="form-control" required autofocus>
              <div class="invalid-feedback">Please enter your username.</div>
            </div>
            <div class="mb-3">
              <label class="form-label">Password</label>
              <input type="password" name="password" class="form-control" required>
              <div class="invalid-feedback">Please enter your password.</div>
            </div>
            <button type="submit" class="btn btn-primary w-100" style="background:var(--brand-primary); border:none;">
              <i class="bi bi-box-arrow-in-right me-1"></i>Login as Admin
            </button>
          </form>
        </div>

        <!-- Student Login -->
        <div class="tab-pane fade <?= $activeTab=='student'?'show active':'' ?>" id="studentPane">
          <form method="POST" class="needs-validation" novalidate>
            <input type="hidden" name="role" value="student">
            <div class="mb-3">
              <label class="form-label">Email</label>
              <input type="email" name="email" class="form-control" required>
              <div class="invalid-feedback">Please enter a valid email.</div>
            </div>
            <div class="mb-3">
              <label class="form-label">Password</label>
              <input type="password" name="password" class="form-control" required>
              <div class="invalid-feedback">Please enter your password.</div>
            </div>
            <button type="submit" class="btn btn-primary w-100" style="background:var(--brand-primary); border:none;">
              <i class="bi bi-box-arrow-in-right me-1"></i>Login as Student
            </button>
            <p class="text-center small mt-2 mb-0">New student? <a href="register.php">Create an account</a></p>
          </form>
        </div>
      </div>

    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/script.js"></script>
</body>
</html>
