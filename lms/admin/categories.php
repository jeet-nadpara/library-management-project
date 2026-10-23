<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
requireAdmin();
$pageTitle = 'Manage Categories';

$errors = [];

// ---- Handle Add ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $name = clean($conn, $_POST['category_name']);
    $desc = clean($conn, $_POST['description']);
    if ($name === '') {
        $errors[] = 'Category name is required.';
    } else {
        $check = mysqli_query($conn, "SELECT id FROM categories WHERE category_name = '$name'");
        if (mysqli_num_rows($check) > 0) {
            $errors[] = 'This category already exists.';
        } else {
            $stmt = mysqli_prepare($conn, "INSERT INTO categories (category_name, description) VALUES (?,?)");
            mysqli_stmt_bind_param($stmt, "ss", $name, $desc);
            mysqli_stmt_execute($stmt);
            setFlash('success', 'Category added successfully.');
            redirect('categories.php');
        }
    }
}

// ---- Handle Edit ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit') {
    $id = (int)$_POST['id'];
    $name = clean($conn, $_POST['category_name']);
    $desc = clean($conn, $_POST['description']);
    if ($name === '') {
        $errors[] = 'Category name is required.';
    } else {
        $stmt = mysqli_prepare($conn, "UPDATE categories SET category_name=?, description=? WHERE id=?");
        mysqli_stmt_bind_param($stmt, "ssi", $name, $desc, $id);
        mysqli_stmt_execute($stmt);
        setFlash('success', 'Category updated successfully.');
        redirect('categories.php');
    }
}

// ---- Handle Delete ----
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $inUse = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM books WHERE category_id=$id"))['c'];
    if ($inUse > 0) {
        setFlash('danger', 'Cannot delete: this category is assigned to existing books.');
    } else {
        mysqli_query($conn, "DELETE FROM categories WHERE id=$id");
        setFlash('success', 'Category deleted successfully.');
    }
    redirect('categories.php');
}

$categories = mysqli_query($conn, "
  SELECT c.*, COUNT(b.id) AS book_count
  FROM categories c LEFT JOIN books b ON b.category_id = c.id
  GROUP BY c.id ORDER BY c.category_name
");
$categoriesList = mysqli_fetch_all($categories, MYSQLI_ASSOC);

include '../includes/admin_header.php';
?>

<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
  <h4 class="section-title mb-0"><i class="bi bi-tags me-2"></i>Manage Categories</h4>
  <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
    <i class="bi bi-plus-circle me-1"></i>Add Category
  </button>
</div>

<?php foreach ($errors as $e): ?>
  <div class="alert alert-danger py-2"><?= htmlspecialchars($e) ?></div>
<?php endforeach; ?>

<div class="card p-3">
  <div class="table-responsive">
    <table class="table table-hover align-middle">
      <thead><tr><th>Category</th><th>Description</th><th># Books</th><th>Actions</th></tr></thead>
      <tbody>
      <?php if (empty($categoriesList)): ?>
        <tr><td colspan="4" class="text-center text-muted py-4">No categories yet.</td></tr>
      <?php endif; ?>
      <?php foreach ($categoriesList as $c): ?>
        <tr>
          <td><?= htmlspecialchars($c['category_name']) ?></td>
          <td><?= htmlspecialchars($c['description']) ?></td>
          <td><span class="badge bg-secondary"><?= $c['book_count'] ?></span></td>
          <td class="text-nowrap">
            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editCategoryModal<?= $c['id'] ?>"><i class="bi bi-pencil"></i></button>
            <a href="?delete=<?= $c['id'] ?>" class="btn btn-sm btn-outline-danger confirm-delete" data-confirm="Delete this category?"><i class="bi bi-trash"></i></a>
          </td>
        </tr>

        <!-- Edit Modal -->
        <div class="modal fade" id="editCategoryModal<?= $c['id'] ?>" tabindex="-1">
          <div class="modal-dialog">
            <div class="modal-content">
              <form method="POST">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" value="<?= $c['id'] ?>">
                <div class="modal-header">
                  <h5 class="modal-title">Edit Category</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                  <div class="mb-3">
                    <label class="form-label">Category Name</label>
                    <input type="text" name="category_name" class="form-control" required value="<?= htmlspecialchars($c['category_name']) ?>">
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="2"><?= htmlspecialchars($c['description']) ?></textarea>
                  </div>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                  <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
              </form>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addCategoryModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" class="needs-validation" novalidate>
        <input type="hidden" name="action" value="add">
        <div class="modal-header">
          <h5 class="modal-title">Add Category</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Category Name *</label>
            <input type="text" name="category_name" class="form-control" required>
            <div class="invalid-feedback">Please enter a category name.</div>
          </div>
          <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control" rows="2"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Add Category</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php include '../includes/footer.php'; ?>
