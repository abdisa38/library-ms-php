<?php
/**
 * Fix Admin Password
 * Run this file once to fix the admin password
 */

require_once 'config/database.php';

try {
    $db = getDB();
    
    // Generate correct hash for 'admin123'
    $correctHash = password_hash('admin123', PASSWORD_DEFAULT);
    
    // Update admin password
    $stmt = $db->prepare("UPDATE users SET password = ? WHERE username = 'admin'");
    $stmt->execute([$correctHash]);
    
    echo "✅ Admin password has been reset successfully!<br>";
    echo "You can now login with:<br>";
    echo "Username: <strong>admin</strong><br>";
    echo "Password: <strong>admin123</strong><br><br>";
    echo "<a href='index.php'>Go to Login Page</a><br><br>";
    echo "<small style='color: red;'>Important: Delete this file (fix_admin_password.php) after fixing the password!</small>";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>
