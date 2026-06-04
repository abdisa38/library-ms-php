<?php
/**
 * Librarian Dashboard
 */

require_once '../../config/config.php';
require_once '../../includes/functions.php';

requireRole('librarian');

$pageTitle = 'Librarian Dashboard';
$db = getDB();

// Get statistics
$stmt = $db->query("SELECT COUNT(*) as total FROM books");
$totalBooks = $stmt->fetch()['total'];

$stmt = $db->query("SELECT SUM(available_copies) as total FROM books");
$availableBooks = $stmt->fetch()['total'] ?? 0;

$stmt = $db->query("SELECT COUNT(*) as total FROM borrowings WHERE status = 'borrowed'");
$totalBorrowed = $stmt->fetch()['total'];

$stmt = $db->query("SELECT COUNT(*) as total FROM borrowings WHERE status = 'borrowed' AND due_date < CURDATE()");
$totalOverdue = $stmt->fetch()['total'];

$stmt = $db->query("SELECT COUNT(*) as total FROM borrowings WHERE status = 'returned'");
$totalReturned = $stmt->fetch()['total'];

$stmt = $db->query("SELECT COUNT(*) as total FROM students");
$totalStudents = $stmt->fetch()['total'];

$stmt = $db->query("SELECT COUNT(*) as total FROM categories");
$totalCategories = $stmt->fetch()['total'];

