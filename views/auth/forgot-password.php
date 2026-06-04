<?php
/**
 * Forgot Password Page
 */

require_once '../../config/config.php';
require_once '../../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset'])) {
    $email = sanitize($_POST['email']);
    
    $user = new User();
    $result = $user->requestPasswordReset($email);
    
    if ($result['success']) {
        // In production, send email with reset link
        $resetLink = BASE_URL . "views/auth/reset-password.php?token=" . $result['token'];
        setFlash('success', 'Password reset link has been sent to your email. (Demo: ' . $resetLink . ')');
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
    <title>Forgot Password - Library Management System</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <i class="fas fa-key" style="font-size: 3rem; margin-bottom: 1rem;"></i>
                <h1>Forgot Password?</h1>
                <p>Enter your email to reset your password</p>
            </div>
            
            <div class="auth-body">
                <?php displayFlash(); ?>
                
                <form method="POST" action="">
                    <div class="form-group">
                        <label class="form-label" for="email">Email Address</label>
                        <input type="email" 
                               id="email" 
                               name="email" 
                               class="form-control" 
                               placeholder="Enter your email"
                               required
                               autofocus>
                    </div>
                    
                    <button type="submit" name="reset" class="btn btn-primary w-100">
                        <i class="fas fa-paper-plane"></i> Send Reset Link
                    </button>
                </form>
                
                <div style="text-align: center; margin-top: 1.5rem;">
                    <a href="../../index.php">
                        <i class="fas fa-arrow-left"></i> Back to Login
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <script src="../../assets/js/main.js"></script>
</body>
</html>
