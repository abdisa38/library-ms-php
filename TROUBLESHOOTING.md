# 🔧 Troubleshooting Guide

## ❌ Problem: "Invalid Credentials" Error

### Solution 1: Use Password Fix Script (EASIEST)

1. Open your browser
2. Go to: `http://localhost/library-ms-php/fix_admin_password.php`
3. You should see "✅ Admin password has been reset successfully!"
4. Now login with:
   - **Username:** `admin`
   - **Password:** `admin123`
5. **IMPORTANT:** Delete the `fix_admin_password.php` file after fixing

### Solution 2: Re-import Database

1. Go to: `http://localhost/phpmyadmin`
2. Click on `library_management` database
3. Click "Drop" to delete the database
4. Create new database: `library_management`
5. Import fresh: `database/library.sql`
6. Login with:
   - **Username:** `admin`
   - **Password:** `admin123`

### Solution 3: Manually Update Password via phpMyAdmin

1. Go to: `http://localhost/phpmyadmin`
2. Select `library_management` database
3. Click on `users` table
4. Click "Edit" on the admin user row
5. In the `password` field, select "Function: MD5" and change to blank
6. Paste this hash: `$2y$10$e0MYzXyjpJS7Pd0RVvHwHe6.KZHLb6FcqJwOCzZYdqKfN5vBmCjgO`
7. Click "Go"
8. Login with:
   - **Username:** `admin`
   - **Password:** `admin123`

### Solution 4: Create New Admin via SQL

1. Go to: `http://localhost/phpmyadmin`
2. Select `library_management` database
3. Click "SQL" tab
4. Paste this query:

```sql
UPDATE users 
SET password = '$2y$10$e0MYzXyjpJS7Pd0RVvHwHe6.KZHLb6FcqJwOCzZYdqKfN5vBmCjgO' 
WHERE username = 'admin';
```

5. Click "Go"
6. Login with:
   - **Username:** `admin`
   - **Password:** `admin123`

---

## 🔍 Other Common Issues

### Issue: Database Connection Error

**Symptoms:** "Connection failed" or "Cannot connect to database"

**Solutions:**
1. Check XAMPP MySQL is running (should be green in control panel)
2. Open `config/database.php`
3. Verify settings:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'root');
   define('DB_PASS', '');  // Empty for default XAMPP
   define('DB_NAME', 'library_management');
   ```
4. Make sure database `library_management` exists in phpMyAdmin

### Issue: Blank White Page

**Symptoms:** Page shows nothing, completely blank

**Solutions:**
1. Enable error display:
   - Open `config/config.php`
   - Find these lines:
   ```php
   error_reporting(E_ALL);
   ini_set('display_errors', 1);
   ```
   - Make sure they are set to `1` or `E_ALL`
   
2. Check PHP error logs:
   - Location: `C:\xampp\php\logs\php_error_log`
   - Open and check for errors

3. Check Apache error logs:
   - Location: `C:\xampp\apache\logs\error.log`

### Issue: File Upload Not Working

**Symptoms:** Cannot upload book covers or profile pictures

**Solutions:**
1. Check folders exist:
   - `uploads/books/`
   - `uploads/profiles/`

2. Check folder permissions (Windows):
   - Right-click folder → Properties → Security
   - Make sure "Users" has "Write" permission

3. Check PHP upload settings:
   - Open `C:\xampp\php\php.ini`
   - Find and check:
   ```ini
   file_uploads = On
   upload_max_filesize = 10M
   post_max_size = 10M
   ```
   - Restart Apache after changes

### Issue: CSS/JS Not Loading

**Symptoms:** Page has no styling, looks broken

**Solutions:**
1. Check file paths in browser console (F12)
2. Verify BASE_URL in `config/config.php`:
   ```php
   define('BASE_URL', 'http://localhost/library-ms-php/');
   ```
3. Make sure files exist:
   - `assets/css/style.css`
   - `assets/js/main.js`

### Issue: Session Errors

**Symptoms:** "Session already started" or session warnings

**Solutions:**
1. Clear browser cookies and cache
2. Check `session_start()` is not called multiple times
3. Restart Apache in XAMPP

### Issue: Cannot Access Pages After Login

**Symptoms:** Redirected to login after successful login

**Solutions:**
1. Check sessions are working:
   - Create test file `test_session.php`:
   ```php
   <?php
   session_start();
   $_SESSION['test'] = 'working';
   echo "Session test: " . $_SESSION['test'];
   ?>
   ```
   - Visit file, should show "Session test: working"

2. Check cookies are enabled in browser

3. Clear all browser data and try again

---

## 🆘 Quick Checks

### Verify Installation

Run these checks:

1. ✅ **XAMPP Running?**
   - Apache: Green
   - MySQL: Green

2. ✅ **Database Created?**
   - Go to: `http://localhost/phpmyadmin`
   - See `library_management` in list

3. ✅ **Tables Exist?**
   - Click `library_management`
   - Should see 13 tables (users, books, students, etc.)

4. ✅ **Admin User Exists?**
   - Browse `users` table
   - Should see admin user with role_id = 1

5. ✅ **Files Exist?**
   - Check `index.php` exists
   - Check `config/database.php` exists
   - Check `assets/css/style.css` exists

---

## 📞 Still Having Issues?

### Debug Mode

1. Open `config/config.php`
2. Add at the top:
```php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
```

3. Refresh page and check for error messages

### Test Database Connection

Create file `test_db.php`:
```php
<?php
require_once 'config/database.php';
try {
    $db = getDB();
    echo "✅ Database connected successfully!";
    
    $stmt = $db->query("SELECT * FROM users WHERE username = 'admin'");
    $user = $stmt->fetch();
    
    if ($user) {
        echo "<br>✅ Admin user found!";
        echo "<br>Username: " . $user['username'];
        echo "<br>Email: " . $user['email'];
    } else {
        echo "<br>❌ Admin user not found!";
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>
```

Visit: `http://localhost/library-ms-php/test_db.php`

---

## ✅ Verification Steps After Fix

1. Can login with admin/admin123? ✓
2. Dashboard loads with statistics? ✓
3. Can navigate all menu items? ✓
4. Can add a book? ✓
5. Can add a student? ✓
6. Can create a borrowing? ✓

If all YES → **System is working!** 🎉

---

## 🔐 Security After Fixing

Once system is working:

1. ✅ Change admin password
2. ✅ Delete `fix_admin_password.php`
3. ✅ Delete `test_db.php` (if created)
4. ✅ Delete `test_session.php` (if created)
5. ✅ Disable error display in production

---

**Need more help? Check:**
- `README.md` - Complete documentation
- `SETUP_GUIDE.md` - Installation guide
- `QUICK_START.txt` - Quick reference

---

**Most Common Fix: Just run `fix_admin_password.php`!** 🔧
