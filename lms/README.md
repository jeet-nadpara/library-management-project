# Library Management System

A full-stack Library Management System built with **HTML, CSS, Bootstrap 5, JavaScript, PHP (procedural + mysqli), and MySQL**.

## Features

- **Authentication** — separate Admin and Student login on one page (tabs), sessions, logout, plus **student self-registration** (`register.php`).
- **Dashboard** — Bootstrap stat cards for both roles (books, students, issued, overdue, fines).
- **Book Management (CRUD)** — auto-generated Book ID (`BK0001`, `BK0002`, …), category assignment, copies tracking.
- **Category Management** — add/edit/delete with in-use protection.
- **Student Management (CRUD)** — auto-generated Student Code (`STU0001`, …), active/inactive status, password reset.
- **Issue & Return** — admin issues directly, or approves student-submitted requests; due date = issue date + 14 days; availability auto-updates.
- **Fine Calculation** — ₹5/day after the due date, calculated automatically on return (or live estimate while overdue); admin can mark fines as paid.
- **Search & Filter** — books searchable by title/author/ISBN/code and filterable by category (both admin and student side); students/issues searchable in their respective admin pages.
- **Reports** — Issued / Returned / Overdue views, printable.
- **Responsive UI** — Bootstrap 5 + Bootstrap Icons, collapsible sidebar on mobile.

## Folder Structure

```
lms/
├── admin/              Admin-only pages (requires admin login)
├── student/            Student-only pages (requires student login)
├── includes/           Shared header/footer/sidebar/functions
├── css/style.css       Custom styles
├── js/script.js        Sidebar toggle, validation, delete confirms
├── config/db.php       Database connection
├── database/library_management.sql   Full schema + sample data
├── index.php           Login page (Admin / Student tabs)
├── register.php        Student self-registration
├── logout.php
└── README.md
```

## Passwords / Security

All passwords — the sample data in the SQL file **and** any account created through
the app — are stored as real bcrypt hashes (`password_hash()` / `password_verify()`
in PHP). The sample hashes in `database/library_management.sql` were generated with
the same bcrypt algorithm and cost factor (10) that PHP's `password_hash()` uses by
default, so the demo logins below work out of the box.

## Setup (XAMPP / WAMP / LAMP)

1. Copy the `lms` folder into your server's web root (e.g. `htdocs/lms`).
2. Create the database by importing `database/library_management.sql` via phpMyAdmin or:
   ```
   mysql -u root -p < database/library_management.sql
   ```
3. Open `config/db.php` and update `DB_HOST`, `DB_USER`, `DB_PASS` if needed (defaults match a stock XAMPP install).
4. Visit `http://localhost/lms/` in your browser.

## Demo Logins

| Role    | Username / Email       | Password    |
|---------|-------------------------|-------------|
| Admin   | `admin`                | `admin123`  |
| Student | `john@student.com`     | `student123`|
| Student | `emily@student.com`    | `student123`|
| Student | `michael@student.com`  | `student123`|

## Business Rules

- **Due date** = issue date + 14 days (configurable via `ISSUE_PERIOD_DAYS` in `includes/functions.php`).
- **Fine** = ₹5 per day late (configurable via `FINE_PER_DAY`), calculated from due date to return date (or today, if still outstanding).
- Book **available_copies** auto-decrements on issue/approval and auto-increments on return.
- Students can't be deleted while they have books issued; books can't be deleted while any copy is issued; categories can't be deleted while assigned to a book.
- Students submit **issue requests**; Admin reviews them under **Book Requests** and approves (issues the book) or rejects.

## Security Notes

- Passwords are hashed with PHP's `password_hash()` / verified with `password_verify()`.
- All queries use `mysqli_real_escape_string()` and/or prepared statements.
- Session-based route protection (`requireAdmin()` / `requireStudent()`) on every protected page.

This is a learning/demo-grade project — for production use, add CSRF tokens, rate limiting, and HTTPS enforcement.
