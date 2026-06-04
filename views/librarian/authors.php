<?php
/**
 * Authors Management
 */

require_once '../../config/config.php';
require_once '../../includes/functions.php';

requireRole('librarian');

$pageTitle = 'Manage Authors';
$author = new Author();

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $data = [
                    'name' => sanitize($_POST['name']),
                    'biography' => sanitize($_POST['biography'])
                ];
                $result = $author->create($data);
                setFlash($result['success'] ? 'success' : 'danger', 
                         $result['success'] ? 'Author added successfully' : $result['message']);
                break;
                
            case 'edit':
                $data = [
                    'name' => sanitize($_POST['name']),
                    'biography' => sanitize($_POST['biography'])
                ];
                $result = $author->update($_POST['id'], $data);
                setFlash($result['success'] ? 'success' : 'danger', 
                         $result['success'] ? 'Author updated successfully' : $result['message']);
                break;
                
            case 'delete':
                $result = $author->delete($_POST['id']);
                setFlash($result['success'] ? 'success' : 'danger', 
                         $result['success'] ? 'Author deleted successfully' : $result['message']);
                break;
        }
        redirect('authors.php');
    }
}

$authors = $author->getAll();

include '../../includes/header.php';
?>

<div class="page-header">
    <h1 class="page-title">
        <i class="fas fa-pen-fancy"></i> Manage Authors
    </h1>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">All Authors</h3>
        <button class="btn btn-primary btn-sm" onclick="openModal('addModal')">
            <i class="fas fa-plus"></i> Add Author
        </button>
    </div>
    <div class="card-body">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Biography</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($authors)): ?>
                        <tr>
                            <td colspan="4" style="text-align: center;">No authors found</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($authors as $auth): ?>
                            <tr>
                                <td><?= $auth['id'] ?></td>
                                <td><strong><?= e($auth['name']) ?></strong></td>
                                <td><?= e(substr($auth['biography'], 0, 100)) ?><?= strlen($auth['biography']) > 100 ? '...' : '' ?></td>
                                <td>
                                    <div class="btn-group">
                                        <button class="btn btn-sm btn-primary" onclick='editAuthor(<?= json_encode($auth) ?>)'>
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <form method="POST" style="display: inline;" onsubmit="return confirmDelete()">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?= $auth['id'] ?>">
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
            <h3 class="modal-title">Add Author</h3>
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
                    <label class="form-label">Biography</label>
                    <textarea name="biography" class="form-control" rows="4"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('addModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Add Author</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Modal -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Edit Author</h3>
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
                    <label class="form-label">Biography</label>
                    <textarea name="biography" id="edit_biography" class="form-control" rows="4"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('editModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Update Author</button>
            </div>
        </form>
    </div>
</div>

<?php
$customJS = '
<script>
function editAuthor(author) {
    document.getElementById("edit_id").value = author.id;
    document.getElementById("edit_name").value = author.name;
    document.getElementById("edit_biography").value = author.biography || "";
    openModal("editModal");
}
</script>
';

include '../../includes/footer.php';
?>
