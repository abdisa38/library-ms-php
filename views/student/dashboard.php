<?php
/**
 * Student Dashboard
 */

require_once '../../config/config.php';
require_once '../../includes/functions.php';

requireRole('student');

$pageTitle = 'Student Dashboard';
$db = getDB();

// Get student info (if exists)
$stmt = $db->prepare("SELECT * FROM students WHERE email = ?");
$stmt->execute([$_SESSION['email']]);
$studentInfo = $stmt->fetch();

// Get stats
$studentId = $studentInfo['id'] ?? 0;

// Total borrowed books (current)
$stmt = $db->prepare("SELECT COUNT(*) as total FROM borrowings WHERE student_id = ? AND status = 'borrowed'");
$stmt->execute([$studentId]);
$currentBorrowed = $stmt->fetch()['total'];

// Total books borrowed (all time)
$stmt = $db->prepare("SELECT COUNT(*) as total FROM borrowings WHERE student_id = ?");
$stmt->execute([$studentId]);
$totalBorrowed = $stmt->fetch()['total'];

// Overdue books
$stmt = $db->prepare("SELECT COUNT(*) as total FROM borrowings WHERE student_id = ? AND status = 'borrowed' AND due_date < CURDATE()");
$stmt->execute([$studentId]);
$overdueBooks = $stmt->fetch()['total'];

// Total fines
$stmt = $db->prepare("SELECT SUM(fine_amount) as total FROM borrowings WHERE student_id = ?");
$stmt->execute([$studentId]);
$totalFines = $stmt->fetch()['total'] ?? 0;

