<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Dashboard' ?> - <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="wrapper">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-brand">
                <a href="<?= BASE_URL ?>views/<?= getUserRole() ?>/dashboard.php">
                    <i class="fas fa-book-reader"></i>
                    <span>Library MS</span>
                </a>
            </div>
            
            <ul class="sidebar-menu">
                <li>
                    <a href="<?= BASE_URL ?>views/<?= getUserRole() ?>/dashboard.php">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                
                <?php if (getUserRole() === 'admin' || getUserRole() === 'librarian'): ?>
                    <div class="sidebar-heading">Book Management</div>
                    
                    <li>
                        <a href="<?= BASE_URL ?>views/<?= getUserRole() ?>/books.php">
                            <i class="fas fa-book"></i>
                            <span>Books</span>
                        </a>
                    </li>
                    
                    <li>
                        <a href="<?= BASE_URL ?>views/<?= getUserRole() ?>/categories.php">
                            <i class="fas fa-layer-group"></i>
                            <span>Categories</span>
                        </a>
                    </li>
                    
                    <li>
                        <a href="<?= BASE_URL ?>views/<?= getUserRole() ?>/authors.php">
                            <i class="fas fa-pen-fancy"></i>
                            <span>Authors</span>
                        </a>
                    </li>
                    
                    <li>
                        <a href="<?= BASE_URL ?>views/<?= getUserRole() ?>/publishers.php">
                            <i class="fas fa-building"></i>
                            <span>Publishers</span>
                        </a>
                    </li>
                    
                    <div class="sidebar-heading">Student Management</div>
                    
                    <li>
                        <a href="<?= BASE_URL ?>views/<?= getUserRole() ?>/students.php">
                            <i class="fas fa-user-graduate"></i>
                            <span>Students</span>
                        </a>
                    </li>
                    
                    <div class="sidebar-heading">Circulation</div>
                    
                    <li>
                        <a href="<?= BASE_URL ?>views/<?= getUserRole() ?>/borrowings.php">
                            <i class="fas fa-book-open"></i>
                            <span>Borrowings</span>
                        </a>
                    </li>
                    
                    <li>
                        <a href="<?= BASE_URL ?>views/<?= getUserRole() ?>/returns.php">
                            <i class="fas fa-undo"></i>
                            <span>Returns</span>
                        </a>
                    </li>
                    
                    <li>
                        <a href="<?= BASE_URL ?>views/<?= getUserRole() ?>/overdue.php">
                            <i class="fas fa-exclamation-triangle"></i>
                            <span>Overdue Books</span>
                        </a>
                    </li>
                    
                    <div class="sidebar-heading">Reports</div>
                    
                    <li>
                        <a href="<?= BASE_URL ?>views/<?= getUserRole() ?>/reports.php">
                            <i class="fas fa-chart-bar"></i>
                            <span>Reports</span>
                        </a>
                    </li>
                <?php endif; ?>
                
                <?php if (getUserRole() === 'admin'): ?>
                    <div class="sidebar-heading">Administration</div>
                    
                    <li>
                        <a href="<?= BASE_URL ?>views/admin/users.php">
                            <i class="fas fa-users"></i>
                            <span>Users</span>
                        </a>
                    </li>
                    
                    <li>
                        <a href="<?= BASE_URL ?>views/admin/settings.php">
                            <i class="fas fa-cog"></i>
                            <span>Settings</span>
                        </a>
                    </li>
                    
                    <li>
                        <a href="<?= BASE_URL ?>views/admin/logs.php">
                            <i class="fas fa-history"></i>
                            <span>Activity Logs</span>
                        </a>
                    </li>
                <?php endif; ?>
                
                <?php if (getUserRole() === 'student'): ?>
                    <div class="sidebar-heading">Library</div>
                    
                    <li>
                        <a href="<?= BASE_URL ?>views/student/books.php">
                            <i class="fas fa-book"></i>
                            <span>Browse Books</span>
                        </a>
                    </li>
                    
                    <li>
                        <a href="<?= BASE_URL ?>views/student/borrowings.php">
                            <i class="fas fa-book-open"></i>
                            <span>My Borrowings</span>
                        </a>
                    </li>
                <?php endif; ?>
                
                <div class="sidebar-heading">Account</div>
                
                <li>
                    <a href="<?= BASE_URL ?>views/<?= getUserRole() ?>/profile.php">
                        <i class="fas fa-user"></i>
                        <span>My Profile</span>
                    </a>
                </li>
                
                <li>
                    <a href="<?= BASE_URL ?>views/auth/logout.php" onclick="return confirm('Are you sure you want to logout?')">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </li>
            </ul>
        </aside>
        
        <!-- Main Content -->
        <div class="main-content">
            <!-- Topbar -->
            <nav class="topbar">
                <div class="topbar-left">
                    <button class="sidebar-toggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    
                    <div class="topbar-search">
                        <input type="text" placeholder="Search books, students..." id="globalSearch">
                        <button type="button">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
                
                <div class="topbar-right">
                    <div class="topbar-notification">
                        <i class="fas fa-bell"></i>
                        <span class="notification-badge">3</span>
                    </div>
                    
                    <div class="topbar-user">
                        <img src="<?= BASE_URL ?>uploads/profiles/<?= $_SESSION['profile_picture'] ?? 'default.jpg' ?>" 
                             alt="Profile" 
                             onerror="this.src='<?= BASE_URL ?>assets/images/default-avatar.png'">
                        <div class="topbar-user-info">
                            <div class="topbar-user-name"><?= getUserName() ?></div>
                            <div class="topbar-user-role"><?= ucfirst(getUserRole()) ?></div>
                        </div>
                    </div>
                </div>
            </nav>
            
            <!-- Page Content -->
            <div class="content">
                <?php displayFlash(); ?>
