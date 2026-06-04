<?php
/**
 * User Profile Page
 */

require_once '../../config/config.php';
require_once '../../includes/functions.php';

requireLogin();

$pageTitle = 'My Profile';
$user = new User();
$userData = $user->getUserById($_SESSION['user_id']);

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        $data = [
            'full_name' => sanitize($_POST['full_name']),
            'email' => sanitize($_POST['email']),
            'phone' => sanitize($_POST['phone']),
            'address' => sanitize($_POST['address'])
        ];
        
        // Handle profile picture upload
        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
            $upload = uploadFile($_FILES['profile_picture'], PROFILE_PATH);
            if ($upload['success']) {
                $data['profile_picture'] = $upload['filename'];
                $_SESSION['profile_picture'] = $upload['filename'];
            }
        }
        
        $result = $user->update($_SESSION['user_id'], $data);
        
        if ($result['success']) {
            $_SESSION['full_name'] = $data['full_name'];
            $_SESSION['email'] = $data['email'];
            setFlash('success', 'Profile updated successfully');
        } else {
            setFlash('danger', $result['message']);
        }
        
        redirect('profile.php');
    }
    
    // Handle password change
    if (isset($_POST['change_password'])) {
        $oldPassword = $_POST['old_password'];
        $newPassword = $_POST['new_password'];
        $confirmPassword = $_POST['confirm_password'];
        
        if ($newPassword !== $confirmPassword) {
            setFlash('danger', 'New passwords do not match');
        } else {
            $result = $user->changePassword($_SESSION['user_id'], $oldPassword, $newPassword);
            
            if ($result['success']) {
                setFlash('success', 'Password changed successfully');
            } else {
                setFlash('danger', $result['message']);
            }
        }
        
        redirect('profile.php');
    }
}

include '../../includes/header.php';
?>

<div class="page-header">
    <h1 class="page-title">
        <i class="fas fa-user"></i> My Profile
    </h1>
</div>

<div style="display: grid; grid-template-columns: 1fr 2fr; gap: 1.5rem;">
    <!-- Profile Card -->
    <div class="card">
        <div class="card-body" style="text-align: center;">
            <img src="<?= BASE_URL ?>uploads/profiles/<?= e($userData['profile_picture']) ?>" 
                 alt="Profile Picture" 
                 class="profile-picture"
                 style="margin-bottom: 1rem;"
                 onerror="this.src='<?= BASE_URL ?>assets/images/default-avatar.png'">
            
            <h3><?= e($userData['full_name']) ?></h3>
            <p class="text-muted">@<?= e($userData['username']) ?></p>
            
            <div style="margin-top: 1.5rem;">
                <span class="badge badge-primary" style="font-size: 0.9rem; padding: 0.5rem 1rem;">
                    <?= ucfirst($userData['role_name']) ?>
                </span>
            </div>
            
            <div style="margin-top: 1.5rem; text-align: left;">
                <p><i class="fas fa-envelope"></i> <?= e($userData['email']) ?></p>
                <p><i class="fas fa-phone"></i> <?= e($userData['phone'] ?: 'Not set') ?></p>
                <p><i class="fas fa-calendar"></i> Joined: <?= formatDate($userData['created_at']) ?></p>
                <?php if ($userData['last_login']): ?>
                    <p><i class="fas fa-clock"></i> Last Login: <?= formatDate($userData['last_login']) ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Profile Forms -->
    <div>
        <!-- Update Profile -->
        <div class="card" style="margin-bottom: 1.5rem;">
            <div class="card-header">
                <h3 class="card-title">Update Profile</h3>
            </div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Full Name *</label>
                            <input type="text" 
                                   name="full_name" 
                                   class="form-control" 
                                   value="<?= e($userData['full_name']) ?>"
                                   required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Email *</label>
                            <input type="email" 
                                   name="email" 
                                   class="form-control" 
                                   value="<?= e($userData['email']) ?>"
                                   required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Phone</label>
                            <input type="tel" 
                                   name="phone" 
                                   class="form-control" 
                                   value="<?= e($userData['phone']) ?>">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Profile Picture</label>
                            <input type="file" 
                                   name="profile_picture" 
                                   class="form-control"
                                   accept="image/*">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Address</label>
                        <textarea name="address" 
                                  class="form-control" 
                                  rows="2"><?= e($userData['address']) ?></textarea>
                    </div>
                    
                    <button type="submit" name="update_profile" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Profile
                    </button>
                </form>
            </div>
        </div>
        
        <!-- Change Password -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Change Password</h3>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="form-group">
                        <label class="form-label">Current Password *</label>
                        <input type="password" 
                               name="old_password" 
                               class="form-control"
                               required>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">New Password *</label>
                            <input type="password" 
                                   name="new_password" 
                                   id="new_password"
                                   class="form-control"
                                   required>
                            <small id="passwordStrength"></small>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Confirm New Password *</label>
                            <input type="password" 
                                   name="confirm_password" 
                                   id="confirm_password"
                                   class="form-control"
                                   required>
                            <small id="passwordMatch"></small>
                        </div>
                    </div>
                    
                    <button type="submit" name="change_password" class="btn btn-warning">
                        <i class="fas fa-key"></i> Change Password
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
$customJS = '
<script>
updatePasswordStrength("new_password", "passwordStrength");
checkPasswordMatch("new_password", "confirm_password", "passwordMatch");
</script>
';

include '../../includes/footer.php';
?>
