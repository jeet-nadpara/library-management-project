<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
requireAdmin();
$pageTitle = 'Edit Book';

$id = (int)($_GET['id'] ?? 0);
$result = mysqli_query($conn, "SELECT * FROM books WHERE id = $id");
$book = mysqli_fetch_assoc($result);

if (!$book) {
    setFlash('danger', 'Book not found.');
    redirect('books.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = clean($conn, $_POST['title']);
    $author = clean($conn, $_POST['author']);
    $isbn = clean($conn, $_POST['isbn']);
    $categoryId = (int)$_POST['category_id'];
    $totalCopies = (int)$_POST['total_copies'];
    $shelf = clean($conn, $_POST['shelf_location']);
    $price = (float)$_POST['price'];

    // Work out how many copies are currently issued so available_copies stays consistent
    $issuedNow = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM issued_books WHERE book_id=$id AND status='issued'"))['c'];

    if ($title === '') $errors[] = 'Title is required.';
    if ($author === '') $errors[] = 'Author is required.';
    if ($totalCopies < $issuedNow) $errors[] = "Total copies cannot be less than currently issued copies ($issuedNow).";

    if (empty($errors)) {
        $newAvailable = $totalCopies - $issuedNow;
        $stmt = mysqli_prepare($conn, "UPDATE books SET title=?, author=?, isbn=?, category_id=?, total_copies=?, available_copies=?, shelf_location=?, price=? WHERE id=?");
        mysqli_stmt_bind_param($stmt, "sssiiisdi", $title, $author, $isbn, $categoryId, $totalCopies, $newAvailable, $shelf, $price, $id);
        if (mysqli_stmt_execute($stmt)) {
            setFlash('success', 'Book updated successfully.');
            redirect('books.php');
        } else {
            $errors[] = 'Database error: ' . mysqli_error($conn);
        }
    }
    // Keep edited values on screen if there were errors
    $book = array_merge($book, $_POST);
}

$categories = mysqli_query($conn, "SELECT * FROM categories ORDER BY category_name");
include '../includes/admin_header.php';
?>

<h4 class="section-title"><i class="bi bi-pencil me-2"></i>Edit Book</h4>

<div class="card p-4" style="max-width:700px;">
  <?php foreach ($errors as $e): ?>
    <div class="alert alert-danger py-2"><?= htmlspecialchars($e) ?></div>
  <?php endforeach; ?>

  <form method="POST" class="needs-validation" novalidate>
    <div class="mb-3">
      <label class="form-label">Book ID</label>
      <input type="text" class="form-control" value="<?= htmlspecialchars($book['book_code']) ?>" disabled>
    </div>
    <div class="mb-3">
      <label class="form-label">Title *</label>
      <input type="text" name="title" class="form-control" required value="<?= htmlspecialchars($book['title']) ?>">
      <div class="invalid-feedback">Please enter the book title.</div>
    </div>
    <div class="mb-3">
      <label class="form-label">Author *</label>
      <input type="text" name="author" class="form-control" required value="<?= htmlspecialchars($book['author']) ?>">
      <div class="invalid-feedback">Please enter the author name.</div>
    </div>
    <div class="row">
      <div class="col-md-6 mb-3">
        <label class="form-label">ISBN</label>
        <input type="text" name="isbn" class="form-control" value="<?= htmlspecialchars($book['isbn']) ?>">
      </div>
      <div class="col-md-6 mb-3">
        <label class="form-label">Category</label>
        <select name="category_id" class="form-select">
          <option value="">-- Uncategorized --</option>
          <?php while ($c = mysqli_fetch_assoc($categories)): ?>
            <option value="<?= $c['id'] ?>" <?= $c['id']==$book['category_id']?'selected':'' ?>><?= htmlspecialchars($c['category_name']) ?></option>
          <?php endwhile; ?>
        </select>
      </div>
    </div>
    <div class="row">
      <div class="col-md-4 mb-3">
        <label class="form-label">Total Copies *</label>
        <input type="number" name="total_copies" min="1" class="form-control" required value="<?= htmlspecialchars($book['total_copies']) ?>">
      </div>
      <div class="col-md-4 mb-3">
        <label class="form-label">Shelf Location</label>
        <input type="text" name="shelf_location" class="form-control" value="<?= htmlspecialchars($book['shelf_location']) ?>">
      </div>
      <div class="col-md-4 mb-3">
        <label class="form-label">Price (₹)</label>
        <input type="number" step="0.01" name="price" class="form-control" value="<?= htmlspecialchars($book['price']) ?>">
      </div>
    </div>
    <div class="alert alert-info small">Currently available copies: <strong><?= $book['available_copies'] ?></strong> (auto-recalculated on save based on issued copies)</div>

    <div class="d-flex gap-2">
      <button type="submit" class="btn btn-primary"><i class="bi bi-check2-circle me-1"></i>Update Book</button>
      <a href="books.php" class="btn btn-outline-secondary">Cancel</a>
    </div>
  </form>
</div>

<?php include '../includes/footer.php'; ?>
