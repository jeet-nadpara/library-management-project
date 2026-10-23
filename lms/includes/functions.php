<?php
/**
 * Common Helper Functions
 * Shared across admin and student modules.
 */

// Fine rate per day after due date
define('FINE_PER_DAY', 5);      // ₹5 per day
define('ISSUE_PERIOD_DAYS', 14); // Due date = issue date + 14 days

/**
 * Sanitize user input
 */
function clean($conn, $data) {
    return mysqli_real_escape_string($conn, trim(htmlspecialchars($data)));
}

/**
 * Redirect helper
 */
function redirect($url) {
    header("Location: $url");
    exit();
}

/**
 * Require an admin to be logged in. Redirects to login page otherwise.
 */
function requireAdmin() {
    if (!isset($_SESSION['admin_id'])) {
        redirect('../index.php');
    }
}

/**
 * Require a student to be logged in. Redirects to login page otherwise.
 */
function requireStudent() {
    if (!isset($_SESSION['student_id'])) {
        redirect('../index.php');
    }
}

/**
 * Generate the next auto Book ID, e.g. BK0001, BK0002 ...
 */
function generateBookCode($conn) {
    $result = mysqli_query($conn, "SELECT id FROM books ORDER BY id DESC LIMIT 1");
    $nextId = 1;
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $nextId = $row['id'] + 1;
    }
    return 'BK' . str_pad($nextId, 4, '0', STR_PAD_LEFT);
}

/**
 * Generate the next auto Student Code, e.g. STU0001, STU0002 ...
 */
function generateStudentCode($conn) {
    $result = mysqli_query($conn, "SELECT id FROM students ORDER BY id DESC LIMIT 1");
    $nextId = 1;
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $nextId = $row['id'] + 1;
    }
    return 'STU' . str_pad($nextId, 4, '0', STR_PAD_LEFT);
}

/**
 * Calculate fine (in currency units) for a given due date, compared to
 * either the actual return date (if already returned) or today.
 * Returns 0 if not overdue.
 */
function calculateFine($dueDate, $returnDate = null) {
    $due = new DateTime($dueDate);
    $end = $returnDate ? new DateTime($returnDate) : new DateTime(date('Y-m-d'));

    if ($end <= $due) {
        return 0;
    }
    $diff = $due->diff($end);
    $daysLate = (int) $diff->days;
    return $daysLate * FINE_PER_DAY;
}

/**
 * Days overdue helper (0 if not overdue)
 */
function daysOverdue($dueDate) {
    $due = new DateTime($dueDate);
    $today = new DateTime(date('Y-m-d'));
    if ($today <= $due) return 0;
    return (int) $due->diff($today)->days;
}

/**
 * Format currency for display (Indian Rupee)
 */
function formatCurrency($amount) {
    return '₹' . number_format((float)$amount, 2);
}

/**
 * Format a date nicely for display
 */
function formatDate($date) {
    if (!$date) return '-';
    return date('d M Y', strtotime($date));
}

/**
 * Flash message helpers (simple session based)
 */
function setFlash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}
