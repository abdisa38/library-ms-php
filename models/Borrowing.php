<?php
/**
 * Borrowing Model
 */
class Borrowing {
    private $db;
    
    public function __construct() {
        $this->db = getDB();
    }
    
    /**
     * Create new borrowing
     */
    public function create($data) {
        try {
            // Check if book is available
            if (!isBookAvailable($data['book_id'])) {
                return ['success' => false, 'message' => 'Book is not available'];
            }
            
            // Check if student can borrow more books
            if (!canBorrowMore($data['student_id'])) {
                $maxBooks = getSetting('max_books_per_student', 3);
                return ['success' => false, 'message' => "Student has reached maximum borrowing limit of $maxBooks books"];
            }
            
            // Check if student already has this book
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as count 
                FROM borrowings 
                WHERE student_id = ? AND book_id = ? AND status = 'borrowed'
            ");
            $stmt->execute([$data['student_id'], $data['book_id']]);
            $result = $stmt->fetch();
            
            if ($result['count'] > 0) {
                return ['success' => false, 'message' => 'Student already has this book'];
            }
            
            // Calculate due date
            $borrowDays = (int) getSetting('borrow_days_limit', 14);
            $dueDate = date('Y-m-d', strtotime("+$borrowDays days"));
            
            // Create borrowing
            $stmt = $this->db->prepare("
                INSERT INTO borrowings (student_id, book_id, borrow_date, due_date, status, created_by)
                VALUES (?, ?, ?, ?, 'borrowed', ?)
            ");
            
            $stmt->execute([
                $data['student_id'],
                $data['book_id'],
                date('Y-m-d'),
                $dueDate,
                $_SESSION['user_id'] ?? null
            ]);
            
            $borrowingId = $this->db->lastInsertId();
            
            // Update book available copies
            $book = new Book();
            $book->updateCopies($data['book_id'], false);
            
            logActivity($_SESSION['user_id'] ?? null, 'create_borrowing', "Borrowing ID: $borrowingId created");
            
            return ['success' => true, 'borrowing_id' => $borrowingId];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Get borrowing by ID
     */
    public function getById($id) {
        $stmt = $this->db->prepare("
            SELECT b.*, 
                   s.full_name as student_name, s.student_id as student_code,
                   bk.title as book_title, bk.isbn,
                   a.name as author_name,
                   u.full_name as created_by_name
            FROM borrowings b
            JOIN students s ON b.student_id = s.id
            JOIN books bk ON b.book_id = bk.id
            LEFT JOIN authors a ON bk.author_id = a.id
            LEFT JOIN users u ON b.created_by = u.id
            WHERE b.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    /**
     * Get all borrowings
     */
    public function getAll($limit = null, $offset = 0, $filters = []) {
        $sql = "SELECT b.*, 
                       s.full_name as student_name, s.student_id as student_code,
                       bk.title as book_title, bk.isbn
                FROM borrowings b
                JOIN students s ON b.student_id = s.id
                JOIN books bk ON b.book_id = bk.id
                WHERE 1=1";
        
        $params = [];
        
        if (!empty($filters['status'])) {
            $sql .= " AND b.status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['student_id'])) {
            $sql .= " AND b.student_id = ?";
            $params[] = $filters['student_id'];
        }
        
        if (!empty($filters['search'])) {
            $sql .= " AND (s.full_name LIKE ? OR s.student_id LIKE ? OR bk.title LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        $sql .= " ORDER BY b.created_at DESC";
        
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
     * Count borrowings
     */
    public function count($filters = []) {
        $sql = "SELECT COUNT(*) as total 
                FROM borrowings b
                JOIN students s ON b.student_id = s.id
                JOIN books bk ON b.book_id = bk.id
                WHERE 1=1";
        
        $params = [];
        
        if (!empty($filters['status'])) {
            $sql .= " AND b.status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['search'])) {
            $sql .= " AND (s.full_name LIKE ? OR s.student_id LIKE ? OR bk.title LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return $result['total'];
    }
    
    /**
     * Return book
     */
    public function returnBook($id) {
        try {
            $borrowing = $this->getById($id);
            
            if (!$borrowing) {
                return ['success' => false, 'message' => 'Borrowing not found'];
            }
            
            if ($borrowing['status'] === 'returned') {
                return ['success' => false, 'message' => 'Book already returned'];
            }
            
            $returnDate = date('Y-m-d');
            $fineAmount = calculateFine($borrowing['due_date'], $returnDate);
            $status = $fineAmount > 0 ? 'overdue' : 'returned';
            
            // Update borrowing
            $stmt = $this->db->prepare("
                UPDATE borrowings 
                SET return_date = ?, fine_amount = ?, status = ?
                WHERE id = ?
            ");
            $stmt->execute([$returnDate, $fineAmount, $status, $id]);
            
            // Update book available copies
            $book = new Book();
            $book->updateCopies($borrowing['book_id'], true);
            
            // Create fine record if applicable
            if ($fineAmount > 0) {
                $stmt = $this->db->prepare("
                    INSERT INTO fines (borrowing_id, fine_amount, status)
                    VALUES (?, ?, 'unpaid')
                ");
                $stmt->execute([$id, $fineAmount]);
            }
            
            logActivity($_SESSION['user_id'] ?? null, 'return_book', "Borrowing ID: $id returned");
            
            return ['success' => true, 'fine_amount' => $fineAmount];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Get overdue borrowings
     */
    public function getOverdue() {
        $stmt = $this->db->prepare("
            SELECT b.*, 
                   s.full_name as student_name, s.student_id as student_code, s.email,
                   bk.title as book_title, bk.isbn,
                   DATEDIFF(CURDATE(), b.due_date) as days_overdue
            FROM borrowings b
            JOIN students s ON b.student_id = s.id
            JOIN books bk ON b.book_id = bk.id
            WHERE b.status = 'borrowed' AND b.due_date < CURDATE()
            ORDER BY days_overdue DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Update overdue status
     */
    public function updateOverdueStatus() {
        try {
            $stmt = $this->db->prepare("
                UPDATE borrowings 
                SET status = 'overdue'
                WHERE status = 'borrowed' AND due_date < CURDATE()
            ");
            $stmt->execute();
            return ['success' => true];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Get borrowing statistics
     */
    public function getStats() {
        $stats = [];
        
        // Total borrowed
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM borrowings WHERE status = 'borrowed'");
        $stmt->execute();
        $result = $stmt->fetch();
        $stats['total_borrowed'] = $result['total'];
        
        // Total returned
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM borrowings WHERE status = 'returned'");
        $stmt->execute();
        $result = $stmt->fetch();
        $stats['total_returned'] = $result['total'];
        
        // Total overdue
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM borrowings WHERE status = 'overdue' OR (status = 'borrowed' AND due_date < CURDATE())");
        $stmt->execute();
        $result = $stmt->fetch();
        $stats['total_overdue'] = $result['total'];
        
        return $stats;
    }
    
    /**
     * Get monthly statistics
     */
    public function getMonthlyStats($year = null) {
        $year = $year ?? date('Y');
        
        $stmt = $this->db->prepare("
            SELECT 
                MONTH(borrow_date) as month,
                COUNT(*) as count
            FROM borrowings
            WHERE YEAR(borrow_date) = ?
            GROUP BY MONTH(borrow_date)
            ORDER BY MONTH(borrow_date)
        ");
        $stmt->execute([$year]);
        return $stmt->fetchAll();
    }
}
?>
