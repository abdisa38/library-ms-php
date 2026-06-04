<?php
/**
 * Category Model
 */
class Category {
    private $db;
    
    public function __construct() {
        $this->db = getDB();
    }
    
    public function create($data) {
        try {
            $stmt = $this->db->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
            $stmt->execute([$data['name'], $data['description'] ?? null]);
            return ['success' => true, 'id' => $this->db->lastInsertId()];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM categories WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public function getAll() {
        $stmt = $this->db->query("SELECT * FROM categories ORDER BY name");
        return $stmt->fetchAll();
    }
    
    public function update($id, $data) {
        try {
            $stmt = $this->db->prepare("UPDATE categories SET name = ?, description = ? WHERE id = ?");
            $stmt->execute([$data['name'], $data['description'] ?? null, $id]);
            return ['success' => true];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    public function delete($id) {
        try {
            $stmt = $this->db->prepare("DELETE FROM categories WHERE id = ?");
            $stmt->execute([$id]);
            return ['success' => true];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    public function count() {
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM categories");
        $result = $stmt->fetch();
        return $result['total'];
    }
    
    public function getWithBookCount() {
        $stmt = $this->db->query("
            SELECT c.*, COUNT(b.id) as book_count
            FROM categories c
            LEFT JOIN books b ON c.id = b.category_id
            GROUP BY c.id
            ORDER BY c.name
        ");
        return $stmt->fetchAll();
    }
}
?>
