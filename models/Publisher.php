<?php
/**
 * Publisher Model
 */
class Publisher {
    private $db;
    
    public function __construct() {
        $this->db = getDB();
    }
    
    public function create($data) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO publishers (name, address, email, phone) 
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([
                $data['name'],
                $data['address'] ?? null,
                $data['email'] ?? null,
                $data['phone'] ?? null
            ]);
            return ['success' => true, 'id' => $this->db->lastInsertId()];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM publishers WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public function getAll() {
        $stmt = $this->db->query("SELECT * FROM publishers ORDER BY name");
        return $stmt->fetchAll();
    }
    
    public function update($id, $data) {
        try {
            $stmt = $this->db->prepare("
                UPDATE publishers 
                SET name = ?, address = ?, email = ?, phone = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $data['name'],
                $data['address'] ?? null,
                $data['email'] ?? null,
                $data['phone'] ?? null,
                $id
            ]);
            return ['success' => true];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    public function delete($id) {
        try {
            $stmt = $this->db->prepare("DELETE FROM publishers WHERE id = ?");
            $stmt->execute([$id]);
            return ['success' => true];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    public function count() {
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM publishers");
        $result = $stmt->fetch();
        return $result['total'];
    }
}
?>
