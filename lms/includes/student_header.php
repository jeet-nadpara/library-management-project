<?php
/**
 * Student Header - included at the top of every student page.
 */
$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' - ' : '' ?>Library Student Portal</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<link rel="stylesheet" href="../css/style.css">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark main-navbar sticky-top">
  <div class="container-fluid">
    <button class="btn btn-link text-white d-lg-none" id="sidebarToggle"><i class="bi bi-list fs-3"></i></button>
    <a class="navbar-brand fw-bold" href="dashboard.php"><i class="bi bi-mortarboard-fill me-2"></i>Library Portal</a>
    <div class="ms-auto d-flex align-items-center gap-3">
      <span class="text-white-50 d-none d-sm-inline"><i class="bi bi-person-circle me-1"></i><?= htmlspecialchars($_SESSION['student_name'] ?? 'Student') ?></span>
      <a href="../logout.php" class="btn btn-sm btn-outline-light"><i class="bi bi-box-arrow-right me-1"></i>Logout</a>
    </div>
  </div>
</nav>

<div class="d-flex">
  <?php include 'student_sidebar.php'; ?>
  <main class="flex-grow-1 p-3 p-md-4 main-content">
    <?php if ($flash): ?>
      <div class="alert alert-<?= $flash['type'] ?> alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($flash['message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php endif; ?>
