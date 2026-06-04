# Library Management System - Setup Guide

## Quick Installation (5 Minutes)

### Step 1: Install XAMPP
1. Download XAMPP from https://www.apachefriends.org/
2. Install XAMPP to `C:\xampp\`
3. Start Apache and MySQL from XAMPP Control Panel

### Step 2: Setup Files
1. Your project is already in: `C:\xampp\htdocs\Library ms php\library-ms-php\`
2. All folders and files are created ✓

### Step 3: Create Database
1. Open browser and go to: `http://localhost/phpmyadmin`
2. Click "New" in the left sidebar
3. Database name: `library_management`
4. Collation: `utf8mb4_unicode_ci`
5. Click "Create"

### Step 4: Import Database
1. Click on `library_management` database
2. Click "Import" tab at the top
3. Click "Choose File"
4. Navigate to: `C:\xampp\htdocs\Library ms php\library-ms-php\database\library.sql`
5. Click "Go" at the bottom
6. Wait for "Import has been successfully finished" message

### Step 5: Access the System
1. Open browser
2. Go to: `http://localhost/library-ms-php/`
3. Login with:
   - **Username:** `admin`
   - **Password:** `admin123`

## Default Accounts

### Admin Account
- Username: `admin`
- Password: `admin123`
- Full Access to all features

## Important First Steps

### 1. Change Admin Password
After first login:
1. Go to: My Profile
2. Click "Change Password"
3. Enter new secure password

### 2. Configure Settings
Go to: Admin > Settings
- Set daily fine rate
- Set borrowing days limit
- Set max books per student

### 3. Add Your Data
Start adding:
- Categories
- Authors
- Publishers
- Books
- Students

## Common Issues & Solutions

### Issue: Cannot connect to database
**Solution:**
1. Check XAMPP MySQL is running (green in control panel)
2. Verify database name is `library_management`
3. Check `config/database.php` for correct credentials

### Issue: Blank page after login
**Solution:**
1. Check PHP error logs in `C:\xampp\php\logs\`
2. Enable error reporting in `config/config.php`:
   ```php
   error_reporting(E_ALL);
   ini_set('display_errors', 1);
   ```

### Issue: File upload not working
**Solution:**
1. Check `uploads/` folder exists
2. Check folder permissions (should be writable)
3. Verify `upload_max_filesize` in `php.ini`

### Issue: Login says "Invalid credentials"
**Solution:**
1. Ensure database was imported correctly
2. Check users table has data: `SELECT * FROM users;`
3. Default password is: `admin123`

## Directory Structure Check

Ensure these folders exist:
```
library-ms-php/
├── uploads/
│   ├── books/          ✓ Created
│   └── profiles/       ✓ Created
├── database/           ✓ Created
├── assets/
│   ├── css/           ✓ Created
│   ├── js/            ✓ Created
│   └── images/        ✓ Created
├── config/            ✓ Created
├── models/            ✓ Created
├── views/             ✓ Created
└── includes/          ✓ Created
```

## Database Tables Check

After import, verify tables exist:
```sql
SHOW TABLES FROM library_management;
```

Should show:
- activity_logs
- authors
- books
- borrowings
- categories
- fines
- notifications
- publishers
- roles
- settings
- students
- users

## Testing the Installation

### 1. Test Login
- Go to `http://localhost/library-ms-php/`
- Login with admin credentials
- Should see admin dashboard

### 2. Test Book Management
- Go to: Admin > Books
- Click "Add New Book"
- Fill in details
- Upload a book cover image
- Click "Add Book"
- Book should appear in the list

### 3. Test Student Management
- Go to: Admin > Students
- Click "Add New Student"
- Fill in details
- Click "Add Student"
- Student should appear in the list

### 4. Test Borrowing
- Go to: Admin > Borrowings
- Click "New Borrowing"
- Select student and book
- Click "Borrow Book"
- Should show success message

### 5. Test Return
- Go to: Admin > Borrowings
- Find the borrowing
- Click "Return"
- Confirm return
- Should show in Recent Returns

## Sample Data Included

The database includes:
- 8 Categories (Fiction, Non-Fiction, Science, etc.)
- 5 Authors (J.K. Rowling, George Orwell, etc.)
- 4 Publishers
- 4 Sample Books
- 3 Sample Students
- System Settings (fine rate, borrow limit, etc.)

## Security Checklist (Before Production)

- [ ] Change admin password
- [ ] Update database credentials
- [ ] Disable error display
- [ ] Enable HTTPS
- [ ] Set secure file permissions
- [ ] Review and test all features
- [ ] Backup database regularly
- [ ] Change default session settings

## Features Available

### Admin Features
✓ Dashboard with statistics and charts
✓ Book management (Add, Edit, Delete)
✓ Category management
✓ Author management
✓ Publisher management
✓ Student management
✓ Borrowing management
✓ Return processing with auto fine calculation
✓ Overdue tracking
✓ Reports and analytics
✓ Export to CSV
✓ Activity logs
✓ System settings

### Security Features
✓ Password hashing (bcrypt)
✓ SQL injection protection (Prepared Statements)
✓ XSS protection
✓ Session management
✓ Role-based access control
✓ Input validation
✓ File upload security

## Next Steps

1. ✓ Database imported
2. ✓ System accessible
3. Change admin password
4. Add your categories
5. Add your authors
6. Add your publishers
7. Add your books
8. Add your students
9. Start borrowing books!

## Need Help?

Check these files:
- `README.md` - Complete documentation
- `database/library.sql` - Database structure
- `config/config.php` - Configuration settings

## System URLs

- **Login Page:** `http://localhost/library-ms-php/`
- **Admin Dashboard:** `http://localhost/library-ms-php/views/admin/dashboard.php`
- **Books:** `http://localhost/library-ms-php/views/admin/books.php`
- **Students:** `http://localhost/library-ms-php/views/admin/students.php`
- **Borrowings:** `http://localhost/library-ms-php/views/admin/borrowings.php`

## Success!

If you can:
1. ✓ Login as admin
2. ✓ See the dashboard
3. ✓ Navigate through menus
4. ✓ View sample data

**Your Library Management System is ready to use!** 📚

---

**Enjoy managing your library!**
