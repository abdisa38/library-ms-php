-- =============================================
-- Library Management System Database
-- =============================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- Database: library_management
CREATE DATABASE IF NOT EXISTS `library_management` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `library_management`;

-- =============================================
-- Table: roles
-- =============================================
CREATE TABLE `roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role_name` varchar(50) NOT NULL,
  `description` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `role_name` (`role_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Table: users
-- =============================================
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `phone` varchar(20),
  `address` text,
  `profile_picture` varchar(255) DEFAULT 'default.jpg',
  `is_active` tinyint(1) DEFAULT 1,
  `remember_token` varchar(255),
  `reset_token` varchar(255),
  `reset_token_expire` datetime,
  `last_login` datetime,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  KEY `role_id` (`role_id`),
  CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Table: authors
-- =============================================
CREATE TABLE `authors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `biography` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Table: publishers
-- =============================================
CREATE TABLE `publishers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `address` text,
  `email` varchar(100),
  `phone` varchar(20),
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Table: categories
-- =============================================
CREATE TABLE `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Table: books
-- =============================================
CREATE TABLE `books` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `isbn` varchar(20) NOT NULL,
  `title` varchar(255) NOT NULL,
  `author_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `publisher_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `available_copies` int(11) NOT NULL DEFAULT 0,
  `book_cover` varchar(255) DEFAULT 'default-book.jpg',
  `shelf_number` varchar(50),
  `description` text,
  `date_added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `isbn` (`isbn`),
  KEY `author_id` (`author_id`),
  KEY `category_id` (`category_id`),
  KEY `publisher_id` (`publisher_id`),
  KEY `title` (`title`),
  CONSTRAINT `books_ibfk_1` FOREIGN KEY (`author_id`) REFERENCES `authors` (`id`) ON DELETE CASCADE,
  CONSTRAINT `books_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE,
  CONSTRAINT `books_ibfk_3` FOREIGN KEY (`publisher_id`) REFERENCES `publishers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Table: students
-- =============================================
CREATE TABLE `students` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` varchar(50) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20),
  `department` varchar(100),
  `year` int(11),
  `address` text,
  `profile_picture` varchar(255) DEFAULT 'default.jpg',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `student_id` (`student_id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Table: borrowings
-- =============================================
CREATE TABLE `borrowings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `book_id` int(11) NOT NULL,
  `borrow_date` date NOT NULL,
  `due_date` date NOT NULL,
  `return_date` date,
  `status` enum('borrowed','returned','overdue') DEFAULT 'borrowed',
  `fine_amount` decimal(10,2) DEFAULT 0.00,
  `created_by` int(11),
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `student_id` (`student_id`),
  KEY `book_id` (`book_id`),
  KEY `status` (`status`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `borrowings_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  CONSTRAINT `borrowings_ibfk_2` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`) ON DELETE CASCADE,
  CONSTRAINT `borrowings_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Table: fines
-- =============================================
CREATE TABLE `fines` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `borrowing_id` int(11) NOT NULL,
  `fine_amount` decimal(10,2) NOT NULL,
  `paid_amount` decimal(10,2) DEFAULT 0.00,
  `status` enum('unpaid','partial','paid') DEFAULT 'unpaid',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `borrowing_id` (`borrowing_id`),
  CONSTRAINT `fines_ibfk_1` FOREIGN KEY (`borrowing_id`) REFERENCES `borrowings` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Table: settings
-- =============================================
CREATE TABLE `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text NOT NULL,
  `description` text,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Table: activity_logs
-- =============================================
CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11),
  `action` varchar(100) NOT NULL,
  `description` text,
  `ip_address` varchar(45),
  `user_agent` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `created_at` (`created_at`),
  CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Table: notifications
