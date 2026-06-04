<?php
require_once '../../config/config.php';
require_once '../../includes/functions.php';
requireRole('admin');
$pageTitle = 'Activity Logs';
$db = getDB();

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$totalLogs = $db->query("SELECT COUNT(*) as total FROM activity_logs")->fetch()['total'];
$pagination = paginate($page, $totalLogs);

$stmt = $db->prepare("
    SELECT l.*, u.username, u.full_name 
    FROM activity_logs l
    LEFT JOIN users u ON l.user_id = u.id
    ORDER BY l.created_at DESC
    LIMIT ? OFFSET ?
");
$stmt->execute([$pagination['limit'], $pagination['offset']]);
$logs = $stmt->fetchAll();

include '../../includes/header.php';
?>

<div class="page-header">
    <h1 class="page-title"><i class="fas fa-history"></i> Activity Logs</h1>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">System Activity (<?= $totalLogs ?>)</h3>
    </div>
    <div class="card-body">
        <div class="table-container">
            <table>
                <thead>
                    <tr><th>ID</th><th>User</th><th>Action</th><th>Description</th><th>IP Address</th><th>Date & Time</th></tr>
                </thead>
                <tbody>
                    <?php if (empty($logs)): ?>
                        <tr><td colspan="6" style="text-align: center;">No activity logs</td></tr>
                    <?php else: ?>
                        <?php foreach ($logs as $log): ?>
                            <tr>
                                <td><?= $log['id'] ?></td>
                                <td>
                                    <?= $log['full_name'] ? e($log['full_name']) : 'System' ?><br>
                                    <small class="text-muted"><?= $log['username'] ? '@' . e($log['username']) : '-' ?></small>
                                </td>
                                <td><span class="badge badge-info"><?= e($log['action']) ?></span></td>
                                <td><?= e($log['description']) ?></td>
                                <td><small class="text-muted"><?= e($log['ip_address']) ?></small></td>
                                <td><?= formatDate($log['created_at'], 'd M Y H:i:s') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <?php if ($pagination['total_pages'] > 1): ?>
            <nav style="margin-top: 1rem;">
                <ul class="pagination">
                    <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                        <li class="<?= $i === $page ? 'active' : '' ?>">
                            <?php if ($i === $page): ?>
                                <span><?= $i ?></span>
                            <?php else: ?>
                                <a href="?page=<?= $i ?>"><?= $i ?></a>
                            <?php endif; ?>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
