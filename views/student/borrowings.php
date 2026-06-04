<?php
/**
 * Student - My Borrowings
 */

require_once '../../config/config.php';
require_once '../../includes/functions.php';

requireRole('student');

$pageTitle = 'My Borrowings';
$db = getDB();

// Get student info
$stmt = $db->prepare("SELECT * FROM students WHERE email = ?");
$stmt->execute([$_SESSION['email']]);
$studentInfo = $stmt->fetch();

$myBorrowings = [];
$borrowingStats = ['active' => 0, 'returned' => 0, 'overdue' => 0, 'total_fines' => 0];

if ($studentInfo) {
    // Get all borrowings
    $stmt = $db->prepare("
        SELECT b.*, bk.title, bk.isbn, bk.book_cover, a.name as author_name, c.name as category_name
        FROM borrowings b
        JOIN books bk ON b.book_id = bk.id
        LEFT JOIN authors a ON bk.author_id = a.id
        LEFT JOIN categories c ON bk.category_id = c.id
        WHERE b.student_id = ?
        ORDER BY b.created_at DESC
    ");
    $stmt->execute([$studentInfo['id']]);
    $myBorrowings = $stmt->fetchAll();
    
    // Get stats
    foreach ($myBorrowings as $borrowing) {
        if ($borrowing['status'] === 'borrowed') $borrowingStats['active']++;
        if ($borrowing['status'] === 'returned') $borrowingStats['returned']++;
        if ($borrowing['status'] === 'overdue' || ($borrowing['status'] === 'borrowed' && strtotime($borrowing['due_date']) < time())) {
            $borrowingStats['overdue']++;
        }
        $borrowingStats['total_fines'] += $borrowing['fine_amount'];
    }
}

include '../../includes/header.php';
?>

<div class="page-header">
    <h1 class="page-title">
        <i class="fas fa-book-open"></i> My Borrowings
    </h1>
</div>

<?php if (!$studentInfo): ?>
    <div class="alert alert-warning">
        <i class="fas fa-exclamation-triangle"></i>
        <strong>Note:</strong> Your account is not linked to a student profile. 
        Please contact the librarian.
    </div>
<?php else: ?>
    <!-- Statistics -->
    <div class="card-grid" style="margin-bottom: 2rem;">
        <div class="card stat-card info">
            <div class="card-body">
                <div class="stat-card-body">
                    <div class="stat-card-info">
                        <h3><?= $borrowingStats['active'] ?></h3>
                        <p>Active Borrowings</p>
                    </div>
                    <div class="stat-card-icon"><i class="fas fa-book-open"></i></div>
                </div>
            </div>
        </div>
        
        <div class="card stat-card success">
            <div class="card-body">
                <div class="stat-card-body">
                    <div class="stat-card-info">
                        <h3><?= $borrowingStats['returned'] ?></h3>
                        <p>Returned Books</p>
                    </div>
                    <div class="stat-card-icon"><i class="fas fa-check-circle"></i></div>
                </div>
            </div>
        </div>
        
        <div class="card stat-card danger">
            <div class="card-body">
                <div class="stat-card-body">
                    <div class="stat-card-info">
                        <h3><?= $borrowingStats['overdue'] ?></h3>
                        <p>Overdue Books</p>
                    </div>
                    <div class="stat-card-icon"><i class="fas fa-exclamation-triangle"></i></div>
                </div>
            </div>
        </div>
        
        <div class="card stat-card warning">
            <div class="card-body">
                <div class="stat-card-body">
                    <div class="stat-card-info">
                        <h3>$<?= number_format($borrowingStats['total_fines'], 2) ?></h3>
                        <p>Total Fines</p>
                    </div>
                    <div class="stat-card-icon"><i class="fas fa-dollar-sign"></i></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Borrowings Table -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Borrowing History</h3>
        </div>
        <div class="card-body">
            <?php if (empty($myBorrowings)): ?>
                <div style="text-align: center; padding: 3rem;">
                    <i class="fas fa-book-open" style="font-size: 4rem; color: #ccc;"></i>
                    <h3 style="margin-top: 1rem;">No borrowing history</h3>
                    <p class="text-muted">You haven't borrowed any books yet</p>
                    <a href="books.php" class="btn btn-primary" style="margin-top: 1rem;">
                        <i class="fas fa-book"></i> Browse Books
                    </a>
                </div>
            <?php else: ?>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Cover</th>
                                <th>Book Title</th>
                                <th>Author</th>
                                <th>Borrow Date</th>
                                <th>Due Date</th>
                                <th>Return Date</th>
                                <th>Fine</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($myBorrowings as $borrowing): ?>
                                <?php 
                                $isOverdue = $borrowing['status'] === 'borrowed' && strtotime($borrowing['due_date']) < time();
                                $daysLeft = daysDifference(date('Y-m-d'), $borrowing['due_date']);
                                ?>
                                <tr>
                                    <td>
                                        <img src="<?= BASE_URL ?>uploads/books/<?= e($borrowing['book_cover']) ?>" 
                                             class="book-cover-sm" 
                                             alt="<?= e($borrowing['title']) ?>"
                                             onerror="this.src='<?= BASE_URL ?>assets/images/default-book.jpg'">
                                    </td>
                                    <td>
                                        <strong><?= e($borrowing['title']) ?></strong><br>
                                        <small class="text-muted"><?= e($borrowing['isbn']) ?></small>
                                    </td>
                                    <td><?= e($borrowing['author_name']) ?></td>
                                    <td><?= formatDate($borrowing['borrow_date']) ?></td>
                                    <td>
                                        <?= formatDate($borrowing['due_date']) ?>
                                        <?php if ($isOverdue): ?>
                                            <br><small class="text-danger">
                                                <i class="fas fa-exclamation-circle"></i> <?= abs($daysLeft) ?> days overdue
                                            </small>
                                        <?php elseif ($borrowing['status'] === 'borrowed' && $daysLeft <= 2): ?>
                                            <br><small class="text-warning">
                                                <i class="fas fa-clock"></i> <?= abs($daysLeft) ?> days left
                                            </small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?= $borrowing['return_date'] ? formatDate($borrowing['return_date']) : '-' ?>
                                    </td>
                                    <td>
                                        <?php if ($borrowing['fine_amount'] > 0): ?>
                                            <span class="text-danger">$<?= number_format($borrowing['fine_amount'], 2) ?></span>
                                        <?php else: ?>
                                            <span class="text-success">$0.00</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php
                                        $badgeClass = 'secondary';
                                        $statusText = ucfirst($borrowing['status']);
                                        
                                        if ($isOverdue) {
                                            $badgeClass = 'danger';
                                            $statusText = 'Overdue';
                                        } elseif ($borrowing['status'] === 'borrowed') {
                                            $badgeClass = 'info';
                                        } elseif ($borrowing['status'] === 'returned') {
                                            $badgeClass = 'success';
                                        }
                                        ?>
                                        <span class="badge badge-<?= $badgeClass ?>">
                                            <?= $statusText ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<?php include '../../includes/footer.php'; ?>
