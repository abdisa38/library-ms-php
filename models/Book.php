<?php
/**
 * Book Model
 */
class Book {
    private $db;
    
    public function __construct() {
        $this->db = getDB();
    }
    
    /**
     * Create new book
     */
    public function create($data) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO books (isbn, title, author_id, category_id, publisher_id, 
                                 quantity, available_copies, book_cover, shelf_number, description)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $availableCopies = $data['quantity'];
            
            $stmt->execute([
                $data['isbn'],
                $data['title'],
                $data['author_id'],
                $data['category_id'],
                $data['publisher_id'],
                $data['quantity'],
                $availableCopies,
                $data['book_cover'] ?? 'default-book.jpg',
                $data['shelf_number'] ?? null,
                $data['description'] ?? null
            ]);
            
            $bookId = $this->db->lastInsertId();
            logActivity($_SESSION['user_id'] ?? null, 'create_book', "Book ID: $bookId created");
            
            return ['success' => true, 'book_id' => $bookId];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Get book by ID
     */
    public function getById($id) {
        $stmt = $this->db->prepare("
            SELECT b.*, 
                   a.name as author_name,
                   c.name as category_name,
                   p.name as publisher_name
            FROM books b
            LEFT JOIN authors a ON b.author_id = a.id
            LEFT JOIN categories c ON b.category_id = c.id
            LEFT JOIN publishers p ON b.publisher_id = p.id
            WHERE b.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    /**
     * Get all books
     */
    public function getAll($limit = null, $offset = 0, $filters = []) {
        $sql = "SELECT b.*, 
                       a.name as author_name,
                       c.name as category_name,
                       p.name as publisher_name
                FROM books b
                LEFT JOIN authors a ON b.author_id = a.id
                LEFT JOIN categories c ON b.category_id = c.id
                LEFT JOIN publishers p ON b.publisher_id = p.id
                WHERE 1=1";
        
        $params = [];
        
        // Apply filters
        if (!empty($filters['search'])) {
            $sql .= " AND (b.title LIKE ? OR b.isbn LIKE ? OR a.name LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        if (!empty($filters['category_id'])) {
            $sql .= " AND b.category_id = ?";
            $params[] = $filters['category_id'];
        }
        
        if (!empty($filters['author_id'])) {
            $sql .= " AND b.author_id = ?";
            $params[] = $filters['author_id'];
        }
        
        $sql .= " ORDER BY b.date_added DESC";
        
        if ($limit) {
            $sql .= " LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Count books
     */
    public function count($filters = []) {
        $sql = "SELECT COUNT(*) as total 
                FROM books b
                LEFT JOIN authors a ON b.author_id = a.id
                WHERE 1=1";
        
        $params = [];
        
        if (!empty($filters['search'])) {
            $sql .= " AND (b.title LIKE ? OR b.isbn LIKE ? OR a.name LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        if (!empty($filters['category_id'])) {
            $sql .= " AND b.category_id = ?";
            $params[] = $filters['category_id'];
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return $result['total'];
    }
    
    /**
     * Update book
     */
    public function update($id, $data) {
        try {
            $fields = [];
            $params = [];
            
            foreach ($data as $key => $value) {
                if ($key !== 'id') {
                    $fields[] = "$key = ?";
                    $params[] = $value;
                }
            }
            
            $params[] = $id;
            
            $sql = "UPDATE books SET " . implode(', ', $fields) . " WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            
            logActivity($_SESSION['user_id'] ?? null, 'update_book', "Book ID: $id updated");
            
            return ['success' => true];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Delete book
     */
    public function delete($id) {
        try {
            // Check if book is currently borrowed
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as count 
                FROM borrowings 
                WHERE book_id = ? AND status = 'borrowed'
            ");
            $stmt->execute([$id]);
            $result = $stmt->fetch();
            
            if ($result['count'] > 0) {
                return ['success' => false, 'message' => 'Cannot delete book that is currently borrowed'];
            }
            
            $stmt = $this->db->prepare("DELETE FROM books WHERE id = ?");
            $stmt->execute([$id]);
            
            logActivity($_SESSION['user_id'] ?? null, 'delete_book', "Book ID: $id deleted");
            
            return ['success' => true];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Search books
     */
    public function search($query) {
        $searchTerm = '%' . $query . '%';
        $stmt = $this->db->prepare("
            SELECT b.*, 
                   a.name as author_name,
                   c.name as category_name
            FROM books b
            LEFT JOIN authors a ON b.author_id = a.id
            LEFT JOIN categories c ON b.category_id = c.id
            WHERE b.title LIKE ? OR b.isbn LIKE ? OR a.name LIKE ?
            LIMIT 10
        ");
        $stmt->execute([$searchTerm, $searchTerm, $searchTerm]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get low stock books
     */
    public function getLowStock($threshold = 2) {
        $stmt = $this->db->prepare("
            SELECT b.*, 
                   a.name as author_name,
                   c.name as category_name
            FROM books b
            LEFT JOIN authors a ON b.author_id = a.id
            LEFT JOIN categories c ON b.category_id = c.id
            WHERE b.available_copies <= ?
            ORDER BY b.available_copies ASC
        ");
        $stmt->execute([$threshold]);
        return $stmt->fetchAll();
    }
    
    /**
     * Update book copies
     */
    public function updateCopies($id, $increment = true) {
        try {
            $operator = $increment ? '+' : '-';
            $stmt = $this->db->prepare("
                UPDATE books 
                SET available_copies = available_copies $operator 1 
                WHERE id = ?
            ");
            $stmt->execute([$id]);
            return ['success' => true];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Get books by category
     */
    public function getByCategory($categoryId) {
        $stmt = $this->db->prepare("
            SELECT b.*, a.name as author_name
            FROM books b
            LEFT JOIN authors a ON b.author_id = a.id
            WHERE b.category_id = ?
            ORDER BY b.title
        ");
        $stmt->execute([$categoryId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get popular books (most borrowed)
     */
    public function getPopular($limit = 10) {
        $stmt = $this->db->prepare("
            SELECT b.*, 
                   a.name as author_name,
                   COUNT(br.id) as borrow_count
            FROM books b
            LEFT JOIN authors a ON b.author_id = a.id
            LEFT JOIN borrowings br ON b.id = br.book_id
            GROUP BY b.id
            ORDER BY borrow_count DESC
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }
}
?>
