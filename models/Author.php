<?php
/**
 * Author Model
 */
class Author {
    private $db;
    
    public function __construct() {
        $this->db = getDB();
    }
    
    public function create($data) {
        try {
            $stmt = $this->db->prepare("INSERT INTO authors (name, biography) VALUES (?, ?)");
            $stmt->execute([$data['name'], $data['biography'] ?? null]);
            return ['success' => true, 'id' => $this->db->lastInsertId()];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM authors WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public function getAll() {
        $stmt = $this->db->query("SELECT * FROM authors ORDER BY name");
        return $stmt->fetchAll();
    }
    
    public function update($id, $data) {
        try {
            $stmt = $this->db->prepare("UPDATE authors SET name = ?, biography = ? WHERE id = ?");
            $stmt->execute([$data['name'], $data['biography'] ?? null, $id]);
            return ['success' => true];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    public function delete($id) {
        try {
            $stmt = $this->db->prepare("DELETE FROM authors WHERE id = ?");
            $stmt->execute([$id]);
            return ['success' => true];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    public function search($query) {
        $searchTerm = '%' . $query . '%';
        $stmt = $this->db->prepare("SELECT * FROM authors WHERE name LIKE ? LIMIT 10");
        $stmt->execute([$searchTerm]);
        return $stmt->fetchAll();
    }
    
    public function count() {
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM authors");
        $result = $stmt->fetch();
        return $result['total'];
    }
}
?>
