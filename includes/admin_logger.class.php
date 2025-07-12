<?php
// includes/admin_logger.class.php - Admin Activity Logger
class AdminLogger {
    private $conn;
    
    public function __construct() {
        require_once 'db.php';
        $db = new Database();
        $this->conn = $db->connect();
    }
    
    /**
     * Log admin activity
     */
    public function logActivity($adminId, $action, $targetUserId = null, $details = null) {
        try {
            $ipAddress = $this->getClientIP();
            
            $sql = "INSERT INTO admin_logs (admin_id, action, target_user_id, details, ip_address) 
                    VALUES (?, ?, ?, ?, ?)";
            $stmt = $this->conn->prepare($sql);
            
            return $stmt->execute([
                $adminId,
                $action,
                $targetUserId,
                $details,
                $ipAddress
            ]);
        } catch (Exception $e) {
            error_log("Failed to log admin activity: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get admin activity logs with pagination
     */
    public function getLogs($limit = 50, $offset = 0, $filters = []) {
        try {
            $sql = "SELECT al.*, 
                           a.username as admin_username,
                           u.username as target_username
                    FROM admin_logs al
                    LEFT JOIN users a ON al.admin_id = a.id
                    LEFT JOIN users u ON al.target_user_id = u.id
                    WHERE 1=1";
            
            $params = [];
            
            // Apply filters
            if (!empty($filters['admin_id'])) {
                $sql .= " AND al.admin_id = ?";
                $params[] = $filters['admin_id'];
            }
            
            if (!empty($filters['action'])) {
                $sql .= " AND al.action LIKE ?";
                $params[] = "%{$filters['action']}%";
            }
            
            if (!empty($filters['date_from'])) {
                $sql .= " AND DATE(al.created_at) >= ?";
                $params[] = $filters['date_from'];
            }
            
            if (!empty($filters['date_to'])) {
                $sql .= " AND DATE(al.created_at) <= ?";
                $params[] = $filters['date_to'];
            }
            
            $sql .= " ORDER BY al.created_at DESC LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Failed to get admin logs: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get logs count for pagination
     */
    public function getLogsCount($filters = []) {
        try {
            $sql = "SELECT COUNT(*) as total FROM admin_logs al WHERE 1=1";
            $params = [];
            
            // Apply same filters as getLogs
            if (!empty($filters['admin_id'])) {
                $sql .= " AND al.admin_id = ?";
                $params[] = $filters['admin_id'];
            }
            
            if (!empty($filters['action'])) {
                $sql .= " AND al.action LIKE ?";
                $params[] = "%{$filters['action']}%";
            }
            
            if (!empty($filters['date_from'])) {
                $sql .= " AND DATE(al.created_at) >= ?";
                $params[] = $filters['date_from'];
            }
            
            if (!empty($filters['date_to'])) {
                $sql .= " AND DATE(al.created_at) <= ?";
                $params[] = $filters['date_to'];
            }
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $result['total'];
        } catch (Exception $e) {
            error_log("Failed to get logs count: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Get client IP address
     */
    private function getClientIP() {
        $ipKeys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 
                   'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'];
        
        foreach ($ipKeys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP, 
                        FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }
}
?>
