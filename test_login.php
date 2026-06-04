<?php
/**
 * TEST LOGIN - Simple login without password hashing
 * Use this for testing, then switch to secure login
 */

session_start();
require_once 'config/database.php';

// Create simple test user if not exists
try {
    $db = getDB();
    
    // Check if test user exists
    $stmt = $db->prepare("SELECT * FROM users WHERE username = 'test'");
    $stmt->execute();
    $testUser = $stmt->fetch();
    
    if (!$testUser) {
        // Create test user with plain password
        $stmt = $db->prepare("INSERT INTO users (role_id, username, email, password, full_name) VALUES (1, 'test', 'test@test.com', 'test', 'Test User')");
        $stmt->execute();
        echo "<div style='padding:20px; background:#d4edda; color:#155724; margin:20px;'>✅ Test user created!</div>";
    }
} catch (Exception $e) {
    echo "<div style='padding:20px; background:#f8d7da; color:#721c24; margin:20px;'>Error: " . $e->getMessage() . "</div>";
}

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    try {
        $db = getDB();
        $stmt = $db->prepare("SELECT u.*, r.role_name FROM users u JOIN roles r ON u.role_id = r.id WHERE u.username = ? AND u.password = ?");
        $stmt->execute([$username, $password]);
        $user = $stmt->fetch();
        
        if ($user) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role_name'];
            $_SESSION['role_id'] = $user['role_id'];
            $_SESSION['profile_picture'] = $user['profile_picture'] ?? 'default.jpg';
            
            header("Location: views/admin/dashboard.php");
            exit;
        } else {
            $error = "Invalid username or password!";
        }
    } catch (Exception $e) {
        $error = "Error: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TEST LOGIN - Library System</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .container {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            max-width: 450px;
            width: 100%;
        }
        h1 {
            color: #667eea;
            margin-bottom: 10px;
            text-align: center;
        }
        .subtitle {
            color: #666;
            text-align: center;
            margin-bottom: 30px;
            font-size: 14px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 600;
        }
        input {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
        }
        input:focus {
            outline: none;
            border-color: #667eea;
        }
        button {
            width: 100%;
            padding: 14px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 10px;
        }
        button:hover {
            background: #5568d3;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #f5c6cb;
        }
        .info-box {
            background: #d1ecf1;
            color: #0c5460;
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
            border-left: 4px solid #bee5eb;
        }
        .info-box h3 {
            margin-bottom: 10px;
            color: #0c5460;
        }
        .credentials {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .credentials p {
            margin: 5px 0;
            font-family: monospace;
            color: #333;
        }
        .credentials strong {
            color: #667eea;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔓 TEST LOGIN</h1>
        <p class="subtitle">Simple login for testing (no password encryption)</p>
        
        <?php if (isset($error)): ?>
            <div class="error">❌ <?= $error ?></div>
        <?php endif; ?>
        
        <div class="credentials">
            <h3 style="margin-bottom: 10px; color: #667eea;">📋 Test Credentials:</h3>
            <p><strong>Username:</strong> test</p>
            <p><strong>Password:</strong> test</p>
        </div>
        
        <form method="POST">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" value="test" required autofocus>
            </div>
            
            <div class="form-group">
                <label>Password</label>
                <input type="text" name="password" value="test" required>
            </div>
            
            <button type="submit">🚀 LOGIN NOW</button>
        </form>
        
        <div class="info-box">
            <h3>ℹ️ Note:</h3>
            <p style="font-size: 14px; line-height: 1.6;">
                This is a TEST LOGIN page with plain text passwords for easy testing.
                After you're done testing, use the secure <strong>index.php</strong> page.
            </p>
        </div>
        
        <div style="text-align: center; margin-top: 20px;">
            <a href="index.php" style="color: #667eea; text-decoration: none;">
                🔐 Go to Secure Login
            </a>
        </div>
    </div>
</body>
</html>
