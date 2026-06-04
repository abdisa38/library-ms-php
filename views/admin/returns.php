<?php
/**
 * Returns Management
 */

require_once '../../config/config.php';
require_once '../../includes/functions.php';

requireRole('admin');

$pageTitle = 'Book Returns';
$borrowing = new Borrowing();

// Handle return
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'return') {
        $result = $borrowing->returnBook($_POST['borrow_id']);
        
        if ($result['success']) {
            $fineMsg = $result['fine_amount'] > 0 
                ? ' Fine: $' . number_format($result['fine_amount'], 2) 
                : '';
            setFlash('success', 'Book returned successfully!' . $fineMsg);
        } else {
            setFlash('danger', $result['message']);
        }
        
        redirect('returns.php');
    }
}

// Get borrowing if borrow_id is provided
$borrowToReturn = null;
if (isset($_GET['borrow_id'])) {
    $borrowToReturn = $borrowing->getById($_GET['borrow_id']);
}

// Get recent returns
$recentReturns = $borrowing->getAll(20, 0, ['status' => 'returned']);

include '../../includes/header.php';
?>

<div class="page-header">
    <h1 class="page-title">
        <i class="fas fa-undo"></i> Book Returns
    </h1>
</div>

<?php if ($borrowToReturn): ?>
    <!-- Return Form -->
    <div class="card" style="margin-bottom: 1.5rem;">
        <div class="card-header">
            <h3 class="card-title">Return Book</h3>
        </div>
        <div class="card-body">
            <form method="POST">
                <input type="hidden" name="action" value="return">
                <input type="hidden" name="borrow_id" value="<?= $borrowToReturn['id'] ?>">
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Student</label>
                        <input type="text" 
                               class="form-control" 
                               value="<?= e($borrowToReturn['student_name']) ?> (<?= e($borrowToReturn['student_code']) ?>)" 
                               readonly>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Book</label>
                        <input type="text" 
                               class="form-control" 
                               value="<?= e($borrowToReturn['book_title']) ?>" 
                               readonly>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Borrow Date</label>
                        <input type="text" 
                               class="form-control" 
                               value="<?= formatDate($borrowToReturn['borrow_date']) ?>" 
                               readonly>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Due Date</label>
                        <input type="text" 
                               class="form-control" 
                               value="<?= formatDate($borrowToReturn['due_date']) ?>" 
                               readonly>
                    </div>
                </div>
                
                <?php 
                $fine = calculateFine($borrowToReturn['due_date']);
                $daysLate = daysDifference($borrowToReturn['due_date'], date('Y-m-d'));
                ?>
                
                <?php if ($fine > 0): ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Overdue:</strong> This book is <?= $daysLate ?> day(s) late. 
                        Fine amount: <strong>$<?= number_format($fine, 2) ?></strong>
                        (Rate: $<?= getSetting('daily_fine_rate', 5) ?>/day)
                    </div>
                <?php else: ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        Book is returned on time. No fine applicable.
                    </div>
                <?php endif; ?>
                
                <div class="btn-group">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check"></i> Confirm Return
                    </button>
                    <a href="borrowings.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
<?php else: ?>
    <!-- Search for Borrowing -->
    <div class="card" style="margin-bottom: 1.5rem;">
        <div class="card-header">
            <h3 class="card-title">Process Return</h3>
        </div>
        <div class="card-body">
            <p class="text-muted">
                <i class="fas fa-info-circle"></i>
                To return a book, go to <a href="borrowings.php">Borrowings</a> page 
                and click the "Return" button next to the borrowed book.
            </p>
            <a href="borrowings.php" class="btn btn-primary">
                <i class="fas fa-book-open"></i> View Borrowings
            </a>
        </div>
    </div>
<?php endif; ?>

<!-- Recent Returns -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Recent Returns</h3>
    </div>
    <div class="card-body">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Book</th>
                        <th>Borrow Date</th>
                        <th>Due Date</th>
                        <th>Return Date</th>
                        <th>Fine</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recentReturns)): ?>
                        <tr>
                            <td colspan="7" style="text-align: center;">No returns yet</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($recentReturns as $return): ?>
                            <tr>
                                <td>
                                    <strong><?= e($return['student_name']) ?></strong><br>
                                    <small class="text-muted"><?= e($return['student_code']) ?></small>
                                </td>
                                <td>
                                    <strong><?= e($return['book_title']) ?></strong><br>
                                    <small class="text-muted"><?= e($return['isbn']) ?></small>
                                </td>
                                <td><?= formatDate($return['borrow_date']) ?></td>
                                <td><?= formatDate($return['due_date']) ?></td>
                                <td><?= formatDate($return['return_date']) ?></td>
                                <td>
                                    <?php if ($return['fine_amount'] > 0): ?>
                                        <span class="text-danger">$<?= number_format($return['fine_amount'], 2) ?></span>
                                    <?php else: ?>
                                        <span class="text-success">$0.00</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge badge-success">Returned</span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
