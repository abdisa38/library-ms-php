# Library Management System

A complete, production-ready Library Management System built with PHP, MySQL, HTML5, CSS3, and JavaScript.

## Features

### User Roles
- **Admin**: Full system access including user management, settings, and reports
- **Librarian**: Book management, borrowing/return operations, and reports
- **Student**: View and borrow books

### Key Functionalities

#### Authentication
- ✅ Secure login with password hashing
- ✅ Registration system
- ✅ Forgot/Reset password
- ✅ Remember me functionality
- ✅ Role-based access control
- ✅ Session management

#### Book Management
- ✅ Add/Edit/Delete books
- ✅ ISBN, title, author, category, publisher
- ✅ Book cover upload
- ✅ Quantity and availability tracking
- ✅ Shelf number management
- ✅ Search and filter books

#### Category, Author & Publisher Management
- ✅ Manage categories
- ✅ Manage authors with biography
- ✅ Manage publishers with contact info

#### Student Management
- ✅ Add/Edit/Delete students
- ✅ Student ID, email, department, year
- ✅ Profile picture upload
- ✅ Track borrowing history

#### Borrowing System
- ✅ Borrow books with due date
- ✅ Check book availability
- ✅ Prevent duplicate borrowing
- ✅ Maximum books limit per student
- ✅ Automatic stock management

#### Return System
- ✅ Return books
- ✅ Automatic fine calculation
- ✅ Overdue tracking
- ✅ Fine management

#### Dashboard & Reports
- ✅ Statistics cards (total books, borrowed, overdue, etc.)
- ✅ Charts (monthly borrowing, category distribution)
- ✅ Recent activities
- ✅ Low stock alerts

#### Security
- ✅ SQL injection protection (Prepared Statements)
- ✅ XSS protection
- ✅ CSRF token support
- ✅ Password hashing (bcrypt)
- ✅ Input validation and sanitization
- ✅ Secure file upload

#### UI/UX
- ✅ Modern responsive design
- ✅ Mobile-first approach
- ✅ Sidebar navigation
- ✅ Toast notifications
- ✅ Modal dialogs
- ✅ Data tables with pagination
- ✅ Search functionality
- ✅ Beautiful cards and badges

## System Requirements

- PHP 8.0 or higher
- MySQL 5.7 or higher
- Apache Web Server (XAMPP recommended)
- Modern web browser

## Installation Guide

### Step 1: Install XAMPP

