<?php
/**
 * Reset Password Page
 */

require_once '../../config/config.php';
require_once '../../includes/functions.php';

$token = $_GET['token'] ?? '';

if (empty($token)) {
    setFlash('danger', 'Invalid reset token');
    redirect('../../index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset'])) {
    $newPassword = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];
    
    if ($newPassword !== $confirmPassword) {
        setFlash('danger', 'Passwords do not match');
    } else {
        $user = new User();
        $result = $user->resetPassword($token, $newPassword);
        
        if ($result['success']) {
            setFlash('success', 'Password reset successful! Please login with your new password.');
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
    <title>Reset Password - Library Management System</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <i class="fas fa-lock" style="font-size: 3rem; margin-bottom: 1rem;"></i>
                <h1>Reset Password</h1>
                <p>Enter your new password</p>
            </div>
            
            <div class="auth-body">
                <?php displayFlash(); ?>
                
                <form method="POST" action="">
                    <div class="form-group">
                        <label class="form-label" for="password">New Password</label>
                        <input type="password" 
                               id="password" 
                               name="password" 
                               class="form-control" 
                               placeholder="Enter new password"
                               required
                               autofocus>
                        <small id="passwordStrength"></small>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="confirm_password">Confirm Password</label>
                        <input type="password" 
                               id="confirm_password" 
                               name="confirm_password" 
                               class="form-control" 
                               placeholder="Confirm new password"
                               required>
                        <small id="passwordMatch"></small>
                    </div>
                    
                    <button type="submit" name="reset" class="btn btn-primary w-100">
                        <i class="fas fa-check"></i> Reset Password
                    </button>
                </form>
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
