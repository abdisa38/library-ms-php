<?php
/**
 * General Configuration
 * Library Management System
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Base URL
define('BASE_URL', 'http://localhost/library-ms-php/');
define('SITE_NAME', 'Library Management System');

// File upload settings
define('UPLOAD_PATH', __DIR__ . '/../uploads/');
define('BOOK_COVER_PATH', UPLOAD_PATH . 'books/');
define('PROFILE_PATH', UPLOAD_PATH . 'profiles/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB

// Allowed file extensions
define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'gif']);

// Pagination
define('RECORDS_PER_PAGE', 10);

// Date format
define('DATE_FORMAT', 'Y-m-d');
define('DATETIME_FORMAT', 'Y-m-d H:i:s');
define('DISPLAY_DATE_FORMAT', 'd M Y');

// Timezone
date_default_timezone_set('UTC');

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database
require_once __DIR__ . '/database.php';

// Auto-load models and controllers
spl_autoload_register(function($class) {
    $paths = [
        __DIR__ . '/../models/' . $class . '.php',
        __DIR__ . '/../controllers/' . $class . '.php',
    ];
    
    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
});
?>
