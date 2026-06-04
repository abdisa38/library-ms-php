<?php
/**
 * Student Dashboard
 */

require_once '../../config/config.php';
require_once '../../includes/functions.php';

requireRole('student');

$pageTitle = 'Student Dashboard';
$db = getDB();

// Get student info - check by email matching with users table
$studentInfo = null;
$stmt = $db->prepare("SELECT * FROM students WHERE email = ?");
$stmt->execute([$_SESSION['email']]);
$studentInfo = $stmt->fetch();

// Debug: Log the linking check
if (!$studentInfo) {
    error_log("Student dashboard: No student profile found for email: " . $_SESSION['email']);
}

// Get statistics
$borrowedBooks = 0;
$overdueBooks = 0;
$myBorrowings = [];

if ($studentInfo) {
    $stmt = $db->query("SELECT COUNT(*) as total FROM borrowings WHERE student_id = {$studentInfo['id']} AND status = 'borrowed'");
    $borrowedBooks = $stmt->fetch()['total'];
    
    $stmt = $db->query("SELECT COUNT(*) as total FROM borrowings WHERE student_id = {$studentInfo['id']} AND status = 'borrowed' AND due_date < CURDATE()");
    $overdueBooks = $stmt->fetch()['total'];
    
    $stmt = $db->prepare("
        SELECT b.*, bk.title, bk.isbn, bk.book_cover, a.name as author_name
        FROM borrowings b
        JOIN books bk ON b.book_id = bk.id
        LEFT JOIN authors a ON bk.author_id = a.id
        WHERE b.student_id = ? AND b.status = 'borrowed'
        ORDER BY b.due_date ASC
        LIMIT 5
    ");
    $stmt->execute([$studentInfo['id']]);
    $myBorrowings = $stmt->fetchAll();
}

// Get available books
$stmt = $db->prepare("
    SELECT b.*, a.name as author_name, c.name as category_name
    FROM books b
    LEFT JOIN authors a ON b.author_id = a.id
    LEFT JOIN categories c ON b.category_id = c.id
    WHERE b.available_copies > 0
    ORDER BY b.date_added DESC
    LIMIT 8
");
$stmt->execute();
$availableBooks = $stmt->fetchAll();

include '../../includes/header.php';
?>

<div class="page-header">
    <h1 class="page-title">
        <i class="fas fa-tachometer-alt"></i> My Dashboard
    </h1>
    <p class="text-muted">Welcome back, <?= getUserName() ?>!</p>
</div>

<?php if (!$studentInfo): ?>
    <div class="alert alert-warning">
        <i class="fas fa-exclamation-triangle"></i>
        <strong>Note:</strong> Your account is not linked to a student profile. 
        Please contact the librarian to link your account.
    </div>
<?php endif; ?>

<!-- Statistics Cards -->
<div class="card-grid">
    <div class="card stat-card info">
        <div class="card-body">
            <div class="stat-card-body">
                <div class="stat-card-info">
                    <h3><?= $borrowedBooks ?></h3>
                    <p>Currently Borrowed</p>
                </div>
                <div class="stat-card-icon">
                    <i class="fas fa-book-open"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card stat-card <?= $overdueBooks > 0 ? 'danger' : 'success' ?>">
        <div class="card-body">
            <div class="stat-card-body">
                <div class="stat-card-info">
                    <h3><?= $overdueBooks ?></h3>
                    <p>Overdue Books</p>
                </div>
                <div class="stat-card-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card stat-card primary">
        <div class="card-body">
            <div class="stat-card-body">
                <div class="stat-card-info">
                    <h3><?= count($availableBooks) ?>+</h3>
                    <p>Available Books</p>
                </div>
                <div class="stat-card-icon">
                    <i class="fas fa-book"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card stat-card warning">
        <div class="card-body">
            <div class="stat-card-body">
                <div class="stat-card-info">
                    <h3><?= getSetting('max_books_per_student', 3) ?></h3>
                    <p>Max Books Allowed</p>
                </div>
                <div class="stat-card-icon">
                    <i class="fas fa-limit"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if ($studentInfo && !empty($myBorrowings)): ?>
    <!-- My Current Borrowings -->
    <div class="card" style="margin-top: 2rem;">
        <div class="card-header">
            <h3 class="card-title">My Current Borrowings</h3>
            <a href="borrowings.php" class="btn btn-primary btn-sm">View All</a>
        </div>
        <div class="card-body">
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Book Cover</th>
                            <th>Book Title</th>
                            <th>Author</th>
                            <th>Borrow Date</th>
                            <th>Due Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($myBorrowings as $borrowing): ?>
                            <?php 
                            $isOverdue = strtotime($borrowing['due_date']) < time();
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
                                            <i class="fas fa-exclamation-circle"></i> Overdue!
                                        </small>
                                    <?php elseif ($daysLeft <= 2): ?>
                                        <br><small class="text-warning">
                                            <i class="fas fa-clock"></i> Due soon (<?= abs($daysLeft) ?> days)
                                        </small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($isOverdue): ?>
                                        <span class="badge badge-danger">Overdue</span>
                                    <?php else: ?>
                                        <span class="badge badge-info">Borrowed</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Available Books -->
<div class="card" style="margin-top: 2rem;">
    <div class="card-header">
        <h3 class="card-title">Available Books</h3>
        <a href="books.php" class="btn btn-primary btn-sm">Browse All Books</a>
    </div>
    <div class="card-body">
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 1.5rem;">
            <?php foreach ($availableBooks as $book): ?>
                <div class="card" style="overflow: hidden;">
                    <img src="<?= BASE_URL ?>uploads/books/<?= e($book['book_cover']) ?>" 
                         class="book-cover" 
                         alt="<?= e($book['title']) ?>"
                         style="width: 100%; height: 250px; object-fit: cover;"
                         onerror="this.src='<?= BASE_URL ?>assets/images/default-book.jpg'">
                    <div style="padding: 1rem;">
                        <h4 style="font-size: 1rem; margin-bottom: 0.5rem;"><?= e($book['title']) ?></h4>
                        <p class="text-muted" style="font-size: 0.85rem; margin-bottom: 0.5rem;">
                            <i class="fas fa-user"></i> <?= e($book['author_name']) ?>
                        </p>
                        <p style="font-size: 0.85rem; margin-bottom: 0.75rem;">
                            <span class="badge badge-primary"><?= e($book['category_name']) ?></span>
                            <span class="badge badge-success"><?= $book['available_copies'] ?> available</span>
                        </p>
                        <a href="books.php?id=<?= $book['id'] ?>" class="btn btn-primary btn-sm w-100">
                            <i class="fas fa-eye"></i> View Details
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
