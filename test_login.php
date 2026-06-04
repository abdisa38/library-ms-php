<?php
/**
 * TEST LOGIN - Simple login for all roles
 */

session_start();
require_once 'config/database.php';

// Create test users if not exist
try {
    $db = getDB();
    
    // Admin user
    $stmt = $db->prepare("SELECT * FROM users WHERE username = 'test'");
    $stmt->execute();
    if (!$stmt->fetch()) {
        $stmt = $db->prepare("INSERT INTO users (role_id, username, email, password, full_name) VALUES (1, 'test', 'test@test.com', 'test', 'Test Admin')");
        $stmt->execute();
    }
    
    // Librarian user
    $stmt = $db->prepare("SELECT * FROM users WHERE username = 'librarian'");
    $stmt->execute();
    if (!$stmt->fetch()) {
        $stmt = $db->prepare("INSERT INTO users (role_id, username, email, password, full_name) VALUES (2, 'librarian', 'librarian@test.com', 'librarian', 'Test Librarian')");
        $stmt->execute();
    }
    
    // Student user with linked profile
    $stmt = $db->prepare("SELECT * FROM users WHERE username = 'student'");
    $stmt->execute();
    if (!$stmt->fetch()) {
        $stmt = $db->prepare("INSERT INTO users (role_id, username, email, password, full_name) VALUES (3, 'student', 'student@test.com', 'student', 'Test Student')");
        $stmt->execute();
        
        // Create linked student profile
        $stmt = $db->prepare("SELECT * FROM students WHERE email = 'student@test.com'");
        $stmt->execute();
        if (!$stmt->fetch()) {
            $stmt = $db->prepare("INSERT INTO students (student_id, full_name, email, phone, department, year) VALUES ('STD999', 'Test Student', 'student@test.com', '555-0000', 'Computer Science', 1)");
            $stmt->execute();
        }
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
            
            header("Location: views/" . $user['role_name'] . "/dashboard.php");
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
            max-width: 500px;
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
        .role-buttons {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            margin-bottom: 20px;
        }
        .role-btn {
            padding: 15px;
            border: 2px solid #ddd;
            border-radius: 8px;
            background: white;
            cursor: pointer;
            text-align: center;
            transition: all 0.3s;
        }
        .role-btn:hover, .role-btn.active {
            border-color: #667eea;
            background: #f0f3ff;
        }
        .role-btn h3 {
            font-size: 14px;
            margin-bottom: 5px;
            color: #333;
        }
        .role-btn p {
            font-size: 11px;
            color: #666;
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
        }
        .info {
            background: #d1ecf1;
            color: #0c5460;
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔓 EASY LOGIN</h1>
        <p class="subtitle">Click a role to auto-fill credentials</p>
        
        <?php if (isset($error)): ?>
            <div class="error">❌ <?= $error ?></div>
        <?php endif; ?>
        
        <div class="role-buttons">
            <div class="role-btn" onclick="selectRole('test', 'test')">
                <h3>👑 Admin</h3>
                <p>test/test</p>
            </div>
            <div class="role-btn" onclick="selectRole('librarian', 'librarian')">
                <h3>📚 Librarian</h3>
                <p>librarian/librarian</p>
            </div>
            <div class="role-btn" onclick="selectRole('student', 'student')">
                <h3>👨‍🎓 Student</h3>
                <p>student/student</p>
            </div>
        </div>
        
        <form method="POST">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" id="username" required>
            </div>
            
            <div class="form-group">
                <label>Password</label>
                <input type="text" name="password" id="password" required>
            </div>
            
            <button type="submit">🚀 LOGIN NOW</button>
        </form>
        
        <div class="info">
            <strong>✨ Quick Access:</strong><br>
            Just click a role button above to auto-fill!<br>
            • Admin: Full access<br>
            • Librarian: Manage books & students<br>
            • Student: Browse & borrow books
        </div>
        
        <div style="text-align: center; margin-top: 20px;">
            <a href="index.php" style="color: #667eea; text-decoration: none;">
                🔐 Go to Secure Login
            </a>
        </div>
    </div>
    
    <script>
        function selectRole(username, password) {
            document.getElementById('username').value = username;
            document.getElementById('password').value = password;
            
            // Highlight selected button
            document.querySelectorAll('.role-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            event.currentTarget.classList.add('active');
        }
    </script>
</body>
</html>
