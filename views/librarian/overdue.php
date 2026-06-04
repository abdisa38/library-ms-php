<?php
/**
 * Overdue Books Management
 */

require_once '../../config/config.php';
require_once '../../includes/functions.php';

requireRole('librarian');

$pageTitle = 'Overdue Books';
$borrowing = new Borrowing();

// Update overdue status
$borrowing->updateOverdueStatus();

// Get overdue books
$overdueBooks = $borrowing->getOverdue();

include '../../includes/header.php';
?>

<div class="page-header">
    <h1 class="page-title">
        <i class="fas fa-exclamation-triangle"></i> Overdue Books
    </h1>
</div>

<?php if (!empty($overdueBooks)): ?>
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-circle"></i>
        <strong>Attention:</strong> There are currently <?= count($overdueBooks) ?> overdue book(s).
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">All Overdue Books (<?= count($overdueBooks) ?>)</h3>
        <button class="btn btn-primary btn-sm" onclick="window.print()">
            <i class="fas fa-print"></i> Print Report
        </button>
    </div>
    <div class="card-body">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Email</th>
                        <th>Book</th>
                        <th>ISBN</th>
                        <th>Borrow Date</th>
                        <th>Due Date</th>
                        <th>Days Overdue</th>
                        <th>Fine Amount</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($overdueBooks)): ?>
                        <tr>
                            <td colspan="9" style="text-align: center;">
                                <div style="padding: 2rem;">
                                    <i class="fas fa-check-circle" style="font-size: 3rem; color: var(--success-color);"></i>
                                    <h3 style="margin-top: 1rem;">No Overdue Books</h3>
                                    <p class="text-muted">All books are returned on time!</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($overdueBooks as $overdue): ?>
                            <?php 
                            $dailyRate = (float) getSetting('daily_fine_rate', 5);
                            $fineAmount = $overdue['days_overdue'] * $dailyRate;
                            ?>
                            <tr>
                                <td>
                                    <strong><?= e($overdue['student_name']) ?></strong><br>
                                    <small class="text-muted"><?= e($overdue['student_code']) ?></small>
                                </td>
                                <td><?= e($overdue['email']) ?></td>
                                <td><strong><?= e($overdue['book_title']) ?></strong></td>
                                <td><?= e($overdue['isbn']) ?></td>
                                <td><?= formatDate($overdue['borrow_date']) ?></td>
                                <td>
                                    <span class="text-danger">
                                        <i class="fas fa-calendar-times"></i>
                                        <?= formatDate($overdue['due_date']) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-danger">
                                        <?= $overdue['days_overdue'] ?> days
                                    </span>
                                </td>
                                <td>
                                    <strong class="text-danger">
                                        $<?= number_format($fineAmount, 2) ?>
                                    </strong>
                                </td>
                                <td>
                                    <a href="returns.php?borrow_id=<?= $overdue['id'] ?>" 
                                       class="btn btn-sm btn-success">
                                        <i class="fas fa-undo"></i> Process Return
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Summary Card -->
<?php if (!empty($overdueBooks)): ?>
    <div class="card" style="margin-top: 1.5rem;">
        <div class="card-header">
            <h3 class="card-title">Summary</h3>
        </div>
        <div class="card-body">
            <div class="card-grid">
                <div class="card stat-card danger">
                    <div class="card-body">
                        <div class="stat-card-body">
                            <div class="stat-card-info">
                                <h3><?= count($overdueBooks) ?></h3>
                                <p>Total Overdue</p>
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
                                <?php 
                                $totalDays = array_sum(array_column($overdueBooks, 'days_overdue'));
                                ?>
                                <h3><?= $totalDays ?></h3>
                                <p>Total Days Overdue</p>
                            </div>
                            <div class="stat-card-icon">
                                <i class="fas fa-calendar"></i>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card stat-card danger">
                    <div class="card-body">
                        <div class="stat-card-body">
                            <div class="stat-card-info">
                                <?php 
                                $dailyRate = (float) getSetting('daily_fine_rate', 5);
                                $totalFines = $totalDays * $dailyRate;
                                ?>
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
            
            <div style="margin-top: 1.5rem;">
                <p>
                    <i class="fas fa-info-circle"></i>
                    <strong>Fine Rate:</strong> $<?= number_format($dailyRate, 2) ?> per day
                </p>
                <p class="text-muted">
                    You can change the daily fine rate from Settings page.
                </p>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php include '../../includes/footer.php'; ?>
