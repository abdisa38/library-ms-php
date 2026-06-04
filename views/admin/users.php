<?php
require_once '../../config/config.php';
require_once '../../includes/functions.php';
requireRole('admin');
$pageTitle = 'Manage Users';
$user = new User();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add') {
            $data = [
                'role_id' => (int)$_POST['role_id'],
                'username' => sanitize($_POST['username']),
                'email' => sanitize($_POST['email']),
                'password' => $_POST['password'],
                'full_name' => sanitize($_POST['full_name']),
                'phone' => sanitize($_POST['phone'])
            ];
            $result = $user->register($data);
            setFlash($result['success'] ? 'success' : 'danger', $result['success'] ? 'User added successfully' : $result['message']);
        } elseif ($_POST['action'] === 'delete') {
            $result = $user->delete($_POST['user_id']);
            setFlash($result['success'] ? 'success' : 'danger', $result['success'] ? 'User deleted successfully' : $result['message']);
        }
        redirect('users.php');
    }
}

$users = $user->getAllUsers();
include '../../includes/header.php';
?>

<div class="page-header">
    <h1 class="page-title"><i class="fas fa-users"></i> Manage Users</h1>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">All Users</h3>
        <button class="btn btn-primary btn-sm" onclick="openModal('addModal')">
            <i class="fas fa-plus"></i> Add User
        </button>
    </div>
    <div class="card-body">
        <div class="table-container">
            <table>
                <thead>
                    <tr><th>ID</th><th>Username</th><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                        <tr>
                            <td><?= $u['id'] ?></td>
                            <td><?= e($u['username']) ?></td>
                            <td><?= e($u['full_name']) ?></td>
                            <td><?= e($u['email']) ?></td>
                            <td><span class="badge badge-primary"><?= ucfirst($u['role_name']) ?></span></td>
                            <td><span class="badge badge-<?= $u['is_active'] ? 'success' : 'danger' ?>"><?= $u['is_active'] ? 'Active' : 'Inactive' ?></span></td>
                            <td>
                                <?php if ($u['id'] != $_SESSION['user_id']): ?>
                                    <form method="POST" style="display: inline;" onsubmit="return confirmDelete()">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="addModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Add User</h3>
            <button class="modal-close" onclick="closeModal('addModal')">&times;</button>
        </div>
        <form method="POST">
            <div class="modal-body">
                <input type="hidden" name="action" value="add">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Username *</label>
                        <input type="text" name="username" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Full Name *</label>
                        <input type="text" name="full_name" class="form-control" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Email *</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Phone</label>
                        <input type="tel" name="phone" class="form-control">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Role *</label>
                        <select name="role_id" class="form-control" required>
                            <option value="1">Admin</option>
                            <option value="2">Librarian</option>
                            <option value="3">Student</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Password *</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('addModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Add User</button>
            </div>
        </form>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
