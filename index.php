<?php
/**
 * Login Page
 * Library Management System
 */

require_once 'config/config.php';
require_once 'includes/functions.php';

// Redirect if already logged in
if (isLoggedIn()) {
    $role = getUserRole();
    redirect("views/$role/dashboard.php");
}

// Handle remember me cookie
if (isset($_COOKIE['remember_token']) && isset($_COOKIE['user_id'])) {
    $user = new User();
    $userData = $user->getUserById($_COOKIE['user_id']);
    
    if ($userData && isset($userData['remember_token'])) {
        $hashedToken = hash('sha256', $_COOKIE['remember_token']);
        if ($hashedToken === $userData['remember_token']) {
            // Auto login
            $_SESSION['user_id'] = $userData['id'];
            $_SESSION['username'] = $userData['username'];
            $_SESSION['full_name'] = $userData['full_name'];
            $_SESSION['email'] = $userData['email'];
            $_SESSION['role'] = $userData['role_name'];
            $_SESSION['role_id'] = $userData['role_id'];
            $_SESSION['profile_picture'] = $userData['profile_picture'];
            
            $role = $userData['role_name'];
            redirect("views/$role/dashboard.php");
        }
    }
}

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = sanitize($_POST['username']);
    $password = $_POST['password'];
    $remember = isset($_POST['remember']);
    
    $user = new User();
    $result = $user->login($username, $password, $remember);
    
    if ($result['success']) {
        $role = $result['user']['role_name'];
        setFlash('success', 'Welcome back, ' . $result['user']['full_name']);
        redirect("views/$role/dashboard.php");
    } else {
        setFlash('danger', $result['message']);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Library Management System</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <i class="fas fa-book-reader" style="font-size: 3rem; margin-bottom: 1rem;"></i>
                <h1><?= SITE_NAME ?></h1>
                <p>Sign in to your account</p>
            </div>
            
            <div class="auth-body">
                <?php displayFlash(); ?>
                
                <form method="POST" action="">
                    <div class="form-group">
                        <label class="form-label" for="username">
                            <i class="fas fa-user"></i> Username or Email
                        </label>
                        <input type="text" 
                               id="username" 
                               name="username" 
                               class="form-control" 
                               placeholder="Enter username or email"
                               required
                               autofocus>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="password">
                            <i class="fas fa-lock"></i> Password
                        </label>
                        <input type="password" 
                               id="password" 
                               name="password" 
                               class="form-control" 
                               placeholder="Enter password"
                               required>
                    </div>
                    
                    <div class="form-group" style="display: flex; align-items: center; gap: 0.5rem;">
                        <input type="checkbox" id="remember" name="remember">
                        <label for="remember" style="margin: 0; font-weight: normal;">Remember me</label>
                    </div>
                    
                    <button type="submit" name="login" class="btn btn-primary w-100">
                        <i class="fas fa-sign-in-alt"></i> Sign In
                    </button>
                </form>
                
                <div style="text-align: center; margin-top: 1.5rem;">
                    <a href="views/auth/forgot-password.php">Forgot Password?</a>
                </div>
            </div>
            
            <div class="auth-footer">
                Don't have an account? <a href="views/auth/register.php">Register here</a>
            </div>
        </div>
    </div>
    
    <script src="assets/js/main.js"></script>
</body>
</html>
