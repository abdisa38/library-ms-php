<?php
require_once '../../config/config.php';
require_once '../../includes/functions.php';
requireRole('librarian');
$pageTitle = 'Librarian Dashboard';
$db = getDB();

$totalBooks = $db->query("SELECT COUNT(*) as total FROM books")->fetch()['total'];
$totalBorrowed = $db->query("SELECT COUNT(*) as total FROM borrowings WHERE status = 'borrowed'")->fetch()['total'];
$totalOverdue = $db->query("SELECT COUNT(*) as total FROM borrowings WHERE status = 'borrowed' AND due_date < CURDATE()")->fetch()['total'];
$totalStudents = $db->query("SELECT COUNT(*) as total FROM students")->fetch()['total'];

include '../../includes/header.php';
?>

<div class="page-header">
    <h1 class="page-title"><i class="fas fa-tachometer-alt"></i> Librarian Dashboard</h1>
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
    <div class="card stat-card success">
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
</div>

<div class="card" style="margin-top: 2rem;">
    <div class="card-body" style="text-align: center; padding: 3rem;">
        <i class="fas fa-book-reader" style="font-size: 5rem; color: var(--primary-color); opacity: 0.5;"></i>
        <h2 style="margin-top: 1rem;">Welcome to Librarian Dashboard</h2>
        <p class="text-muted">Use the sidebar menu to manage books, students, and borrowings.</p>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
