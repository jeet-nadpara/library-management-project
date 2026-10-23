<?php
/**
 * Database Connection
 * -----------------------------------------------------------
 * Update the credentials below to match your local MySQL / XAMPP
 * / WAMP setup, then import database/library_management.sql
 */

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'library_management');

// Create connection
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8mb4");

// Start session on every page that includes this file
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
