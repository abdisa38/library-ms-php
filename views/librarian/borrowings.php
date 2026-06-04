<?php
/**
 * Borrowings Management
 */

require_once '../../config/config.php';
require_once '../../includes/functions.php';

requireRole('admin');

$pageTitle = 'Manage Borrowings';
$borrowing = new Borrowing();
$book = new Book();
$student = new Student();

// Handle new borrowing
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'borrow') {
        $data = [
            'student_id' => (int)$_POST['student_id'],
            'book_id' => (int)$_POST['book_id']
        ];
        
        $result = $borrowing->create($data);
        
        if ($result['success']) {
            setFlash('success', 'Book borrowed successfully');
        } else {
            setFlash('danger', $result['message']);
        }
        
        redirect('borrowings.php');
    }
}

// Get borrowings with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$status = isset($_GET['status']) ? sanitize($_GET['status']) : null;
$search = isset($_GET['search']) ? sanitize($_GET['search']) : null;

$filters = [];
if ($status) $filters['status'] = $status;
if ($search) $filters['search'] = $search;

$totalBorrowings = $borrowing->count($filters);
$pagination = paginate($page, $totalBorrowings);
$borrowings = $borrowing->getAll($pagination['limit'], $pagination['offset'], $filters);

// Get all books and students for dropdown
$books = $book->getAll();
$students = $student->getAll();

include '../../includes/header.php';
?>

<div class="page-header">
    <h1 class="page-title">
        <i class="fas fa-book-open"></i> Manage Borrowings
    </h1>
</div>

<!-- Filter -->
<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-body">
        <form method="GET" class="form-row" style="align-items: flex-end;">
            <div class="form-group" style="flex: 2;">
                <label class="form-label">Search</label>
                <input type="text" 
                       name="search" 
                       class="form-control" 
                       placeholder="Search by student or book..."
                       value="<?= e($search) ?>">
            </div>
            
            <div class="form-group" style="flex: 1;">
                <label class="form-label">Status</label>
                <select name="status" class="form-control">
                    <option value="">All Status</option>
                    <option value="borrowed" <?= $status === 'borrowed' ? 'selected' : '' ?>>Borrowed</option>
                    <option value="returned" <?= $status === 'returned' ? 'selected' : '' ?>>Returned</option>
                    <option value="overdue" <?= $status === 'overdue' ? 'selected' : '' ?>>Overdue</option>
                </select>
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Search
                </button>
                <a href="borrowings.php" class="btn btn-secondary">
                    <i class="fas fa-redo"></i> Reset
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Borrowings Table -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">All Borrowings (<?= $totalBorrowings ?>)</h3>
        <button class="btn btn-primary btn-sm" onclick="openModal('borrowModal')">
            <i class="fas fa-plus"></i> New Borrowing
        </button>
    </div>
    <div class="card-body">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Student</th>
                        <th>Book</th>
                        <th>Borrow Date</th>
                        <th>Due Date</th>
                        <th>Return Date</th>
                        <th>Fine</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($borrowings)): ?>
                        <tr>
                            <td colspan="9" style="text-align: center;">No borrowings found</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($borrowings as $brw): ?>
                            <tr>
                                <td><?= $brw['id'] ?></td>
                                <td>
                                    <strong><?= e($brw['student_name']) ?></strong><br>
                                    <small class="text-muted"><?= e($brw['student_code']) ?></small>
                                </td>
                                <td>
                                    <strong><?= e($brw['book_title']) ?></strong><br>
                                    <small class="text-muted"><?= e($brw['isbn']) ?></small>
                                </td>
                                <td><?= formatDate($brw['borrow_date']) ?></td>
                                <td>
                                    <?= formatDate($brw['due_date']) ?>
                                    <?php if ($brw['status'] === 'borrowed' && strtotime($brw['due_date']) < time()): ?>
                                        <br><small class="text-danger">Overdue!</small>
                                    <?php endif; ?>
                                </td>
                                <td><?= $brw['return_date'] ? formatDate($brw['return_date']) : '-' ?></td>
                                <td>
                                    <?php if ($brw['fine_amount'] > 0): ?>
                                        <span class="text-danger">$<?= number_format($brw['fine_amount'], 2) ?></span>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    $badgeClass = 'secondary';
                                    if ($brw['status'] === 'borrowed') $badgeClass = 'info';
                                    if ($brw['status'] === 'returned') $badgeClass = 'success';
                                    if ($brw['status'] === 'overdue') $badgeClass = 'danger';
                                    ?>
                                    <span class="badge badge-<?= $badgeClass ?>">
                                        <?= ucfirst($brw['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($brw['status'] === 'borrowed' || $brw['status'] === 'overdue'): ?>
                                        <a href="returns.php?borrow_id=<?= $brw['id'] ?>" class="btn btn-sm btn-success">
                                            <i class="fas fa-undo"></i> Return
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
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
                                <a href="?page=<?= $i ?><?= $status ? '&status=' . $status : '' ?><?= $search ? '&search=' . urlencode($search) : '' ?>">
                                    <?= $i ?>
                                </a>
                            <?php endif; ?>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
</div>

<!-- Borrow Modal -->
<div id="borrowModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">New Borrowing</h3>
            <button class="modal-close" onclick="closeModal('borrowModal')">&times;</button>
        </div>
        <form method="POST">
            <div class="modal-body">
                <input type="hidden" name="action" value="borrow">
                
                <div class="form-group">
                    <label class="form-label">Student *</label>
                    <select name="student_id" class="form-control" required>
                        <option value="">Select Student</option>
                        <?php foreach ($students as $std): ?>
                            <option value="<?= $std['id'] ?>">
                                <?= e($std['full_name']) ?> (<?= e($std['student_id']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Book *</label>
                    <select name="book_id" class="form-control" required>
                        <option value="">Select Book</option>
                        <?php foreach ($books as $bk): ?>
                            <?php if ($bk['available_copies'] > 0): ?>
                                <option value="<?= $bk['id'] ?>">
                                    <?= e($bk['title']) ?> - <?= e($bk['author_name']) ?> 
                                    (Available: <?= $bk['available_copies'] ?>)
                                </option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    <strong>Note:</strong> Due date will be automatically set to 
                    <?= getSetting('borrow_days_limit', 14) ?> days from today.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('borrowModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-check"></i> Borrow Book
                </button>
            </div>
        </form>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
