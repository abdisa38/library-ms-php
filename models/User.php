<?php
/**
 * User Model
 */
class User {
    private $db;
    
    public function __construct() {
        $this->db = getDB();
    }
    
    /**
     * Authenticate user
     */
    public function login($username, $password, $remember = false) {
        try {
            $stmt = $this->db->prepare("
                SELECT u.*, r.role_name 
                FROM users u
                JOIN roles r ON u.role_id = r.id
                WHERE (u.username = ? OR u.email = ?) AND u.is_active = 1
            ");
            $stmt->execute([$username, $username]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                // Set session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role_name'];
                $_SESSION['role_id'] = $user['role_id'];
                $_SESSION['profile_picture'] = $user['profile_picture'];
                
                // Handle remember me
                if ($remember) {
                    $token = generateToken();
                    $this->setRememberToken($user['id'], $token);
                    setcookie('remember_token', $token, time() + (86400 * 30), '/'); // 30 days
                    setcookie('user_id', $user['id'], time() + (86400 * 30), '/');
                }
                
                // Update last login
                $this->updateLastLogin($user['id']);
                
                // Log activity
                logActivity($user['id'], 'login', 'User logged in');
                
                return ['success' => true, 'user' => $user];
            }
            
            return ['success' => false, 'message' => 'Invalid credentials'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Register new user
     */
    public function register($data) {
        try {
            // Check if username exists
            if ($this->usernameExists($data['username'])) {
                return ['success' => false, 'message' => 'Username already exists'];
            }
            
            // Check if email exists
            if ($this->emailExists($data['email'])) {
                return ['success' => false, 'message' => 'Email already exists'];
            }
            
            $stmt = $this->db->prepare("
                INSERT INTO users (role_id, username, email, password, full_name, phone, address)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            
            $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
            $roleId = $data['role_id'] ?? 3; // Default to student
            
            $stmt->execute([
                $roleId,
                $data['username'],
                $data['email'],
                $hashedPassword,
                $data['full_name'],
                $data['phone'] ?? null,
                $data['address'] ?? null
            ]);
            
            $userId = $this->db->lastInsertId();
            logActivity($userId, 'register', 'New user registered');
            
            return ['success' => true, 'user_id' => $userId];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Logout user
     */
    public function logout() {
        if (isset($_SESSION['user_id'])) {
            logActivity($_SESSION['user_id'], 'logout', 'User logged out');
        }
        
        // Clear remember me cookies
        if (isset($_COOKIE['remember_token'])) {
            setcookie('remember_token', '', time() - 3600, '/');
            setcookie('user_id', '', time() - 3600, '/');
        }
        
        session_destroy();
    }
    
    /**
     * Get user by ID
     */
    public function getUserById($id) {
        $stmt = $this->db->prepare("
            SELECT u.*, r.role_name 
            FROM users u
            JOIN roles r ON u.role_id = r.id
            WHERE u.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    /**
     * Get all users
     */
    public function getAllUsers($role = null, $limit = null, $offset = 0) {
        $sql = "SELECT u.*, r.role_name 
                FROM users u
                JOIN roles r ON u.role_id = r.id";
        
        $params = [];
        
        if ($role) {
            $sql .= " WHERE r.role_name = ?";
            $params[] = $role;
        }
        
        $sql .= " ORDER BY u.created_at DESC";
        
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
     * Count users
     */
    public function countUsers($role = null) {
        $sql = "SELECT COUNT(*) as total FROM users u JOIN roles r ON u.role_id = r.id";
        $params = [];
        
        if ($role) {
            $sql .= " WHERE r.role_name = ?";
            $params[] = $role;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return $result['total'];
    }
    
    /**
     * Update user
     */
    public function update($id, $data) {
        try {
            $fields = [];
            $params = [];
            
            foreach ($data as $key => $value) {
                if ($key !== 'id' && $key !== 'password') {
                    $fields[] = "$key = ?";
                    $params[] = $value;
                }
            }
            
            $params[] = $id;
            
            $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            
            logActivity($id, 'update_profile', 'User profile updated');
            
            return ['success' => true];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Change password
     */
    public function changePassword($id, $oldPassword, $newPassword) {
        try {
            $user = $this->getUserById($id);
            
            if (!password_verify($oldPassword, $user['password'])) {
                return ['success' => false, 'message' => 'Current password is incorrect'];
            }
            
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $this->db->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$hashedPassword, $id]);
            
            logActivity($id, 'change_password', 'Password changed');
            
            return ['success' => true];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Delete user
     */
    public function delete($id) {
        try {
            $stmt = $this->db->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$id]);
            
            logActivity($_SESSION['user_id'] ?? null, 'delete_user', "User ID: $id deleted");
            
            return ['success' => true];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Check if username exists
     */
    private function usernameExists($username) {
        $stmt = $this->db->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        return $stmt->fetch() !== false;
    }
    
    /**
     * Check if email exists
     */
    private function emailExists($email) {
        $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch() !== false;
    }
    
    /**
     * Set remember token
     */
    private function setRememberToken($userId, $token) {
        $hashedToken = hash('sha256', $token);
        $stmt = $this->db->prepare("UPDATE users SET remember_token = ? WHERE id = ?");
        $stmt->execute([$hashedToken, $userId]);
    }
    
    /**
     * Update last login
     */
    private function updateLastLogin($userId) {
        $stmt = $this->db->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
        $stmt->execute([$userId]);
    }
    
    /**
     * Request password reset
     */
    public function requestPasswordReset($email) {
        try {
            $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if (!$user) {
                return ['success' => false, 'message' => 'Email not found'];
            }
            
            $token = generateToken();
            $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            $stmt = $this->db->prepare("
                UPDATE users 
                SET reset_token = ?, reset_token_expire = ? 
                WHERE id = ?
            ");
            $stmt->execute([$token, $expiry, $user['id']]);
            
            return ['success' => true, 'token' => $token];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Reset password
     */
    public function resetPassword($token, $newPassword) {
        try {
            $stmt = $this->db->prepare("
                SELECT id FROM users 
                WHERE reset_token = ? AND reset_token_expire > NOW()
            ");
            $stmt->execute([$token]);
            $user = $stmt->fetch();
            
            if (!$user) {
                return ['success' => false, 'message' => 'Invalid or expired token'];
            }
            
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $this->db->prepare("
                UPDATE users 
                SET password = ?, reset_token = NULL, reset_token_expire = NULL 
                WHERE id = ?
            ");
            $stmt->execute([$hashedPassword, $user['id']]);
            
            logActivity($user['id'], 'reset_password', 'Password reset via email');
            
            return ['success' => true];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
?>
