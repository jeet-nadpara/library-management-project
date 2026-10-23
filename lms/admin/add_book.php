<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
requireAdmin();
$pageTitle = 'Add Book';

$errors = [];
$bookCode = generateBookCode($conn); // Auto Book ID preview

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = clean($conn, $_POST['title']);
    $author = clean($conn, $_POST['author']);
    $isbn = clean($conn, $_POST['isbn']);
    $categoryId = (int)$_POST['category_id'];
    $totalCopies = (int)$_POST['total_copies'];
    $shelf = clean($conn, $_POST['shelf_location']);
    $price = (float)$_POST['price'];

    if ($title === '') $errors[] = 'Title is required.';
    if ($author === '') $errors[] = 'Author is required.';
    if ($totalCopies < 1) $errors[] = 'Total copies must be at least 1.';

    if (empty($errors)) {
        // Re-generate right before insert to avoid race duplicates
        $bookCode = generateBookCode($conn);
        $stmt = mysqli_prepare($conn, "INSERT INTO books (book_code, title, author, isbn, category_id, total_copies, available_copies, shelf_location, price) VALUES (?,?,?,?,?,?,?,?,?)");
        mysqli_stmt_bind_param($stmt, "ssssiiisd", $bookCode, $title, $author, $isbn, $categoryId, $totalCopies, $totalCopies, $shelf, $price);
        if (mysqli_stmt_execute($stmt)) {
            setFlash('success', "Book \"$title\" added successfully with code $bookCode.");
            redirect('books.php');
        } else {
            $errors[] = 'Database error: ' . mysqli_error($conn);
        }
    }
}

$categories = mysqli_query($conn, "SELECT * FROM categories ORDER BY category_name");
include '../includes/admin_header.php';
?>

<h4 class="section-title"><i class="bi bi-plus-circle me-2"></i>Add New Book</h4>

<div class="card p-4" style="max-width:700px;">
  <?php foreach ($errors as $e): ?>
    <div class="alert alert-danger py-2"><?= htmlspecialchars($e) ?></div>
  <?php endforeach; ?>

  <form method="POST" class="needs-validation" novalidate>
    <div class="mb-3">
      <label class="form-label">Book ID (auto-generated)</label>
      <input type="text" class="form-control" value="<?= htmlspecialchars($bookCode) ?>" disabled>
    </div>
    <div class="mb-3">
      <label class="form-label">Title *</label>
      <input type="text" name="title" class="form-control" required value="<?= htmlspecialchars($_POST['title'] ?? '') ?>">
      <div class="invalid-feedback">Please enter the book title.</div>
    </div>
    <div class="mb-3">
      <label class="form-label">Author *</label>
      <input type="text" name="author" class="form-control" required value="<?= htmlspecialchars($_POST['author'] ?? '') ?>">
      <div class="invalid-feedback">Please enter the author name.</div>
    </div>
    <div class="row">
      <div class="col-md-6 mb-3">
        <label class="form-label">ISBN</label>
        <input type="text" name="isbn" class="form-control" value="<?= htmlspecialchars($_POST['isbn'] ?? '') ?>">
      </div>
      <div class="col-md-6 mb-3">
        <label class="form-label">Category</label>
        <select name="category_id" class="form-select">
          <option value="">-- Uncategorized --</option>
          <?php while ($c = mysqli_fetch_assoc($categories)): ?>
            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['category_name']) ?></option>
          <?php endwhile; ?>
        </select>
      </div>
    </div>
    <div class="row">
      <div class="col-md-4 mb-3">
        <label class="form-label">Total Copies *</label>
        <input type="number" name="total_copies" min="1" class="form-control" required value="<?= htmlspecialchars($_POST['total_copies'] ?? 1) ?>">
        <div class="invalid-feedback">Enter at least 1 copy.</div>
      </div>
      <div class="col-md-4 mb-3">
        <label class="form-label">Shelf Location</label>
        <input type="text" name="shelf_location" class="form-control" placeholder="e.g. A1-01" value="<?= htmlspecialchars($_POST['shelf_location'] ?? '') ?>">
      </div>
      <div class="col-md-4 mb-3">
        <label class="form-label">Price (₹)</label>
        <input type="number" step="0.01" name="price" class="form-control" value="<?= htmlspecialchars($_POST['price'] ?? '0.00') ?>">
      </div>
    </div>

    <div class="d-flex gap-2">
      <button type="submit" class="btn btn-primary"><i class="bi bi-check2-circle me-1"></i>Save Book</button>
      <a href="books.php" class="btn btn-outline-secondary">Cancel</a>
    </div>
  </form>
</div>

<?php include '../includes/footer.php'; ?>
