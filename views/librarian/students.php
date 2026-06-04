<?php
/**
 * Students Management
 */

require_once '../../config/config.php';
require_once '../../includes/functions.php';

requireRole('librarian');

$pageTitle = 'Manage Students';
$student = new Student();

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $profilePicture = 'default.jpg';
                
                if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
                    $upload = uploadFile($_FILES['profile_picture'], PROFILE_PATH);
                    if ($upload['success']) {
                        $profilePicture = $upload['filename'];
                    }
                }
                
                $data = [
                    'student_id' => sanitize($_POST['student_id']),
                    'full_name' => sanitize($_POST['full_name']),
                    'email' => sanitize($_POST['email']),
                    'phone' => sanitize($_POST['phone']),
                    'department' => sanitize($_POST['department']),
                    'year' => (int)$_POST['year'],
                    'address' => sanitize($_POST['address']),
                    'profile_picture' => $profilePicture
                ];
                
                $result = $student->create($data);
                
                if ($result['success']) {
                    setFlash('success', 'Student added successfully');
                } else {
                    setFlash('danger', $result['message']);
                }
                break;
                
            case 'edit':
                $data = [
                    'student_id' => sanitize($_POST['student_id']),
                    'full_name' => sanitize($_POST['full_name']),
                    'email' => sanitize($_POST['email']),
                    'phone' => sanitize($_POST['phone']),
                    'department' => sanitize($_POST['department']),
                    'year' => (int)$_POST['year'],
                    'address' => sanitize($_POST['address'])
                ];
                
                if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
                    $upload = uploadFile($_FILES['profile_picture'], PROFILE_PATH);
                    if ($upload['success']) {
                        $data['profile_picture'] = $upload['filename'];
                    }
                }
                
                $result = $student->update($_POST['id'], $data);
                
                if ($result['success']) {
                    setFlash('success', 'Student updated successfully');
                } else {
                    setFlash('danger', $result['message']);
                }
                break;
                
            case 'delete':
                $result = $student->delete($_POST['id']);
                
                if ($result['success']) {
                    setFlash('success', 'Student deleted successfully');
                } else {
                    setFlash('danger', $result['message']);
                }
                break;
        }
        
        redirect('students.php');
    }
}

// Get students with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$search = isset($_GET['search']) ? sanitize($_GET['search']) : null;

$totalStudents = $student->count($search);
$pagination = paginate($page, $totalStudents);
$students = $student->getAll($pagination['limit'], $pagination['offset'], $search);

include '../../includes/header.php';
?>

<div class="page-header">
    <h1 class="page-title">
        <i class="fas fa-user-graduate"></i> Manage Students
    </h1>
</div>

<!-- Search -->
<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-body">
        <form method="GET" class="d-flex gap-2">
            <input type="text" 
                   name="search" 
                   class="form-control" 
                   placeholder="Search by name, student ID, email..."
                   value="<?= e($search) ?>"
                   style="flex: 1;">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-search"></i> Search
            </button>
            <a href="students.php" class="btn btn-secondary">
                <i class="fas fa-redo"></i> Reset
            </a>
        </form>
    </div>
</div>

<!-- Students Table -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">All Students (<?= $totalStudents ?>)</h3>
        <button class="btn btn-primary btn-sm" onclick="openModal('addStudentModal')">
            <i class="fas fa-plus"></i> Add New Student
        </button>
    </div>
    <div class="card-body">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Student ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Department</th>
                        <th>Year</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($students)): ?>
                        <tr>
                            <td colspan="8" style="text-align: center;">No students found</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($students as $std): ?>
                            <tr>
                                <td><strong><?= e($std['student_id']) ?></strong></td>
                                <td><?= e($std['full_name']) ?></td>
                                <td><?= e($std['email']) ?></td>
                                <td><?= e($std['phone']) ?></td>
                                <td><?= e($std['department']) ?></td>
                                <td>Year <?= $std['year'] ?></td>
                                <td>
                                    <span class="badge badge-<?= $std['is_active'] ? 'success' : 'danger' ?>">
                                        <?= $std['is_active'] ? 'Active' : 'Inactive' ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <button class="btn btn-sm btn-primary" onclick='editStudent(<?= json_encode($std) ?>)'>
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <form method="POST" style="display: inline;" onsubmit="return confirmDelete()">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?= $std['id'] ?>">
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
        
        <!-- Pagination -->
        <?php if ($pagination['total_pages'] > 1): ?>
            <nav style="margin-top: 1rem;">
                <ul class="pagination">
                    <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                        <li class="<?= $i === $page ? 'active' : '' ?>">
                            <?php if ($i === $page): ?>
                                <span><?= $i ?></span>
                            <?php else: ?>
                                <a href="?page=<?= $i ?><?= $search ? '&search=' . urlencode($search) : '' ?>"><?= $i ?></a>
                            <?php endif; ?>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
</div>

<!-- Add Student Modal -->
<div id="addStudentModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Add New Student</h3>
            <button class="modal-close" onclick="closeModal('addStudentModal')">&times;</button>
        </div>
        <form method="POST" enctype="multipart/form-data">
            <div class="modal-body">
                <input type="hidden" name="action" value="add">
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Student ID *</label>
                        <input type="text" name="student_id" class="form-control" required>
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
                        <label class="form-label">Department</label>
                        <input type="text" name="department" class="form-control">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Year</label>
                        <input type="number" name="year" class="form-control" min="1" max="5">
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Address</label>
                    <textarea name="address" class="form-control" rows="2"></textarea>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Profile Picture</label>
                    <input type="file" name="profile_picture" class="form-control" accept="image/*">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('addStudentModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Add Student</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Student Modal -->
<div id="editStudentModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Edit Student</h3>
            <button class="modal-close" onclick="closeModal('editStudentModal')">&times;</button>
        </div>
        <form method="POST" enctype="multipart/form-data">
            <div class="modal-body">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="edit_id">
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Student ID *</label>
                        <input type="text" name="student_id" id="edit_student_id" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Full Name *</label>
                        <input type="text" name="full_name" id="edit_full_name" class="form-control" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Email *</label>
                        <input type="email" name="email" id="edit_email" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Phone</label>
                        <input type="tel" name="phone" id="edit_phone" class="form-control">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Department</label>
                        <input type="text" name="department" id="edit_department" class="form-control">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Year</label>
                        <input type="number" name="year" id="edit_year" class="form-control" min="1" max="5">
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Address</label>
                    <textarea name="address" id="edit_address" class="form-control" rows="2"></textarea>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Profile Picture</label>
                    <input type="file" name="profile_picture" class="form-control" accept="image/*">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('editStudentModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Update Student</button>
            </div>
        </form>
    </div>
</div>

<?php
$customJS = '
<script>
function editStudent(student) {
    document.getElementById("edit_id").value = student.id;
    document.getElementById("edit_student_id").value = student.student_id;
    document.getElementById("edit_full_name").value = student.full_name;
    document.getElementById("edit_email").value = student.email;
    document.getElementById("edit_phone").value = student.phone || "";
    document.getElementById("edit_department").value = student.department || "";
    document.getElementById("edit_year").value = student.year || "";
    document.getElementById("edit_address").value = student.address || "";
    
    openModal("editStudentModal");
}
</script>
';

include '../../includes/footer.php';
?>