// Get current borrowings
$stmt = $db->prepare("
    SELECT b.*, bk.title, bk.isbn, bk.book_cover, a.name as author_name,
           DATEDIFF(b.due_date, CURDATE()) as days_remaining
    FROM borrowings b
    JOIN books bk ON b.book_id = bk.id
    LEFT JOIN authors a ON bk.author_id = a.id
    WHERE b.student_id = ? AND b.status = 'borrowed'
    ORDER BY b.due_date ASC
");
$stmt->execute([$studentId]);
$currentBorrowings = $stmt->fetchAll();

// Get borrowing history
$stmt = $db->prepare("
    SELECT b.*, bk.title, bk.isbn, a.name as author_name
    FROM borrowings b
    JOIN books bk ON b.book_id = bk.id
    LEFT JOIN authors a ON bk.author_id = a.id
    WHERE b.student_id = ? AND b.status != 'borrowed'
    ORDER BY b.return_date DESC
    LIMIT 5
");
$stmt->execute([$studentId]);
$recentHistory = $stmt->fetchAll();

// Get available books
$stmt = $db->query("
    SELECT b.*, a.name as author_name, c.name as category_name
    FROM books b
    LEFT JOIN authors a ON b.author_id = a.id
    LEFT JOIN categories c ON b.category_id = c.id
    WHERE b.available_copies > 0
    ORDER BY b.date_added DESC
    LIMIT 8
");
$availableBooks = $stmt->fetchAll();

include '../../includes/header.php';
?>

<div class="page-header">
    <h1 class="page-title">
        <i class="fas fa-book-reader"></i> Student Dashboard
    </h1>
    <p>Welcome, <?= e($studentInfo['full_name'] ?? getUserName()) ?>!</p>
</div>

<!-- Statistics Cards -->
<div class="card-grid">
    <div class="card stat-card info">
        <div class="card-body">
            <div class="stat-card-body">
                <div class="stat-card-info">
                    <h3><?= $currentBorrowed ?></h3>
                    <p>Currently Borrowed</p>
                </div>
                <div class="stat-card-icon">
                    <i class="fas fa-book-open"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card stat-card primary">
        <div class="card-body">
            <div class="stat-card-body">
                <div class="stat-card-info">
                    <h3><?= $totalBorrowed ?></h3>
                    <p>Total Borrowed</p>
                </div>
                <div class="stat-card-icon">
                    <i class="fas fa-book"></i>
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
    
    <div class="card stat-card warning">
        <div class="card-body">
            <div class="stat-card-body">
                <div class="stat-card-info">
                    <h3>$<?= number_format($totalFines, 2) ?></h3>
                    <p>Total Fines</p>
                </div>
                <div class="stat-card-icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Current Borrowings -->
<?php if (!empty($currentBorrowings)): ?>
    <div class="card" style="margin-top: 2rem;">
        <div class="card-header">
            <h3 class="card-title">My Current Borrowings</h3>
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
                        <?php foreach ($currentBorrowings as $borrow): ?>
                            <tr>
                                <td>
                                    <img src="<?= BASE_URL ?>uploads/books/<?= e($borrow['book_cover']) ?>" 
                                         class="book-cover-sm" 
                                         alt="<?= e($borrow['title']) ?>"
                                         onerror="this.src='<?= BASE_URL ?>assets/images/default-book.jpg'">
                                </td>
                                <td>
                                    <strong><?= e($borrow['title']) ?></strong><br>
                                    <small class="text-muted">ISBN: <?= e($borrow['isbn']) ?></small>
                                </td>
                                <td><?= e($borrow['author_name']) ?></td>
                                <td><?= formatDate($borrow['borrow_date']) ?></td>
                                <td>
                                    <?= formatDate($borrow['due_date']) ?><br>
                                    <?php if ($borrow['days_remaining'] < 0): ?>
                                        <span class="badge badge-danger">
                                            <?= abs($borrow['days_remaining']) ?> days overdue
                                        </span>
                                    <?php elseif ($borrow['days_remaining'] <= 3): ?>
                                        <span class="badge badge-warning">
                                            <?= $borrow['days_remaining'] ?> days left
                                        </span>
                                    <?php else: ?>
                                        <span class="badge badge-success">
                                            <?= $borrow['days_remaining'] ?> days left
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($borrow['days_remaining'] < 0): ?>
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
        <a href="books.php" class="btn btn-primary btn-sm">
            <i class="fas fa-book"></i> View All Books
        </a>
    </div>
    <div class="card-body">
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 1.5rem;">
            <?php foreach ($availableBooks as $book): ?>
                <div class="card">
                    <img src="<?= BASE_URL ?>uploads/books/<?= e($book['book_cover']) ?>" 
                         class="book-cover" 
                         alt="<?= e($book['title']) ?>"
                         onerror="this.src='<?= BASE_URL ?>assets/images/default-book.jpg'">
                    <div class="card-body">
                        <h4 style="font-size: 0.95rem; margin-bottom: 0.5rem;"><?= e($book['title']) ?></h4>
                        <p class="text-muted" style="font-size: 0.85rem; margin-bottom: 0.5rem;">
                            <?= e($book['author_name']) ?>
                        </p>
                        <span class="badge badge-primary"><?= e($book['category_name']) ?></span>
                        <p style="margin-top: 0.5rem; font-size: 0.85rem;">
                            <span class="badge badge-success"><?= $book['available_copies'] ?> available</span>
                        </p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Recent History -->
<?php if (!empty($recentHistory)): ?>
    <div class="card" style="margin-top: 2rem;">
        <div class="card-header">
            <h3 class="card-title">Recent Borrowing History</h3>
        </div>
        <div class="card-body">
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Book Title</th>
                            <th>Author</th>
                            <th>Borrow Date</th>
                            <th>Return Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentHistory as $history): ?>
                            <tr>
                                <td>
                                    <strong><?= e($history['title']) ?></strong><br>
                                    <small class="text-muted">ISBN: <?= e($history['isbn']) ?></small>
                                </td>
                                <td><?= e($history['author_name']) ?></td>
                                <td><?= formatDate($history['borrow_date']) ?></td>
                                <td><?= formatDate($history['return_date']) ?></td>
                                <td>
                                    <span class="badge badge-success">Returned</span>
                                    <?php if ($history['fine_amount'] > 0): ?>
                                        <br><small class="text-danger">Fine: $<?= number_format($history['fine_amount'], 2) ?></small>
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

<?php if ($studentId === 0): ?>
    <div class="alert alert-info" style="margin-top: 2rem;">
        <i class="fas fa-info-circle"></i>
        <strong>Note:</strong> Your account is not linked to a student record yet. 
        Please contact the librarian to complete your profile.
    </div>
<?php endif; ?>

<?php include '../../includes/footer.php'; ?>
