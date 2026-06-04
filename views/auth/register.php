<?php
/**
 * Registration Page
 */

require_once '../../config/config.php';
require_once '../../includes/functions.php';

// Redirect if already logged in
if (isLoggedIn()) {
    $role = getUserRole();
    redirect("../$ role/dashboard.php");
}

// Handle registration
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $data = [
        'username' => sanitize($_POST['username']),
        'email' => sanitize($_POST['email']),
        'password' => $_POST['password'],
        'full_name' => sanitize($_POST['full_name']),
        'phone' => sanitize($_POST['phone']),
        'address' => sanitize($_POST['address']),
        'role_id' => 3 // Default to student role
    ];
    
    // Validate password match
    if ($data['password'] !== $_POST['confirm_password']) {
        setFlash('danger', 'Passwords do not match');
    } else {
        $user = new User();
        $result = $user->register($data);
        
        if ($result['success']) {
            setFlash('success', 'Registration successful! Please login.');
            redirect('../../index.php');
        } else {
            setFlash('danger', $result['message']);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Library Management System</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card" style="max-width: 600px;">
            <div class="auth-header">
                <i class="fas fa-user-plus" style="font-size: 3rem; margin-bottom: 1rem;"></i>
                <h1>Create Account</h1>
                <p>Join our library community</p>
            </div>
            
            <div class="auth-body">
                <?php displayFlash(); ?>
                
                <form method="POST" action="">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="full_name">Full Name</label>
                            <input type="text" 
                                   id="full_name" 
                                   name="full_name" 
                                   class="form-control" 
                                   placeholder="Enter full name"
                                   required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="username">Username</label>
                            <input type="text" 
                                   id="username" 
                                   name="username" 
                                   class="form-control" 
                                   placeholder="Choose username"
                                   required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="email">Email</label>
                            <input type="email" 
                                   id="email" 
                                   name="email" 
                                   class="form-control" 
                                   placeholder="Enter email"
                                   required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="phone">Phone</label>
                            <input type="tel" 
                                   id="phone" 
                                   name="phone" 
                                   class="form-control" 
                                   placeholder="Enter phone number">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="address">Address</label>
                        <textarea id="address" 
                                  name="address" 
                                  class="form-control" 
                                  placeholder="Enter address"
                                  rows="2"></textarea>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="password">Password</label>
                            <input type="password" 
                                   id="password" 
                                   name="password" 
                                   class="form-control" 
                                   placeholder="Create password"
                                   required>
                            <small id="passwordStrength"></small>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="confirm_password">Confirm Password</label>
                            <input type="password" 
                                   id="confirm_password" 
                                   name="confirm_password" 
                                   class="form-control" 
                                   placeholder="Confirm password"
                                   required>
                            <small id="passwordMatch"></small>
                        </div>
                    </div>
                    
                    <button type="submit" name="register" class="btn btn-primary w-100">
                        <i class="fas fa-user-plus"></i> Create Account
                    </button>
                </form>
            </div>
            
            <div class="auth-footer">
                Already have an account? <a href="../../index.php">Sign in here</a>
            </div>
        </div>
    </div>
    
    <script src="../../assets/js/main.js"></script>
    <script>
        updatePasswordStrength('password', 'passwordStrength');
        checkPasswordMatch('password', 'confirm_password', 'passwordMatch');
    </script>
</body>
</html>