1. Download XAMPP from [https://www.apachefriends.org/](https://www.apachefriends.org/)
2. Install XAMPP on your computer
3. Start Apache and MySQL services from XAMPP Control Panel

### Step 2: Setup Project

1. Clone or download this repository
2. Copy the project folder to `C:\xampp\htdocs\`
3. Your project path should be: `C:\xampp\htdocs\library-ms-php\`

### Step 3: Create Database

**Method 1: Using phpMyAdmin**
1. Open your browser and go to `http://localhost/phpmyadmin`
2. Click "New" to create a new database
3. Database name: `library_management`
4. Click "Create"
5. Select the `library_management` database
6. Click "Import" tab
7. Click "Choose File" and select `database/library.sql`
8. Click "Go" to import

**Method 2: Using MySQL Command Line**
```bash
mysql -u root -p
CREATE DATABASE library_management;
USE library_management;
SOURCE C:/xampp/htdocs/library-ms-php/database/library.sql;
EXIT;
```

### Step 4: Configure Database

1. Open `config/database.php`
2. Update database credentials if needed:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');  // Your MySQL password
define('DB_NAME', 'library_management');
```

### Step 5: Create Upload Directories

The following directories are required for file uploads:
- `uploads/books/` - For book covers
- `uploads/profiles/` - For profile pictures

These directories are already created in the project structure.

### Step 6: Set Permissions (Optional for production)

For Linux/Mac users:
```bash
chmod -R 755 library-ms-php
chmod -R 777 library-ms-php/uploads
```

### Step 7: Access the System

1. Open your browser
2. Navigate to: `http://localhost/library-ms-php/`
3. Login with default credentials:

**Admin Account:**
- Username: `admin`
- Password: `admin123`

## Default Settings

- Daily fine rate: $5.00
- Borrow days limit: 14 days
- Maximum books per student: 3 books

These can be changed from Admin > Settings

## Project Structure

```
library-ms-php/
├── assets/
│   ├── css/
│   │   └── style.css          # Main stylesheet
│   ├── js/
│   │   └── main.js            # JavaScript functions
│   └── images/                # Static images
├── config/
│   ├── config.php             # General configuration
│   └── database.php           # Database connection
├── controllers/               # Controller files (MVC)
├── models/                    # Model files
│   ├── User.php
│   ├── Book.php
│   ├── Student.php
│   ├── Borrowing.php
│   ├── Category.php
│   ├── Author.php
│   └── Publisher.php
├── views/                     # View files
│   ├── admin/                 # Admin pages
│   │   ├── dashboard.php
│   │   ├── books.php
│   │   ├── students.php
│   │   ├── borrowings.php
│   │   ├── returns.php
│   │   ├── categories.php
│   │   ├── authors.php
│   │   ├── publishers.php
│   │   ├── users.php
│   │   ├── settings.php
│   │   ├── reports.php
│   │   └── logs.php
│   ├── librarian/             # Librarian pages
│   ├── student/               # Student pages
│   └── auth/                  # Authentication pages
│       ├── register.php
│       ├── logout.php
│       ├── forgot-password.php
│       └── reset-password.php
├── includes/                  # Include files
│   ├── header.php
│   ├── footer.php
│   └── functions.php          # Helper functions
├── uploads/                   # File uploads
│   ├── books/                 # Book covers
│   └── profiles/              # Profile pictures
├── database/
│   └── library.sql            # Database schema
├── index.php                  # Login page
└── README.md                  # This file
```

## Features by Role

### Admin Features
- Full dashboard with statistics and charts
- Manage all books (Add, Edit, Delete, Search)
- Manage categories, authors, and publishers
- Manage students and their profiles
- Manage borrowing and returns
- View overdue books and calculate fines
- Manage system users (Admin, Librarian)
- Configure system settings
- View activity logs
- Generate reports
- Database backup

### Librarian Features
- Dashboard with key statistics
- Manage books and inventory
- Process book borrowing
- Process book returns
- Track overdue books
- Manage students
- Generate reports

### Student Features
- View available books
- Search and browse books
- View borrowing history
- Check due dates
- View fines

## Security Best Practices Implemented

1. **Password Security**
   - Passwords hashed using `password_hash()` with bcrypt
   - Minimum password requirements can be enforced

2. **SQL Injection Prevention**
   - All queries use prepared statements with PDO
   - Input parameters properly bound

3. **XSS Protection**
   - All output escaped using `htmlspecialchars()`
   - Helper function `e()` for escaping

4. **CSRF Protection**
   - CSRF token functions available
   - Can be implemented on sensitive forms

5. **File Upload Security**
   - File type validation
   - File size limits
   - Unique filename generation
   - Files stored outside web root (recommended)

6. **Session Security**
   - Secure session configuration
   - Session regeneration on login
   - Proper session timeout

## Database Schema

### Main Tables
- `roles` - User roles (Admin, Librarian, Student)
- `users` - System users
- `students` - Student information
- `authors` - Book authors
- `publishers` - Book publishers
- `categories` - Book categories
- `books` - Book inventory
- `borrowings` - Borrowing transactions
- `fines` - Fine records
- `settings` - System settings
- `activity_logs` - User activity logs
- `notifications` - System notifications

## Customization

### Change Logo/Branding
Edit in `includes/header.php`:
```php
<div class="sidebar-brand">
    <a href="...">
        <i class="fas fa-book-reader"></i>
        <span>Your Library Name</span>
    </a>
</div>
```

### Change Colors
Edit CSS variables in `assets/css/style.css`:
```css
:root {
    --primary-color: #4e73df;  /* Change to your color */
    --success-color: #1cc88a;
    --danger-color: #e74a3b;
    /* ... */
}
```

### Add New Features
1. Create model in `models/`
2. Create view in appropriate `views/` folder
3. Update navigation in `includes/header.php`

## Troubleshooting

### Database Connection Error
- Check XAMPP MySQL is running
- Verify database credentials in `config/database.php`
- Ensure database `library_management` exists

### Upload Directory Errors
- Check `uploads/` directory exists
- Ensure proper permissions (777 for development)

### Blank Page or Errors
- Enable error reporting in `config/config.php`
- Check PHP error logs in XAMPP

### Login Not Working
- Clear browser cache and cookies
- Check if sessions are enabled
- Verify password: default is `admin123`

## Browser Support

- Chrome (recommended)
- Firefox
- Safari
- Edge
- Opera

## Technologies Used

- **Backend**: PHP 8+ with PDO
- **Database**: MySQL
- **Frontend**: HTML5, CSS3, Vanilla JavaScript
- **Charts**: Chart.js
- **Icons**: Font Awesome 6
- **Architecture**: MVC Pattern

## Sample Data

The database comes with sample data:
- 1 Admin user
- 8 Categories
- 5 Authors
- 4 Publishers
- 4 Sample books
- 3 Sample students

## License

This project is open-source and available for educational and commercial use.

## Support

For issues or questions:
1. Check the troubleshooting section
2. Review the code comments
3. Check database schema

## Credits

Developed as a complete library management solution with modern best practices and security standards.

## Version

Version 1.0.0 - Production Ready

---

**Important Notes:**
1. Change default admin password immediately in production
2. Enable HTTPS in production
3. Set proper file permissions
4. Regular database backups recommended
5. Keep PHP and MySQL updated
6. Review security settings before deployment

**Enjoy managing your library! 📚**
