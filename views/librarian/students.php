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
            case 'link_account':
                // Create a user account linked to student
                $db = getDB();
                
                // Check if email already exists
                $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
                $stmt->execute([$_POST['email']]);
                if ($stmt->fetch()) {
                    setFlash('warning', 'User account already exists with this email');
                } else {
                    // Create new user account with student role
                    $stmt = $db->prepare("
                        INSERT INTO users (role_id, username, email, password, full_name) 
                        VALUES (3, ?, ?, ?, ?)
                    ");
                    $username = strtolower(str_replace(' ', '', $_POST['email'])); // Use email prefix as username
                    $username = explode('@', $username)[0]; // Get part before @
                    $password = 'student123'; // Default password
                    
                    $stmt->execute([
                        $username,
                        $_POST['email'],
                        $password, // Plain text for easy login
                        $_POST['full_name']
                    ]);
                    
                    setFlash('success', "User account created! Username: {$username}, Password: student123");
                }
                redirect('students.php');
                break;
                
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

// Get all users with student role for linking
$db = getDB();
$stmt = $db->query("SELECT id, username, full_name, email FROM users WHERE role_id = 3");
$studentUsers = $stmt->fetchAll();

include '../../includes/header.php';
?>

<div class="page-header">
    <h1 class="page-title">
        <i class="fas fa-user-graduate"></i> Manage Students
    </h1>
</div>

<!-- Info Alert -->
<div class="alert alert-info" style="margin-bottom: 1.5rem;">
    <i class="fas fa-info-circle"></i>
    <strong>💡 Student Account Linking:</strong> 
    Students need a user account to login and access the online system. 
    If you see a "<strong>Link Account</strong>" button, click it to create a login account for that student.
    Default credentials: <strong>Username (from email) / Password: student123</strong>
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
                        <th>User Account</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($students)): ?>
                        <tr>
                            <td colspan="9" style="text-align: center;">No students found</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($students as $std): ?>
                            <?php
                            // Check if student is linked to a user account
                            $stmt = $db->prepare("SELECT u.id, u.username, u.full_name FROM users u WHERE u.email = ? AND u.role_id = 3");
                            $stmt->execute([$std['email']]);
                            $linkedUser = $stmt->fetch();
                            ?>
                            <tr>
                                <td><strong><?= e($std['student_id']) ?></strong></td>
                                <td><?= e($std['full_name']) ?></td>
                                <td><?= e($std['email']) ?></td>
                                <td><?= e($std['phone']) ?></td>
                                <td><?= e($std['department']) ?></td>
                                <td>Year <?= $std['year'] ?></td>
                                <td>
                                    <?php if ($linkedUser): ?>
                                        <span class="badge badge-success">
                                            <i class="fas fa-check-circle"></i> <?= e($linkedUser['username']) ?>
                                        </span>
                                    <?php else: ?>
                                        <button class="btn btn-sm btn-warning" onclick="linkUserAccount(<?= $std['id'] ?>, '<?= e($std['email']) ?>', '<?= e($std['full_name']) ?>')">
                                            <i class="fas fa-link"></i> Link Account
                                        </button>
                                    <?php endif; ?>
                                </td>
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

<!-- Link User Account Modal -->
<div id="linkAccountModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Link User Account</h3>
            <button class="modal-close" onclick="closeModal('linkAccountModal')">&times;</button>
        </div>
        <form method="POST">
            <div class="modal-body">
                <input type="hidden" name="action" value="link_account">
                <input type="hidden" name="student_id" id="link_student_id">
                <input type="hidden" name="email" id="link_email">
                <input type="hidden" name="full_name" id="link_full_name">
                
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    <strong>Create Login Account</strong><br>
                    This will create a user account that the student can use to login to the system.
                </div>
                
                <div class="form-group">
                    <label class="form-label">Student Name</label>
                    <input type="text" id="link_display_name" class="form-control" readonly>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="text" id="link_display_email" class="form-control" readonly>
                </div>
                
                <div class="alert alert-warning">
                    <i class="fas fa-key"></i>
                    <strong>Default Login Credentials:</strong><br>
                    <strong>Username:</strong> (auto-generated from email)<br>
                    <strong>Password:</strong> student123<br>
                    <small>Student can change password after first login</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('linkAccountModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-link"></i> Create Account & Link
                </button>
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

function linkUserAccount(studentId, email, fullName) {
    document.getElementById("link_student_id").value = studentId;
    document.getElementById("link_email").value = email;
    document.getElementById("link_full_name").value = fullName;
    document.getElementById("link_display_name").value = fullName;
    document.getElementById("link_display_email").value = email;
    
    openModal("linkAccountModal");
}
</script>
';

include '../../includes/footer.php';
?>
