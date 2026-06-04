<?php
/**
 * Helper Functions
 * Library Management System
 */

/**
 * Sanitize input data
 */
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

/**
 * Redirect to a specific page
 */
function redirect($page) {
    header("Location: " . BASE_URL . $page);
    exit();
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Check if user has specific role
 */
function hasRole($role) {
    return isset($_SESSION['role']) && $_SESSION['role'] === $role;
}

/**
 * Require login
 */
function requireLogin() {
    if (!isLoggedIn()) {
        $_SESSION['error'] = 'Please login to continue';
        redirect('index.php');
    }
}

/**
 * Require specific role
 */
function requireRole($role) {
    requireLogin();
    if (!hasRole($role)) {
        $_SESSION['error'] = 'Access denied';
        redirect('index.php');
    }
}

/**
 * Set flash message
 */
function setFlash($type, $message) {
    $_SESSION['flash_type'] = $type;
    $_SESSION['flash_message'] = $message;
}

/**
 * Display flash message
 */
function displayFlash() {
    if (isset($_SESSION['flash_message'])) {
        $type = $_SESSION['flash_type'] ?? 'info';
        $message = $_SESSION['flash_message'];
        echo "<div class='alert alert-{$type} alert-dismissible'>
                <button type='button' class='close' onclick='this.parentElement.remove()'>&times;</button>
                {$message}
              </div>";
        unset($_SESSION['flash_message'], $_SESSION['flash_type']);
    }
}

/**
 * Format date
 */
function formatDate($date, $format = DISPLAY_DATE_FORMAT) {
    return date($format, strtotime($date));
}

/**
 * Calculate days difference
 */
function daysDifference($date1, $date2) {
    $datetime1 = new DateTime($date1);
    $datetime2 = new DateTime($date2);
    $interval = $datetime1->diff($datetime2);
    return $interval->days;
}

/**
 * Generate random token
 */
function generateToken($length = 32) {
    return bin2hex(random_bytes($length));
}

/**
 * Upload file
 */
function uploadFile($file, $path, $allowedTypes = ALLOWED_IMAGE_TYPES) {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'No file uploaded or upload error'];
    }
    
    // Check file size
    if ($file['size'] > MAX_FILE_SIZE) {
        return ['success' => false, 'message' => 'File size exceeds maximum allowed'];
    }
    
    // Check file type
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedTypes)) {
        return ['success' => false, 'message' => 'Invalid file type'];
    }
    
    // Generate unique filename
    $filename = uniqid() . '_' . time() . '.' . $ext;
    $destination = $path . $filename;
    
    // Create directory if not exists
    if (!is_dir($path)) {
        mkdir($path, 0777, true);
    }
    
    // Move file
    if (move_uploaded_file($file['tmp_name'], $destination)) {
        return ['success' => true, 'filename' => $filename];
    }
    
    return ['success' => false, 'message' => 'Failed to move uploaded file'];
}

/**
 * Delete file
 */
function deleteFile($path) {
    if (file_exists($path) && is_file($path)) {
        return unlink($path);
    }
    return false;
}

/**
 * Pagination
 */
function paginate($currentPage, $totalRecords, $recordsPerPage = RECORDS_PER_PAGE) {
    $totalPages = ceil($totalRecords / $recordsPerPage);
    $offset = ($currentPage - 1) * $recordsPerPage;
    
    return [
        'total_pages' => $totalPages,
        'current_page' => $currentPage,
        'offset' => $offset,
        'limit' => $recordsPerPage,
        'total_records' => $totalRecords
    ];
}

/**
 * Get setting value
 */
function getSetting($key, $default = null) {
    try {
        $db = getDB();
        $stmt = $db->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        $result = $stmt->fetch();
        return $result ? $result['setting_value'] : $default;
    } catch (Exception $e) {
        return $default;
    }
}

/**
 * Log activity
 */
function logActivity($userId, $action, $description = null) {
    try {
        $db = getDB();
        $stmt = $db->prepare("
            INSERT INTO activity_logs (user_id, action, description, ip_address, user_agent)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $userId,
            $action,
            $description,
            $_SERVER['REMOTE_ADDR'] ?? null,
            $_SERVER['HTTP_USER_AGENT'] ?? null
        ]);
    } catch (Exception $e) {
        error_log("Failed to log activity: " . $e->getMessage());
    }
}

/**
 * Create notification
 */
function createNotification($userId, $title, $message, $type = 'info') {
    try {
        $db = getDB();
        $stmt = $db->prepare("
            INSERT INTO notifications (user_id, title, message, type)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$userId, $title, $message, $type]);
    } catch (Exception $e) {
        error_log("Failed to create notification: " . $e->getMessage());
    }
}

/**
 * Escape output for HTML
 */
function e($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Generate CSRF token
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Get user's full name
 */
function getUserName() {
    return $_SESSION['full_name'] ?? 'User';
}

/**
 * Get user's role
 */
function getUserRole() {
    return $_SESSION['role'] ?? 'guest';
}

/**
 * Calculate fine amount
 */
function calculateFine($dueDate, $returnDate = null) {
    $return = $returnDate ?? date('Y-m-d');
    $days = daysDifference($dueDate, $return);
    
    if ($days <= 0) {
        return 0;
    }
    
    $dailyRate = (float) getSetting('daily_fine_rate', 5.00);
    return $days * $dailyRate;
}

/**
 * Check if book is available
 */
function isBookAvailable($bookId) {
    try {
        $db = getDB();
        $stmt = $db->prepare("SELECT available_copies FROM books WHERE id = ?");
        $stmt->execute([$bookId]);
        $book = $stmt->fetch();
        return $book && $book['available_copies'] > 0;
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Check if student can borrow more books
 */
function canBorrowMore($studentId) {
    try {
        $db = getDB();
        $maxBooks = (int) getSetting('max_books_per_student', 3);
        
        $stmt = $db->prepare("
            SELECT COUNT(*) as borrowed_count 
            FROM borrowings 
            WHERE student_id = ? AND status = 'borrowed'
        ");
        $stmt->execute([$studentId]);
        $result = $stmt->fetch();
        
        return $result['borrowed_count'] < $maxBooks;
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Export to CSV
 */
function exportToCSV($filename, $data, $headers = []) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    $output = fopen('php://output', 'w');
    
    if (!empty($headers)) {
        fputcsv($output, $headers);
    }
    
    foreach ($data as $row) {
        fputcsv($output, $row);
    }
    
    fclose($output);
    exit();
}
?>
