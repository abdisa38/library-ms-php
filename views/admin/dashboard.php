<?php
/**
 * Admin Dashboard
 */

require_once '../../config/config.php';
require_once '../../includes/functions.php';

requireRole('admin');

$pageTitle = 'Admin Dashboard';

// Get statistics
$db = getDB();

// Total books
$stmt = $db->query("SELECT COUNT(*) as total FROM books");
$totalBooks = $stmt->fetch()['total'];

// Total categories
$stmt = $db->query("SELECT COUNT(*) as total FROM categories");
$totalCategories = $stmt->fetch()['total'];

// Total students
$stmt = $db->query("SELECT COUNT(*) as total FROM students");
$totalStudents = $stmt->fetch()['total'];

// Total borrowed books
$stmt = $db->query("SELECT COUNT(*) as total FROM borrowings WHERE status = 'borrowed'");
$totalBorrowed = $stmt->fetch()['total'];

// Total returned books
$stmt = $db->query("SELECT COUNT(*) as total FROM borrowings WHERE status = 'returned'");
$totalReturned = $stmt->fetch()['total'];

// Total overdue books
$stmt = $db->query("SELECT COUNT(*) as total FROM borrowings WHERE status = 'borrowed' AND due_date < CURDATE()");
$totalOverdue = $stmt->fetch()['total'];

// Available books
$stmt = $db->query("SELECT SUM(available_copies) as total FROM books");
$availableBooks = $stmt->fetch()['total'] ?? 0;

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

// Monthly borrow statistics
$stmt = $db->query("
    SELECT 
        MONTH(borrow_date) as month,
        COUNT(*) as count
    FROM borrowings
    WHERE YEAR(borrow_date) = YEAR(CURDATE())
    GROUP BY MONTH(borrow_date)
    ORDER BY MONTH(borrow_date)
");
$monthlyStats = $stmt->fetchAll();

// Category statistics
$stmt = $db->query("
    SELECT c.name, COUNT(b.id) as book_count
    FROM categories c
    LEFT JOIN books b ON c.id = b.category_id
    GROUP BY c.id, c.name
    ORDER BY book_count DESC
    LIMIT 5
");
$categoryStats = $stmt->fetchAll();

include '../../includes/header.php';
?>

<div class="page-header">
    <h1 class="page-title">
        <i class="fas fa-tachometer-alt"></i> Admin Dashboard
    </h1>
    <ul class="breadcrumb">
        <li>Home</li>
        <li>Dashboard</li>
    </ul>
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
                <div class="stat-card-icon">
                    <i class="fas fa-book"></i>
                </div>
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
                <div class="stat-card-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
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
                <div class="stat-card-icon">
                    <i class="fas fa-book-open"></i>
                </div>
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
                <div class="stat-card-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
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
                <div class="stat-card-icon">
                    <i class="fas fa-undo"></i>
                </div>
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
                <div class="stat-card-icon">
                    <i class="fas fa-user-graduate"></i>
                </div>
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
                <div class="stat-card-icon">
                    <i class="fas fa-layer-group"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Charts -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 1.5rem; margin-top: 2rem;">
    <!-- Monthly Borrow Chart -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Monthly Borrowing Report</h3>
        </div>
        <div class="card-body">
            <canvas id="monthlyChart"></canvas>
        </div>
    </div>
    
    <!-- Category Chart -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Books by Category</h3>
        </div>
        <div class="card-body">
            <canvas id="categoryChart"></canvas>
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

<?php
$customJS = '
<script>
// Monthly Chart
const monthlyCtx = document.getElementById("monthlyChart");
if (monthlyCtx) {
    const monthNames = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
    const monthlyData = new Array(12).fill(0);
    ' . json_encode($monthlyStats) . '.forEach(stat => {
        monthlyData[stat.month - 1] = stat.count;
    });
    
    new Chart(monthlyCtx, {
        type: "line",
        data: {
            labels: monthNames,
            datasets: [{
                label: "Borrowings",
                data: monthlyData,
                borderColor: "#4e73df",
                backgroundColor: "rgba(78, 115, 223, 0.1)",
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
}

// Category Chart
const categoryCtx = document.getElementById("categoryChart");
if (categoryCtx) {
    const categoryData = ' . json_encode($categoryStats) . ';
    const labels = categoryData.map(c => c.name);
    const data = categoryData.map(c => parseInt(c.book_count));
    
    new Chart(categoryCtx, {
        type: "doughnut",
        data: {
            labels: labels,
            datasets: [{
                data: data,
                backgroundColor: [
                    "#4e73df",
                    "#1cc88a",
                    "#36b9cc",
                    "#f6c23e",
                    "#e74a3b"
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true
        }
    });
}
</script>
';

include '../../includes/footer.php';
?>