// Recent borrowings
$stmt = $db->prepare("
    SELECT b.*, s.full_name as student_name, s.student_id as student_code,
           bk.title as book_title, bk.isbn
    FROM borrowings b
    JOIN students s ON b.student_id = s.id
    JOIN books bk ON b.book_id = bk.id
    ORDER BY b.created_at DESC
    LIMIT 5
");
$stmt->execute();
$recentBorrowings = $stmt->fetchAll();

// Low stock books
$stmt = $db->query("
    SELECT b.*, a.name as author_name
    FROM books b
    LEFT JOIN authors a ON b.author_id = a.id
    WHERE b.available_copies <= 2 AND b.available_copies > 0
    ORDER BY b.available_copies ASC
    LIMIT 5
");
$lowStockBooks = $stmt->fetchAll();

include '../../includes/header.php';
?>

<div class="page-header">
    <h1 class="page-title">
        <i class="fas fa-tachometer-alt"></i> Librarian Dashboard
    </h1>
    <p class="text-muted">Welcome back, <?= getUserName() ?>!</p>
</div>

<!-- Statistics Cards -->
<div class="card-grid">
    <div class="card stat-card primary">
        <div class="card-body">
            <div class="stat-card-body">
                <div class="stat-card-info">
                    <h3><?= number_format($totalBooks) ?></h3>
                    <p>Total Books</p>
                </div>
                <div class="stat-card-icon"><i class="fas fa-book"></i></div>
            </div>
        </div>
    </div>
    
    <div class="card stat-card success">
        <div class="card-body">
            <div class="stat-card-body">
                <div class="stat-card-info">
                    <h3><?= number_format($availableBooks) ?></h3>
                    <p>Available Books</p>
                </div>
                <div class="stat-card-icon"><i class="fas fa-check-circle"></i></div>
            </div>
        </div>
    </div>
    
    <div class="card stat-card info">
        <div class="card-body">
            <div class="stat-card-body">
                <div class="stat-card-info">
                    <h3><?= number_format($totalBorrowed) ?></h3>
                    <p>Borrowed Books</p>
                </div>
                <div class="stat-card-icon"><i class="fas fa-book-open"></i></div>
            </div>
        </div>
    </div>
    
    <div class="card stat-card danger">
        <div class="card-body">
            <div class="stat-card-body">
                <div class="stat-card-info">
                    <h3><?= number_format($totalOverdue) ?></h3>
                    <p>Overdue Books</p>
                </div>
                <div class="stat-card-icon"><i class="fas fa-exclamation-triangle"></i></div>
            </div>
        </div>
    </div>
</div>

<div class="card-grid">
    <div class="card stat-card success">
        <div class="card-body">
            <div class="stat-card-body">
                <div class="stat-card-info">
                    <h3><?= number_format($totalReturned) ?></h3>
                    <p>Returned Books</p>
                </div>
                <div class="stat-card-icon"><i class="fas fa-undo"></i></div>
            </div>
        </div>
    </div>
    
    <div class="card stat-card warning">
        <div class="card-body">
            <div class="stat-card-body">
                <div class="stat-card-info">
                    <h3><?= number_format($totalStudents) ?></h3>
                    <p>Total Students</p>
                </div>
                <div class="stat-card-icon"><i class="fas fa-user-graduate"></i></div>
            </div>
        </div>
    </div>
    
    <div class="card stat-card primary">
        <div class="card-body">
            <div class="stat-card-body">
                <div class="stat-card-info">
                    <h3><?= number_format($totalCategories) ?></h3>
                    <p>Categories</p>
                </div>
                <div class="stat-card-icon"><i class="fas fa-layer-group"></i></div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="card" style="margin-top: 2rem;">
    <div class="card-header">
        <h3 class="card-title">Quick Actions</h3>
    </div>
    <div class="card-body">
        <div class="btn-group" style="gap: 1rem; flex-wrap: wrap;">
            <a href="borrowings.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> New Borrowing
            </a>
            <a href="returns.php" class="btn btn-success">
                <i class="fas fa-undo"></i> Process Return
            </a>
            <a href="books.php" class="btn btn-info">
                <i class="fas fa-book"></i> Add Book
            </a>
            <a href="students.php" class="btn btn-warning">
                <i class="fas fa-user-plus"></i> Add Student
            </a>
            <a href="overdue.php" class="btn btn-danger">
                <i class="fas fa-exclamation-triangle"></i> View Overdue
            </a>
            <a href="reports.php" class="btn btn-secondary">
                <i class="fas fa-chart-bar"></i> Generate Report
            </a>
        </div>
    </div>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 1.5rem; margin-top: 2rem;">
    <!-- Recent Borrowings -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Recent Borrowings</h3>
            <a href="borrowings.php" class="btn btn-primary btn-sm">View All</a>
        </div>
        <div class="card-body">
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Book</th>
                            <th>Due Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recentBorrowings)): ?>
                            <tr>
                                <td colspan="4" style="text-align: center;">No recent borrowings</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($recentBorrowings as $borrowing): ?>
                                <tr>
                                    <td>
                                        <strong><?= e($borrowing['student_name']) ?></strong><br>
                                        <small class="text-muted"><?= e($borrowing['student_code']) ?></small>
                                    </td>
                                    <td>
                                        <small><?= e($borrowing['book_title']) ?></small>
                                    </td>
                                    <td><?= formatDate($borrowing['due_date']) ?></td>
                                    <td>
                                        <?php
                                        $badgeClass = 'secondary';
                                        if ($borrowing['status'] === 'borrowed') $badgeClass = 'info';
                                        if ($borrowing['status'] === 'returned') $badgeClass = 'success';
                                        if ($borrowing['status'] === 'overdue') $badgeClass = 'danger';
                                        ?>
                                        <span class="badge badge-<?= $badgeClass ?>">
                                            <?= ucfirst($borrowing['status']) ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Low Stock Alert -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Low Stock Alert</h3>
            <a href="books.php" class="btn btn-warning btn-sm">Manage Books</a>
        </div>
        <div class="card-body">
            <?php if (empty($lowStockBooks)): ?>
                <div style="text-align: center; padding: 2rem;">
                    <i class="fas fa-check-circle" style="font-size: 3rem; color: var(--success-color);"></i>
                    <p style="margin-top: 1rem;" class="text-muted">All books well stocked!</p>
                </div>
            <?php else: ?>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Book Title</th>
                                <th>Author</th>
                                <th>Available</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($lowStockBooks as $book): ?>
                                <tr>
                                    <td><strong><?= e($book['title']) ?></strong></td>
                                    <td><?= e($book['author_name']) ?></td>
                                    <td>
                                        <span class="badge badge-warning">
                                            <?= $book['available_copies'] ?> left
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
</div>

<?php include '../../includes/footer.php'; ?>
