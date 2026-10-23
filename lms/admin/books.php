<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
requireAdmin();
$pageTitle = 'Manage Books';

// ---- Search & Filter ----
$search = isset($_GET['search']) ? clean($conn, $_GET['search']) : '';
$categoryFilter = isset($_GET['category']) ? (int)$_GET['category'] : 0;

$sql = "SELECT b.*, c.category_name FROM books b LEFT JOIN categories c ON c.id = b.category_id WHERE 1=1";
if ($search !== '') {
    $sql .= " AND (b.title LIKE '%$search%' OR b.author LIKE '%$search%' OR b.book_code LIKE '%$search%' OR b.isbn LIKE '%$search%')";
}
if ($categoryFilter > 0) {
    $sql .= " AND b.category_id = $categoryFilter";
}
$sql .= " ORDER BY b.id DESC";
$books = mysqli_query($conn, $sql);

$categories = mysqli_query($conn, "SELECT * FROM categories ORDER BY category_name");
$categoriesList = mysqli_fetch_all($categories, MYSQLI_ASSOC);

include '../includes/admin_header.php';
?>

<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
  <h4 class="section-title mb-0"><i class="bi bi-book me-2"></i>Manage Books</h4>
  <a href="add_book.php" class="btn btn-primary"><i class="bi bi-plus-circle me-1"></i>Add New Book</a>
</div>

<div class="card p-3 mb-3">
  <form method="GET" class="row g-2">
    <div class="col-md-6">
      <input type="text" name="search" class="form-control" placeholder="Search by title, author, code or ISBN..." value="<?= htmlspecialchars($search) ?>">
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

<div class="card p-3">
  <div class="table-responsive">
    <table class="table table-hover align-middle">
      <thead>
        <tr>
          <th>Code</th><th>Title</th><th>Author</th><th>Category</th>
          <th>Total</th><th>Available</th><th>Price</th><th>Actions</th>
        </tr>
      </thead>
      <tbody>
      <?php if (mysqli_num_rows($books) === 0): ?>
        <tr><td colspan="8" class="text-center text-muted py-4">No books found.</td></tr>
      <?php endif; ?>
      <?php while ($b = mysqli_fetch_assoc($books)): ?>
        <tr>
          <td><span class="badge bg-secondary"><?= htmlspecialchars($b['book_code']) ?></span></td>
          <td><?= htmlspecialchars($b['title']) ?></td>
          <td><?= htmlspecialchars($b['author']) ?></td>
          <td><?= htmlspecialchars($b['category_name'] ?? 'Uncategorized') ?></td>
          <td><?= $b['total_copies'] ?></td>
          <td>
            <?php if ($b['available_copies'] > 0): ?>
              <span class="badge bg-success"><?= $b['available_copies'] ?> available</span>
            <?php else: ?>
              <span class="badge bg-danger">Out of stock</span>
            <?php endif; ?>
          </td>
          <td><?= formatCurrency($b['price']) ?></td>
          <td class="text-nowrap">
            <a href="edit_book.php?id=<?= $b['id'] ?>" class="btn btn-sm btn-outline-primary" title="Edit"><i class="bi bi-pencil"></i></a>
            <a href="delete_book.php?id=<?= $b['id'] ?>" class="btn btn-sm btn-outline-danger confirm-delete" data-confirm="Delete this book?" title="Delete"><i class="bi bi-trash"></i></a>
          </td>
        </tr>
      <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include '../includes/footer.php'; ?>
