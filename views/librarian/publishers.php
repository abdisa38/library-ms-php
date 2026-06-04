<?php
/**
 * Publishers Management
 */

require_once '../../config/config.php';
require_once '../../includes/functions.php';

requireRole('librarian');

$pageTitle = 'Manage Publishers';
$publisher = new Publisher();

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $data = [
                    'name' => sanitize($_POST['name']),
                    'address' => sanitize($_POST['address']),
                    'email' => sanitize($_POST['email']),
                    'phone' => sanitize($_POST['phone'])
                ];
                $result = $publisher->create($data);
                setFlash($result['success'] ? 'success' : 'danger', 
                         $result['success'] ? 'Publisher added successfully' : $result['message']);
                break;
                
            case 'edit':
                $data = [
                    'name' => sanitize($_POST['name']),
                    'address' => sanitize($_POST['address']),
                    'email' => sanitize($_POST['email']),
                    'phone' => sanitize($_POST['phone'])
                ];
                $result = $publisher->update($_POST['id'], $data);
                setFlash($result['success'] ? 'success' : 'danger', 
                         $result['success'] ? 'Publisher updated successfully' : $result['message']);
                break;
                
            case 'delete':
                $result = $publisher->delete($_POST['id']);
                setFlash($result['success'] ? 'success' : 'danger', 
                         $result['success'] ? 'Publisher deleted successfully' : $result['message']);
                break;
        }
        redirect('publishers.php');
    }
}

$publishers = $publisher->getAll();

include '../../includes/header.php';
?>

<div class="page-header">
    <h1 class="page-title">
        <i class="fas fa-building"></i> Manage Publishers
    </h1>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">All Publishers</h3>
        <button class="btn btn-primary btn-sm" onclick="openModal('addModal')">
            <i class="fas fa-plus"></i> Add Publisher
        </button>
    </div>
    <div class="card-body">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Address</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($publishers)): ?>
                        <tr>
                            <td colspan="6" style="text-align: center;">No publishers found</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($publishers as $pub): ?>
                            <tr>
                                <td><?= $pub['id'] ?></td>
                                <td><strong><?= e($pub['name']) ?></strong></td>
                                <td><?= e($pub['address']) ?></td>
                                <td><?= e($pub['email']) ?></td>
                                <td><?= e($pub['phone']) ?></td>
                                <td>
                                    <div class="btn-group">
                                        <button class="btn btn-sm btn-primary" onclick='editPublisher(<?= json_encode($pub) ?>)'>
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <form method="POST" style="display: inline;" onsubmit="return confirmDelete()">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?= $pub['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Modal -->
<div id="addModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Add Publisher</h3>
            <button class="modal-close" onclick="closeModal('addModal')">&times;</button>
        </div>
        <form method="POST">
            <div class="modal-body">
                <input type="hidden" name="action" value="add">
                <div class="form-group">
                    <label class="form-label">Name *</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Address</label>
                    <textarea name="address" class="form-control" rows="2"></textarea>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Phone</label>
                        <input type="tel" name="phone" class="form-control">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('addModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Add Publisher</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Modal -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Edit Publisher</h3>
            <button class="modal-close" onclick="closeModal('editModal')">&times;</button>
        </div>
        <form method="POST">
            <div class="modal-body">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="edit_id">
                <div class="form-group">
                    <label class="form-label">Name *</label>
                    <input type="text" name="name" id="edit_name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Address</label>
                    <textarea name="address" id="edit_address" class="form-control" rows="2"></textarea>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" id="edit_email" class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Phone</label>
                        <input type="tel" name="phone" id="edit_phone" class="form-control">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('editModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Update Publisher</button>
            </div>
        </form>
    </div>
</div>

<?php
$customJS = '
<script>
function editPublisher(pub) {
    document.getElementById("edit_id").value = pub.id;
    document.getElementById("edit_name").value = pub.name;
    document.getElementById("edit_address").value = pub.address || "";
    document.getElementById("edit_email").value = pub.email || "";
    document.getElementById("edit_phone").value = pub.phone || "";
    openModal("editModal");
}
</script>
';

include '../../includes/footer.php';
?>
