<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
requireStudent();
$pageTitle = 'Search Books';
$studentId = $_SESSION['student_id'];

// Handle "Request Issue" button
if (isset($_GET['request_book'])) {
    $bookId = (int)$_GET['request_book'];
    $book = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM books WHERE id=$bookId"));

    if (!$book || $book['available_copies'] < 1) {
        setFlash('danger', 'This book is currently unavailable.');
    } else {
        $already = mysqli_query($conn, "SELECT id FROM book_requests WHERE book_id=$bookId AND student_id=$studentId AND status='pending'");
        $alreadyIssued = mysqli_query($conn, "SELECT id FROM issued_books WHERE book_id=$bookId AND student_id=$studentId AND status='issued'");
        if (mysqli_num_rows($already) > 0) {
            setFlash('warning', 'You already have a pending request for this book.');
        } elseif (mysqli_num_rows($alreadyIssued) > 0) {
            setFlash('warning', 'You already have this book issued.');
        } else {
            $today = date('Y-m-d');
            $stmt = mysqli_prepare($conn, "INSERT INTO book_requests (book_id, student_id, request_date, status) VALUES (?,?,?, 'pending')");
            mysqli_stmt_bind_param($stmt, "iis", $bookId, $studentId, $today);
            mysqli_stmt_execute($stmt);
            setFlash('success', "Issue request submitted for \"{$book['title']}\". Please wait for admin approval.");
        }
    }
    redirect('search_books.php');
}

$search = isset($_GET['search']) ? clean($conn, $_GET['search']) : '';
$categoryFilter = isset($_GET['category']) ? (int)$_GET['category'] : 0;

$sql = "SELECT b.*, c.category_name FROM books b LEFT JOIN categories c ON c.id = b.category_id WHERE 1=1";
if ($search !== '') {
    $sql .= " AND (b.title LIKE '%$search%' OR b.author LIKE '%$search%' OR b.isbn LIKE '%$search%')";
}
if ($categoryFilter > 0) {
    $sql .= " AND b.category_id = $categoryFilter";
}
$sql .= " ORDER BY b.title";
$books = mysqli_query($conn, $sql);

$categories = mysqli_query($conn, "SELECT * FROM categories ORDER BY category_name");
$categoriesList = mysqli_fetch_all($categories, MYSQLI_ASSOC);

include '../includes/student_header.php';
?>

<h4 class="section-title"><i class="bi bi-search me-2"></i>Search Books</h4>

<div class="card p-3 mb-3">
  <form method="GET" class="row g-2">
    <div class="col-md-6">
      <input type="text" name="search" class="form-control" placeholder="Search by title, author or ISBN..." value="<?= htmlspecialchars($search) ?>">
    </div>
    <div class="col-md-4">
      <select name="category" class="form-select">
        <option value="0">All Categories</option>
        <?php foreach ($categoriesList as $cat): ?>
          <option value="<?= $cat['id'] ?>" <?= $categoryFilter==$cat['id']?'selected':'' ?>><?= htmlspecialchars($cat['category_name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-2 d-grid">
      <button type="submit" class="btn btn-secondary"><i class="bi bi-funnel me-1"></i>Filter</button>
    </div>
  </form>
</div>

<div class="row g-3">
  <?php if (mysqli_num_rows($books) === 0): ?>
    <p class="text-muted text-center py-4">No books matched your search.</p>
  <?php endif; ?>
  <?php while ($b = mysqli_fetch_assoc($books)): ?>
    <div class="col-md-6 col-lg-4">
      <div class="card h-100">
        <div class="book-cover-placeholder"><i class="bi bi-book"></i></div>
        <div class="card-body">
          <h6 class="fw-bold mb-1"><?= htmlspecialchars($b['title']) ?></h6>
          <p class="text-muted small mb-1">by <?= htmlspecialchars($b['author']) ?></p>
          <p class="mb-1"><span class="badge bg-light text-dark border"><?= htmlspecialchars($b['category_name'] ?? 'Uncategorized') ?></span></p>
          <p class="small mb-2">
            <?php if ($b['available_copies'] > 0): ?>
              <span class="text-success"><i class="bi bi-check-circle me-1"></i><?= $b['available_copies'] ?> available</span>
            <?php else: ?>
              <span class="text-danger"><i class="bi bi-x-circle me-1"></i>Not available</span>
            <?php endif; ?>
          </p>
          <a href="?request_book=<?= $b['id'] ?>" class="btn btn-sm btn-primary w-100 <?= $b['available_copies']<1?'disabled':'' ?>">
            <i class="bi bi-send me-1"></i>Request Issue
          </a>
        </div>
      </div>
    </div>
  <?php endwhile; ?>
</div>

<?php include '../includes/footer.php'; ?>
