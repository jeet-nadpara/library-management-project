-- ==========================================================
-- Library Management System - Database Schema
-- ==========================================================
-- Import this file into phpMyAdmin / MySQL to create the
-- complete database with sample data.
-- ==========================================================

CREATE DATABASE IF NOT EXISTS library_management;
USE library_management;

-- ----------------------------------------------------------
-- Table: admins
-- ----------------------------------------------------------
CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL, -- stored as password_hash()
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Default admin login -> username: admin | password: admin123
INSERT INTO admins (username, password, full_name, email) VALUES
('admin', '$2b$10$FSSYJvlXj4bmRMoDaqNXnefHAkYQJGGEuNFVh4EUYlav3YL1TOs1G', 'System Administrator', 'admin@library.com');
-- NOTE: The hash above is a real bcrypt hash of the plaintext password "admin123"
-- (generated with cost factor 10, the same default PHP's password_hash() uses).

-- ----------------------------------------------------------
-- Table: categories
-- ----------------------------------------------------------
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(100) NOT NULL UNIQUE,
    description VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO categories (category_name, description) VALUES
('Fiction', 'Novels and imaginative literature'),
('Science', 'Physics, chemistry, biology and general science'),
('Technology', 'Computer science, engineering and IT'),
('History', 'Historical books and biographies'),
('Mathematics', 'Pure and applied mathematics');

-- ----------------------------------------------------------
-- Table: students
-- ----------------------------------------------------------
CREATE TABLE students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_code VARCHAR(20) NOT NULL UNIQUE, -- e.g. STU0001
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20) DEFAULT NULL,
    password VARCHAR(255) NOT NULL, -- password_hash()
    address VARCHAR(255) DEFAULT NULL,
    status ENUM('active','inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Default student login -> email: john@student.com | password: student123
INSERT INTO students (student_code, name, email, phone, password, address) VALUES
('STU0001', 'John Carter', 'john@student.com', '9876543210', '$2b$10$Z2u8f1cSIABuyv7WkT7jTOdot9oRf.t0DJOH7dqZ/YyQz5OdzRSoS', 'Downtown, City'),
('STU0002', 'Emily Watson', 'emily@student.com', '9876500011', '$2b$10$Z2u8f1cSIABuyv7WkT7jTOdot9oRf.t0DJOH7dqZ/YyQz5OdzRSoS', 'Lakeside Street'),
('STU0003', 'Michael Lee', 'michael@student.com', '9876500022', '$2b$10$Z2u8f1cSIABuyv7WkT7jTOdot9oRf.t0DJOH7dqZ/YyQz5OdzRSoS', 'Hill View Road');
-- NOTE: The hash above is a real bcrypt hash of the plaintext password "student123"

-- ----------------------------------------------------------
-- Table: books
-- ----------------------------------------------------------
CREATE TABLE books (
    id INT AUTO_INCREMENT PRIMARY KEY,
    book_code VARCHAR(20) NOT NULL UNIQUE, -- Auto generated e.g. BK0001
    title VARCHAR(150) NOT NULL,
    author VARCHAR(100) NOT NULL,
    isbn VARCHAR(30) DEFAULT NULL,
    category_id INT DEFAULT NULL,
    total_copies INT NOT NULL DEFAULT 1,
    available_copies INT NOT NULL DEFAULT 1,
    shelf_location VARCHAR(50) DEFAULT NULL,
    price DECIMAL(10,2) DEFAULT 0.00,
    added_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
) ENGINE=InnoDB;

INSERT INTO books (book_code, title, author, isbn, category_id, total_copies, available_copies, shelf_location, price) VALUES
('BK0001', 'The Great Gatsby', 'F. Scott Fitzgerald', '9780743273565', 1, 5, 5, 'A1-01', 350.00),
('BK0002', 'A Brief History of Time', 'Stephen Hawking', '9780553380163', 2, 4, 4, 'B2-05', 480.00),
('BK0003', 'Clean Code', 'Robert C. Martin', '9780132350884', 3, 6, 6, 'C1-10', 650.00),
('BK0004', 'Sapiens', 'Yuval Noah Harari', '9780062316097', 4, 3, 3, 'D3-02', 550.00),
('BK0005', 'Introduction to Algorithms', 'Thomas H. Cormen', '9780262033848', 3, 4, 4, 'C1-12', 900.00),
('BK0006', 'The Theory of Everything', 'Stephen Hawking', '9781597775409', 2, 2, 2, 'B2-06', 400.00),
('BK0007', 'To Kill a Mockingbird', 'Harper Lee', '9780061120084', 1, 5, 5, 'A1-04', 300.00),
('BK0008', 'Calculus Made Easy', 'Silvanus P. Thompson', '9780312185480', 5, 3, 3, 'E1-01', 380.00);

-- ----------------------------------------------------------
-- Table: issued_books
-- ----------------------------------------------------------
CREATE TABLE issued_books (
    id INT AUTO_INCREMENT PRIMARY KEY,
    book_id INT NOT NULL,
    student_id INT NOT NULL,
    issue_date DATE NOT NULL,
    due_date DATE NOT NULL,
    return_date DATE DEFAULT NULL,
    status ENUM('issued','returned') NOT NULL DEFAULT 'issued',
    fine_amount DECIMAL(10,2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Sample: one overdue, one currently issued, one returned
INSERT INTO issued_books (book_id, student_id, issue_date, due_date, return_date, status, fine_amount) VALUES
(1, 1, DATE_SUB(CURDATE(), INTERVAL 20 DAY), DATE_SUB(CURDATE(), INTERVAL 6 DAY), NULL, 'issued', 0.00),
(3, 2, DATE_SUB(CURDATE(), INTERVAL 5 DAY), DATE_ADD(CURDATE(), INTERVAL 9 DAY), NULL, 'issued', 0.00),
(4, 3, DATE_SUB(CURDATE(), INTERVAL 30 DAY), DATE_SUB(CURDATE(), INTERVAL 16 DAY), DATE_SUB(CURDATE(), INTERVAL 10 DAY), 'returned', 30.00);

-- Reduce available copies for the two currently issued books above
UPDATE books SET available_copies = available_copies - 1 WHERE id IN (1, 3);

-- ----------------------------------------------------------
-- Table: fines
-- ----------------------------------------------------------
CREATE TABLE fines (
    id INT AUTO_INCREMENT PRIMARY KEY,
    issued_book_id INT NOT NULL,
    student_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    paid TINYINT(1) NOT NULL DEFAULT 0,
    paid_date DATE DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (issued_book_id) REFERENCES issued_books(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Sample fine records
INSERT INTO fines (issued_book_id, student_id, amount, paid, paid_date) VALUES
(3, 3, 30.00, 1, DATE_SUB(CURDATE(), INTERVAL 10 DAY));

-- ----------------------------------------------------------
-- Table: book_requests
-- Students submit a request; Admin approves (which issues the
-- book) or rejects it.
-- ----------------------------------------------------------
CREATE TABLE book_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    book_id INT NOT NULL,
    student_id INT NOT NULL,
    request_date DATE NOT NULL,
    status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Sample pending request
INSERT INTO book_requests (book_id, student_id, request_date, status) VALUES
(2, 1, CURDATE(), 'pending');

-- ==========================================================
-- End of file
-- ==========================================================
