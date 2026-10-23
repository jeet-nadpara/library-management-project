<?php $current = basename($_SERVER['PHP_SELF']); ?>
<aside class="sidebar bg-dark" id="sidebar">
  <ul class="nav flex-column p-2">
    <li class="nav-item">
      <a class="nav-link <?= $current=='dashboard.php'?'active':'' ?>" href="dashboard.php">
        <i class="bi bi-speedometer2 me-2"></i>Dashboard
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link <?= $current=='search_books.php'?'active':'' ?>" href="search_books.php">
        <i class="bi bi-search me-2"></i>Search Books
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link <?= $current=='my_books.php'?'active':'' ?>" href="my_books.php">
        <i class="bi bi-journal-bookmark me-2"></i>My Issued Books
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link <?= $current=='my_fines.php'?'active':'' ?>" href="my_fines.php">
        <i class="bi bi-cash-coin me-2"></i>My Fines
      </a>
    </li>
  </ul>
</aside>
