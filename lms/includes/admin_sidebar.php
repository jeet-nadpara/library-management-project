<?php $current = basename($_SERVER['PHP_SELF']); ?>
<aside class="sidebar bg-dark" id="sidebar">
  <ul class="nav flex-column p-2">
    <li class="nav-item">
      <a class="nav-link <?= $current=='dashboard.php'?'active':'' ?>" href="dashboard.php">
        <i class="bi bi-speedometer2 me-2"></i>Dashboard
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link <?= $current=='books.php'?'active':'' ?>" href="books.php">
        <i class="bi bi-book me-2"></i>Books
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link <?= $current=='categories.php'?'active':'' ?>" href="categories.php">
        <i class="bi bi-tags me-2"></i>Categories
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link <?= $current=='students.php'?'active':'' ?>" href="students.php">
        <i class="bi bi-people me-2"></i>Students
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link <?= $current=='requests.php'?'active':'' ?>" href="requests.php">
        <i class="bi bi-inbox me-2"></i>Book Requests
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link <?= $current=='issue_book.php'?'active':'' ?>" href="issue_book.php">
        <i class="bi bi-journal-arrow-up me-2"></i>Issue Book
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link <?= $current=='return_book.php'?'active':'' ?>" href="return_book.php">
        <i class="bi bi-journal-arrow-down me-2"></i>Return Book
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link <?= $current=='fines.php'?'active':'' ?>" href="fines.php">
        <i class="bi bi-cash-coin me-2"></i>Fines
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link <?= $current=='reports.php'?'active':'' ?>" href="reports.php">
        <i class="bi bi-graph-up me-2"></i>Reports
      </a>
    </li>
  </ul>
</aside>
