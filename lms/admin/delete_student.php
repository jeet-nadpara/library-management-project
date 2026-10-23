<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
requireAdmin();

$id = (int)($_GET['id'] ?? 0);

$activeIssues = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM issued_books WHERE student_id=$id AND status='issued'"))['c'];

if ($activeIssues > 0) {
    setFlash('danger', 'Cannot delete this student: they currently have books issued.');
} else {
    mysqli_query($conn, "DELETE FROM students WHERE id = $id");
    setFlash('success', 'Student deleted successfully.');
}

redirect('students.php');
