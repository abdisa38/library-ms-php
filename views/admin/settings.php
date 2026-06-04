<?php
require_once '../../config/config.php';
require_once '../../includes/functions.php';
requireRole('admin');
$pageTitle = 'Settings';
$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $settings = [
        'daily_fine_rate' => sanitize($_POST['daily_fine_rate']),
        'borrow_days_limit' => sanitize($_POST['borrow_days_limit']),
        'max_books_per_student' => sanitize($_POST['max_books_per_student']),
        'system_name' => sanitize($_POST['system_name']),
        'system_email' => sanitize($_POST['system_email'])
    ];
    
    try {
        foreach ($settings as $key => $value) {
            $stmt = $db->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = ?");
            $stmt->execute([$value, $key]);
        }
        setFlash('success', 'Settings updated successfully');
        redirect('settings.php');
    } catch (Exception $e) {
        setFlash('danger', 'Error updating settings: ' . $e->getMessage());
    }
}

$stmt = $db->query("SELECT * FROM settings");
$allSettings = $stmt->fetchAll();
$settings = [];
foreach ($allSettings as $s) {
    $settings[$s['setting_key']] = $s['setting_value'];
}

include '../../includes/header.php';
?>

<div class="page-header">
    <h1 class="page-title"><i class="fas fa-cog"></i> System Settings</h1>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">General Settings</h3>
    </div>
    <div class="card-body">
        <form method="POST">
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">System Name</label>
                    <input type="text" name="system_name" class="form-control" value="<?= e($settings['system_name'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">System Email</label>
                    <input type="email" name="system_email" class="form-control" value="<?= e($settings['system_email'] ?? '') ?>">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Daily Fine Rate ($)</label>
                    <input type="number" name="daily_fine_rate" class="form-control" step="0.01" value="<?= e($settings['daily_fine_rate'] ?? '5.00') ?>">
                    <small class="text-muted">Fine charged per day for overdue books</small>
                </div>
                <div class="form-group">
                    <label class="form-label">Borrow Days Limit</label>
                    <input type="number" name="borrow_days_limit" class="form-control" value="<?= e($settings['borrow_days_limit'] ?? '14') ?>">
                    <small class="text-muted">Maximum days allowed for borrowing</small>
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">Max Books Per Student</label>
                <input type="number" name="max_books_per_student" class="form-control" value="<?= e($settings['max_books_per_student'] ?? '3') ?>">
                <small class="text-muted">Maximum number of books a student can borrow at once</small>
            </div>
            
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Save Settings
            </button>
        </form>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
