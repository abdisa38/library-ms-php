<?php
/**
 * Student Model
 */
class Student {
    private $db;
    
    public function __construct() {
        $this->db = getDB();
    }
    
    /**
     * Create new student
     */
    public function create($data) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO students (student_id, full_name, email, phone, department, year, address, profile_picture)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $data['student_id'],
                $data['full_name'],
                $data['email'],
                $data['phone'] ?? null,
                $data['department'] ?? null,
                $data['year'] ?? null,
                $data['address'] ?? null,
                $data['profile_picture'] ?? 'default.jpg'
            ]);
            
            $id = $this->db->lastInsertId();
            logActivity($_SESSION['user_id'] ?? null, 'create_student', "Student ID: $id created");
            
            return ['success' => true, 'id' => $id];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Get student by ID
     */
    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM students WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    /**
     * Get student by student ID
     */
    public function getByStudentId($studentId) {
        $stmt = $this->db->prepare("SELECT * FROM students WHERE student_id = ?");
        $stmt->execute([$studentId]);
        return $stmt->fetch();
    }
    
    /**
     * Get all students
     */
    public function getAll($limit = null, $offset = 0, $search = null) {
        $sql = "SELECT * FROM students WHERE 1=1";
        $params = [];
        
        if ($search) {
            $sql .= " AND (full_name LIKE ? OR student_id LIKE ? OR email LIKE ? OR department LIKE ?)";
            $searchTerm = '%' . $search . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        $sql .= " ORDER BY created_at DESC";
        
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
     * Count students
     */
    public function count($search = null) {
        $sql = "SELECT COUNT(*) as total FROM students WHERE 1=1";
        $params = [];
        
        if ($search) {
            $sql .= " AND (full_name LIKE ? OR student_id LIKE ? OR email LIKE ?)";
            $searchTerm = '%' . $search . '%';
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
     * Update student
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
            
            $sql = "UPDATE students SET " . implode(', ', $fields) . " WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            
            logActivity($_SESSION['user_id'] ?? null, 'update_student', "Student ID: $id updated");
            
            return ['success' => true];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Delete student
     */
    public function delete($id) {
        try {
            // Check if student has active borrowings
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as count 
                FROM borrowings 
                WHERE student_id = ? AND status = 'borrowed'
            ");
            $stmt->execute([$id]);
            $result = $stmt->fetch();
            
            if ($result['count'] > 0) {
                return ['success' => false, 'message' => 'Cannot delete student with active borrowings'];
            }
            
            $stmt = $this->db->prepare("DELETE FROM students WHERE id = ?");
            $stmt->execute([$id]);
            
            logActivity($_SESSION['user_id'] ?? null, 'delete_student', "Student ID: $id deleted");
            
            return ['success' => true];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Search students
     */
    public function search($query) {
        $searchTerm = '%' . $query . '%';
        $stmt = $this->db->prepare("
            SELECT * FROM students 
            WHERE full_name LIKE ? OR student_id LIKE ? OR email LIKE ?
            LIMIT 10
        ");
        $stmt->execute([$searchTerm, $searchTerm, $searchTerm]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get student borrowing history
     */
    public function getBorrowingHistory($id, $limit = null) {
        $sql = "SELECT b.*, bk.title, bk.isbn, a.name as author_name
                FROM borrowings b
                JOIN books bk ON b.book_id = bk.id
                LEFT JOIN authors a ON bk.author_id = a.id
                WHERE b.student_id = ?
                ORDER BY b.created_at DESC";
        
        if ($limit) {
            $sql .= " LIMIT ?";
        }
        
        $stmt = $this->db->prepare($sql);
        
        if ($limit) {
            $stmt->execute([$id, $limit]);
        } else {
            $stmt->execute([$id]);
        }
        
        return $stmt->fetchAll();
    }
    
    /**
     * Get active borrowings
     */
    public function getActiveBorrowings($id) {
        $stmt = $this->db->prepare("
            SELECT b.*, bk.title, bk.isbn
            FROM borrowings b
            JOIN books bk ON b.book_id = bk.id
            WHERE b.student_id = ? AND b.status = 'borrowed'
            ORDER BY b.due_date ASC
        ");
        $stmt->execute([$id]);
        return $stmt->fetchAll();
    }
}
?>
