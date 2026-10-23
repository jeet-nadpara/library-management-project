<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
requireAdmin();

$id = (int)($_GET['id'] ?? 0);

// Prevent deleting a book that currently has active issues
$activeIssues = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM issued_books WHERE book_id=$id AND status='issued'"))['c'];

if ($activeIssues > 0) {
    setFlash('danger', 'Cannot delete this book: it has copies currently issued to students.');
} else {
    mysqli_query($conn, "DELETE FROM books WHERE id = $id");
    setFlash('success', 'Book deleted successfully.');
}

redirect('books.php');
