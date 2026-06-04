<?php
/**
 * Reports Page
 */

require_once '../../config/config.php';
require_once '../../includes/functions.php';

requireRole('librarian');

$pageTitle = 'Reports';
$db = getDB();

// Date range filter
$startDate = isset($_GET['start_date']) ? sanitize($_GET['start_date']) : date('Y-m-01');
$endDate = isset($_GET['end_date']) ? sanitize($_GET['end_date']) : date('Y-m-d');

// Export functionality
if (isset($_GET['export']) && isset($_GET['type'])) {
    $exportType = $_GET['type'];
    
    switch ($exportType) {
        case 'borrowings':
            $stmt = $db->prepare("
                SELECT b.id, s.student_id, s.full_name, bk.title, bk.isbn,
                       b.borrow_date, b.due_date, b.return_date, b.status, b.fine_amount
                FROM borrowings b
                JOIN students s ON b.student_id = s.id
                JOIN books bk ON b.book_id = bk.id
                WHERE b.borrow_date BETWEEN ? AND ?
                ORDER BY b.borrow_date DESC
            ");
            $stmt->execute([$startDate, $endDate]);
            $data = $stmt->fetchAll();
            
            $headers = ['ID', 'Student ID', 'Student Name', 'Book Title', 'ISBN', 
                       'Borrow Date', 'Due Date', 'Return Date', 'Status', 'Fine'];
            
            exportToCSV('borrowings_report.csv', $data, $headers);
            break;
            
        case 'inventory':
            $stmt = $db->query("
                SELECT b.id, b.isbn, b.title, a.name as author, c.name as category,
                       p.name as publisher, b.quantity, b.available_copies, b.shelf_number
                FROM books b
                LEFT JOIN authors a ON b.author_id = a.id
                LEFT JOIN categories c ON b.category_id = c.id
                LEFT JOIN publishers p ON b.publisher_id = p.id
                ORDER BY b.title
            ");
            $data = $stmt->fetchAll();
            
            $headers = ['ID', 'ISBN', 'Title', 'Author', 'Category', 
                       'Publisher', 'Total Quantity', 'Available', 'Shelf'];
            
            exportToCSV('inventory_report.csv', $data, $headers);
            break;
    }
}

// Get statistics
$stats = [];

// Borrowing statistics
$stmt = $db->prepare("
    SELECT 
        COUNT(*) as total_borrowings,
        SUM(CASE WHEN status = 'borrowed' THEN 1 ELSE 0 END) as active_borrowings,
        SUM(CASE WHEN status = 'returned' THEN 1 ELSE 0 END) as returned_books,
        SUM(CASE WHEN status = 'overdue' THEN 1 ELSE 0 END) as overdue_books
    FROM borrowings
    WHERE borrow_date BETWEEN ? AND ?
");
$stmt->execute([$startDate, $endDate]);
$stats['borrowing'] = $stmt->fetch();

// Fine statistics
$stmt = $db->prepare("
    SELECT 
        COUNT(*) as total_fines,
        SUM(fine_amount) as total_fine_amount,
        SUM(paid_amount) as total_paid,
        SUM(fine_amount - paid_amount) as total_outstanding
    FROM fines
    WHERE created_at BETWEEN ? AND ?
");
$stmt->execute([$startDate, $endDate]);
$stats['fines'] = $stmt->fetch();

// Top borrowed books
$stmt = $db->prepare("
    SELECT bk.title, bk.isbn, a.name as author, COUNT(b.id) as borrow_count
    FROM borrowings b
    JOIN books bk ON b.book_id = bk.id
    LEFT JOIN authors a ON bk.author_id = a.id
    WHERE b.borrow_date BETWEEN ? AND ?
    GROUP BY b.book_id
    ORDER BY borrow_count DESC
    LIMIT 10
");
$stmt->execute([$startDate, $endDate]);
$topBooks = $stmt->fetchAll();

// Most active students
$stmt = $db->prepare("
    SELECT s.student_id, s.full_name, s.department, COUNT(b.id) as borrow_count
    FROM borrowings b
    JOIN students s ON b.student_id = s.id
    WHERE b.borrow_date BETWEEN ? AND ?
    GROUP BY b.student_id
    ORDER BY borrow_count DESC
    LIMIT 10
");
$stmt->execute([$startDate, $endDate]);
$topStudents = $stmt->fetchAll();

include '../../includes/header.php';
?>

<div class="page-header">
    <h1 class="page-title">
        <i class="fas fa-chart-bar"></i> Reports & Analytics
    </h1>
</div>

<!-- Date Range Filter -->
<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-body">
        <form method="GET" class="form-row" style="align-items: flex-end;">
            <div class="form-group">
                <label class="form-label">Start Date</label>
                <input type="date" name="start_date" class="form-control" value="<?= $startDate ?>">
            </div>
            
            <div class="form-group">
                <label class="form-label">End Date</label>
                <input type="date" name="end_date" class="form-control" value="<?= $endDate ?>">
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-filter"></i> Filter
                </button>
                <a href="reports.php" class="btn btn-secondary">
                    <i class="fas fa-redo"></i> Reset
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Statistics Cards -->
<div class="card-grid">
    <div class="card stat-card primary">
        <div class="card-body">
            <div class="stat-card-body">
                <div class="stat-card-info">
                    <h3><?= number_format($stats['borrowing']['total_borrowings']) ?></h3>
                    <p>Total Borrowings</p>
                </div>
                <div class="stat-card-icon">
                    <i class="fas fa-book-open"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card stat-card info">
        <div class="card-body">
            <div class="stat-card-body">
                <div class="stat-card-info">
                    <h3><?= number_format($stats['borrowing']['active_borrowings']) ?></h3>
                    <p>Active Borrowings</p>
                </div>
                <div class="stat-card-icon">
                    <i class="fas fa-bookmark"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card stat-card success">
        <div class="card-body">
            <div class="stat-card-body">
                <div class="stat-card-info">
                    <h3><?= number_format($stats['borrowing']['returned_books']) ?></h3>
                    <p>Returned Books</p>
                </div>
                <div class="stat-card-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card stat-card danger">
        <div class="card-body">
            <div class="stat-card-body">
                <div class="stat-card-info">
                    <h3><?= number_format($stats['borrowing']['overdue_books']) ?></h3>
                    <p>Overdue Books</p>
                </div>
                <div class="stat-card-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Export Options -->
<div class="card" style="margin-top: 1.5rem;">
    <div class="card-header">
        <h3 class="card-title">Export Reports</h3>
    </div>
    <div class="card-body">
        <div class="btn-group">
            <a href="?export=1&type=borrowings&start_date=<?= $startDate ?>&end_date=<?= $endDate ?>" 
               class="btn btn-success">
                <i class="fas fa-file-excel"></i> Export Borrowings (CSV)
            </a>
            <a href="?export=1&type=inventory" class="btn btn-success">
                <i class="fas fa-file-excel"></i> Export Inventory (CSV)
            </a>
            <button onclick="window.print()" class="btn btn-primary">
                <i class="fas fa-print"></i> Print Report
            </button>
        </div>
    </div>
</div>

<!-- Top Borrowed Books -->
<div class="card" style="margin-top: 1.5rem;">
    <div class="card-header">
        <h3 class="card-title">Top 10 Borrowed Books</h3>
    </div>
    <div class="card-body">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Title</th>
                        <th>ISBN</th>
                        <th>Author</th>
                        <th>Times Borrowed</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($topBooks)): ?>
                        <tr>
                            <td colspan="5" style="text-align: center;">No data available</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($topBooks as $index => $book): ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td><strong><?= e($book['title']) ?></strong></td>
                                <td><?= e($book['isbn']) ?></td>
                                <td><?= e($book['author']) ?></td>
                                <td>
                                    <span class="badge badge-primary"><?= $book['borrow_count'] ?> times</span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Most Active Students -->
<div class="card" style="margin-top: 1.5rem;">
    <div class="card-header">
        <h3 class="card-title">Top 10 Most Active Students</h3>
    </div>
    <div class="card-body">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Student ID</th>
                        <th>Name</th>
                        <th>Department</th>
                        <th>Books Borrowed</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($topStudents)): ?>
                        <tr>
                            <td colspan="5" style="text-align: center;">No data available</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($topStudents as $index => $student): ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td><strong><?= e($student['student_id']) ?></strong></td>
                                <td><?= e($student['full_name']) ?></td>
                                <td><?= e($student['department']) ?></td>
                                <td>
                                    <span class="badge badge-success"><?= $student['borrow_count'] ?> books</span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Fine Statistics -->
<?php if ($stats['fines']['total_fines'] > 0): ?>
    <div class="card" style="margin-top: 1.5rem;">
        <div class="card-header">
            <h3 class="card-title">Fine Statistics</h3>
        </div>
        <div class="card-body">
            <div class="card-grid">
                <div class="card stat-card warning">
                    <div class="card-body">
                        <div class="stat-card-body">
                            <div class="stat-card-info">
                                <h3><?= number_format($stats['fines']['total_fines']) ?></h3>
                                <p>Total Fines</p>
                            </div>
                            <div class="stat-card-icon">
                                <i class="fas fa-receipt"></i>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card stat-card danger">
                    <div class="card-body">
                        <div class="stat-card-body">
                            <div class="stat-card-info">
                                <h3>$<?= number_format($stats['fines']['total_fine_amount'], 2) ?></h3>
                                <p>Total Amount</p>
                            </div>
                            <div class="stat-card-icon">
                                <i class="fas fa-dollar-sign"></i>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card stat-card success">
                    <div class="card-body">
                        <div class="stat-card-body">
                            <div class="stat-card-info">
                                <h3>$<?= number_format($stats['fines']['total_paid'], 2) ?></h3>
                                <p>Paid</p>
                            </div>
                            <div class="stat-card-icon">
                                <i class="fas fa-check-double"></i>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card stat-card warning">
                    <div class="card-body">
                        <div class="stat-card-body">
                            <div class="stat-card-info">
                                <h3>$<?= number_format($stats['fines']['total_outstanding'], 2) ?></h3>
                                <p>Outstanding</p>
                            </div>
                            <div class="stat-card-icon">
                                <i class="fas fa-exclamation"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php include '../../includes/footer.php'; ?>
