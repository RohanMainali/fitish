
<?php
// /includes/user.class.php
require_once 'db.php';
class User {
    private $conn;
    public function __construct() {
        $db = new Database();
        $this->conn = $db->connect();
    }
    
    // Create user with specific role
    public function create($username, $email, $password, $role = 'user') {
        $sql = "INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        return $stmt->execute([$username, $email, $hashed, $role]);
    }
    
    // Get user by username
    public function getByUsername($username) {
        $sql = "SELECT * FROM users WHERE username = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$username]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Get user by email
    public function getByEmail($email) {
        $sql = "SELECT * FROM users WHERE email = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Get user by ID
    public function getById($id) {
        $sql = "SELECT * FROM users WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Update user - supports both array data and individual parameters
    public function update($id, $usernameOrData, $email = null, $role = null) {
        if (is_array($usernameOrData)) {
            // Original array-based method
            $data = $usernameOrData;
            $fields = [];
            $values = [];
            foreach ($data as $key => $value) {
                $fields[] = "$key = ?";
                $values[] = $value;
            }
            $values[] = $id;
            $sql = "UPDATE users SET " . implode(", ", $fields) . " WHERE id = ?";
        } else {
            // Individual parameters method
            $username = $usernameOrData;
            $sql = "UPDATE users SET username = ?, email = ?, role = ? WHERE id = ?";
            $values = [$username, $email, $role, $id];
        }
        
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute($values);
    }
    
    // Delete user
    public function delete($id) {
        $sql = "DELETE FROM users WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$id]);
    }
    
    // Verify password
    public function verifyPassword($username, $password) {
        $user = $this->getByUsername($username);
        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        return false;
    }
    
    // Get all users (for admin management)
    public function getAll() {
        $sql = "SELECT id, username, email, role, created_at, last_login, is_active FROM users ORDER BY created_at DESC";
        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Get users by role
    public function getByRole($role) {
        $sql = "SELECT id, username, email, role, created_at, last_login, is_active FROM users WHERE role = ? ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$role]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Get users with filters
    public function getFiltered($role = '', $active = '', $search = '') {
        $sql = "SELECT id, username, email, role, created_at, last_login, is_active FROM users WHERE 1=1";
        $params = [];
        
        if ($role) {
            $sql .= " AND role = ?";
            $params[] = $role;
        }
        
        if ($active !== '') {
            $sql .= " AND is_active = ?";
            $params[] = $active;
        }
        
        if ($search) {
            $sql .= " AND (username LIKE ? OR email LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        
        $sql .= " ORDER BY created_at DESC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Promote user to admin (superadmin only)
    public function promoteToAdmin($userId) {
        return $this->update($userId, ['role' => 'admin']);
    }
    
    // Demote admin to user (superadmin only)
    public function demoteToUser($userId) {
        return $this->update($userId, ['role' => 'user']);
    }
    
    // Check if username exists (excluding specific user ID)
    public function usernameExists($username, $excludeId = null) {
        $sql = "SELECT COUNT(*) as count FROM users WHERE username = ?";
        $params = [$username];
        
        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result['count'] > 0;
    }
    
    // Check if email exists (excluding specific user ID)
    public function emailExists($email, $excludeId = null) {
        $sql = "SELECT COUNT(*) as count FROM users WHERE email = ?";
        $params = [$email];
        
        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result['count'] > 0;
    }
    
    // Get user statistics
    public function getStats() {
        $sql = "SELECT 
                    COUNT(*) as total_users,
                    SUM(CASE WHEN role = 'user' THEN 1 ELSE 0 END) as regular_users,
                    SUM(CASE WHEN role = 'admin' THEN 1 ELSE 0 END) as admins,
                    SUM(CASE WHEN role = 'superadmin' THEN 1 ELSE 0 END) as superadmins,
                    SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active_users,
                    SUM(CASE WHEN is_active = 0 THEN 1 ELSE 0 END) as inactive_users
                FROM users";
        
        $stmt = $this->conn->query($sql);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Get total user count
    public function getTotalCount() {
        $sql = "SELECT COUNT(*) as count FROM users";
        $stmt = $this->conn->query($sql);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'];
    }
    
    // Get active users count
    public function getActiveUsersCount() {
        $sql = "SELECT COUNT(*) as count FROM users WHERE last_login >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
        $stmt = $this->conn->query($sql);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'];
    }
    
    // Get all users with filters
    public function getAllWithFilters($search = '', $roleFilter = '') {
        $sql = "SELECT id, username, email, role, created_at, last_login FROM users WHERE 1=1";
        $params = [];
        
        if ($search) {
            $sql .= " AND (username LIKE ? OR email LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        
        if ($roleFilter) {
            $sql .= " AND role = ?";
            $params[] = $roleFilter;
        }
        
        $sql .= " ORDER BY created_at DESC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
