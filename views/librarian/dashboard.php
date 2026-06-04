<?php
require_once '../../config/config.php';
require_once '../../includes/functions.php';
requireRole('librarian');
$pageTitle = 'Librarian Dashboard';
$db = getDB();

$totalBooks = $db->query("SELECT COUNT(*) as total FROM books")->fetch()['total'];
$availableBooks = $db->query("SELECT SUM(available_copies) as total FROM books")->fetch()['total'] ?? 0;
$totalBorrowed = $db->query("SELECT COUNT(*) as total FROM borrowings WHERE status = 'borrowed'")->fetch()['total'];
$totalOverdue = $db->query("SELECT COUNT(*) as total FROM borrowings WHERE status = 'borrowed' AND due_date < CURDATE()")->fetch()['total'];
$totalReturned = $db->query("SELECT COUNT(*) as total FROM borrowings WHERE status = 'returned'")->fetch()['total'];
$totalStudents = $db->query("SELECT COUNT(*) as total FROM students")->fetch()['total'];
$totalCategories = $db->query("SELECT COUNT(*) as total FROM categories")->fetch()['total'];

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
    SELECT b.*, a.name as author_name, c.name as category_name
    FROM books b
    LEFT JOIN authors a ON b.author_id = a.id
    LEFT JOIN categories c ON b.category_id = c.id
    WHERE b.available_copies <= 2 AND b.available_copies > 0
    ORDER BY b.available_copies ASC
    LIMIT 5
");
$lowStockBooks = $stmt->fetchAll();

include '../../includes/header.php';
?>

<div class="page-header">
    <h1 class="page-title"><i class="fas fa-tachometer-alt"></i> Librarian Dashboard</h1>
    <p class="text-muted">Welcome back, <?= getUserName() ?>!</p>
</div>

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
                    <p>Available Copies</p>
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
                    <p>Currently Borrowed</p>
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

<div class="card-grid" style="margin-top: 1.5rem;">
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
    <div class="card stat-card info">
        <div class="card-body">
            <div class="stat-card-body">
                <div class="stat-card-info">
                    <h3><?= count($lowStockBooks) ?></h3>
                    <p>Low Stock Alert</p>
                </div>
                <div class="stat-card-icon"><i class="fas fa-exclamation"></i></div>
            </div>
        </div>
    </div>
</div>

<?php if ($totalOverdue > 0): ?>
<div class="alert alert-danger" style="margin-top: 2rem;">
    <i class="fas fa-exclamation-triangle"></i>
    <strong>Attention:</strong> There are <?= $totalOverdue ?> overdue book(s). 
    <a href="overdue.php" style="color: #721c24; text-decoration: underline;">View Overdue Books</a>
</div>
<?php endif; ?>

<!-- Quick Actions -->
<div class="card" style="margin-top: 2rem;">
    <div class="card-header">
        <h3 class="card-title">Quick Actions</h3>
    </div>
    <div class="card-body">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
            <a href="books.php" class="btn btn-primary" style="padding: 1.5rem; text-align: center;">
                <i class="fas fa-book" style="font-size: 2rem; display: block; margin-bottom: 0.5rem;"></i>
                Manage Books
            </a>
            <a href="borrowings.php" class="btn btn-info" style="padding: 1.5rem; text-align: center;">
                <i class="fas fa-book-open" style="font-size: 2rem; display: block; margin-bottom: 0.5rem;"></i>
                New Borrowing
            </a>
            <a href="returns.php" class="btn btn-success" style="padding: 1.5rem; text-align: center;">
                <i class="fas fa-undo" style="font-size: 2rem; display: block; margin-bottom: 0.5rem;"></i>
                Process Return
            </a>
            <a href="students.php" class="btn btn-warning" style="padding: 1.5rem; text-align: center;">
                <i class="fas fa-user-graduate" style="font-size: 2rem; display: block; margin-bottom: 0.5rem;"></i>
                Manage Students
            </a>
        </div>
    </div>
</div>

<!-- Recent Borrowings -->
<div class="card" style="margin-top: 2rem;">
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
                        <th>Borrow Date</th>
                        <th>Due Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recentBorrowings)): ?>
                        <tr>
                            <td colspan="5" style="text-align: center;">No recent borrowings</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($recentBorrowings as $borrowing): ?>
                            <tr>
                                <td>
                                    <strong><?= e($borrowing['student_name']) ?></strong><br>
                                    <small class="text-muted"><?= e($borrowing['student_code']) ?></small>
                                </td>
                                <td>
                                    <strong><?= e($borrowing['book_title']) ?></strong><br>
                                    <small class="text-muted"><?= e($borrowing['isbn']) ?></small>
                                </td>
                                <td><?= formatDate($borrowing['borrow_date']) ?></td>
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
<?php if (!empty($lowStockBooks)): ?>
<div class="card" style="margin-top: 2rem;">
    <div class="card-header">
        <h3 class="card-title">Low Stock Books</h3>
        <a href="books.php" class="btn btn-warning btn-sm">View All Books</a>
    </div>
    <div class="card-body">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Author</th>
                        <th>Category</th>
                        <th>Available Copies</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($lowStockBooks as $book): ?>
                        <tr>
                            <td><strong><?= e($book['title']) ?></strong></td>
                            <td><?= e($book['author_name']) ?></td>
                            <td><span class="badge badge-primary"><?= e($book['category_name']) ?></span></td>
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
    </div>
</div>
<?php endif; ?>

<?php include '../../includes/footer.php'; ?>
