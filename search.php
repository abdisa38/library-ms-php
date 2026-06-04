<?php
/**
 * AJAX Search Endpoint
 */

require_once 'config/config.php';
require_once 'includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$query = isset($_GET['q']) ? sanitize($_GET['q']) : '';
$type = isset($_GET['type']) ? sanitize($_GET['type']) : 'books';

if (strlen($query) < 2) {
    echo json_encode([]);
    exit;
}

try {
    switch ($type) {
        case 'books':
            $book = new Book();
            $results = $book->search($query);
            echo json_encode($results);
            break;
            
        case 'students':
            $student = new Student();
            $results = $student->search($query);
            echo json_encode($results);
            break;
            
        case 'authors':
            $author = new Author();
            $results = $author->search($query);
            echo json_encode($results);
            break;
            
        default:
            echo json_encode([]);
    }
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