-- =============================================
CREATE TABLE `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11),
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `type` enum('info','success','warning','danger') DEFAULT 'info',
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Insert Default Roles
-- =============================================
INSERT INTO `roles` (`id`, `role_name`, `description`) VALUES
(1, 'admin', 'System Administrator with full access'),
(2, 'librarian', 'Librarian with book management access'),
(3, 'student', 'Student/User with limited access');

-- =============================================
-- Insert Default Admin User
-- Password: admin123
-- =============================================
INSERT INTO `users` (`id`, `role_id`, `username`, `email`, `password`, `full_name`, `phone`, `address`) VALUES
(1, 1, 'admin', 'admin@library.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator', '1234567890', 'Library Office');

-- =============================================
-- Insert Default Settings
-- =============================================
INSERT INTO `settings` (`setting_key`, `setting_value`, `description`) VALUES
('daily_fine_rate', '5.00', 'Daily fine rate for overdue books (in currency)'),
('borrow_days_limit', '14', 'Maximum days allowed for borrowing'),
('max_books_per_student', '3', 'Maximum books a student can borrow at once'),
('system_name', 'Library Management System', 'Name of the library system'),
('system_email', 'library@example.com', 'System email address');

-- =============================================
-- Insert Sample Categories
-- =============================================
INSERT INTO `categories` (`name`, `description`) VALUES
('Fiction', 'Fictional books and novels'),
('Non-Fiction', 'Non-fictional educational books'),
('Science', 'Scientific and research books'),
('Technology', 'Technology and computer science books'),
('History', 'Historical books and references'),
('Biography', 'Biographical books'),
('Reference', 'Reference books and encyclopedias'),
('Literature', 'Literature and poetry');

-- =============================================
-- Insert Sample Authors
-- =============================================
INSERT INTO `authors` (`name`, `biography`) VALUES
('J.K. Rowling', 'British author, best known for Harry Potter series'),
('George Orwell', 'English novelist and essayist'),
('Stephen King', 'American author of horror and fiction'),
('Agatha Christie', 'English writer known for detective novels'),
('Dan Brown', 'American author known for thriller novels');

-- =============================================
-- Insert Sample Publishers
-- =============================================
INSERT INTO `publishers` (`name`, `address`, `email`, `phone`) VALUES
('Penguin Books', '123 Publisher St, New York', 'info@penguin.com', '123-456-7890'),
('HarperCollins', '456 Book Ave, London', 'contact@harpercollins.com', '098-765-4321'),
('Random House', '789 Reader Rd, Chicago', 'info@randomhouse.com', '555-123-4567'),
('Oxford University Press', '321 Academic Blvd, Oxford', 'info@oup.com', '444-555-6666');

-- =============================================
-- Insert Sample Books
-- =============================================
INSERT INTO `books` (`isbn`, `title`, `author_id`, `category_id`, `publisher_id`, `quantity`, `available_copies`, `shelf_number`, `description`) VALUES
('978-0-7475-3269-9', 'Harry Potter and the Philosopher\'s Stone', 1, 1, 1, 5, 5, 'A-101', 'First book in the Harry Potter series'),
('978-0-452-28423-4', '1984', 2, 1, 2, 3, 3, 'A-102', 'Dystopian social science fiction novel'),
('978-0-385-50420-4', 'The Da Vinci Code', 5, 1, 3, 4, 4, 'B-201', 'Mystery thriller novel'),
('978-0-00-712498-5', 'Murder on the Orient Express', 4, 1, 2, 3, 3, 'B-202', 'Detective novel by Agatha Christie');

-- =============================================
-- Insert Sample Students
-- =============================================
INSERT INTO `students` (`student_id`, `full_name`, `email`, `phone`, `department`, `year`, `address`) VALUES
('STD001', 'John Doe', 'john.doe@student.com', '555-0001', 'Computer Science', 2, '123 Student Ave'),
('STD002', 'Jane Smith', 'jane.smith@student.com', '555-0002', 'Engineering', 3, '456 Campus Rd'),
('STD003', 'Mike Johnson', 'mike.j@student.com', '555-0003', 'Business', 1, '789 University St');

COMMIT;
