<?php
// includes/rbac.class.php - Enhanced Role-Based Access Control
class RBAC {
    
    /**
     * Require specific roles to access a page
     * @param array $allowedRoles Array of allowed roles
     * @param string $redirectUrl Where to redirect if unauthorized
     */
    public static function requireRole($allowedRoles = [], $redirectUrl = '/fitishh/login.php') {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $currentRole = $_SESSION['role'] ?? null;
        $userId = $_SESSION['user_id'] ?? null;
        
        // Check if user is logged in
        if (!$userId) {
            header("Location: $redirectUrl");
            exit;
        }
        
        // Verify role from database (security check)
        if (!self::verifyRoleFromDB($userId, $currentRole)) {
            // Role mismatch, destroy session and redirect
            session_destroy();
            header("Location: $redirectUrl?error=invalid_session");
            exit;
        }
        
        // Check if current role is in allowed roles
        if (!in_array($currentRole, $allowedRoles)) {
            header("Location: /fitishh/dashboard.php?error=unauthorized");
            exit;
        }
        
        // Update last activity
        self::updateLastActivity($userId);
    }
    
    /**
     * Verify user role against database
     */
    private static function verifyRoleFromDB($userId, $sessionRole) {
        try {
            require_once __DIR__ . '/db.php';
            $db = new Database();
            $conn = $db->connect();
            
            $stmt = $conn->prepare("SELECT role, is_active FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user || !$user['is_active']) {
                return false;
            }
            
            return $user['role'] === $sessionRole;
        } catch (Exception $e) {
            error_log("RBAC verification error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update user's last activity
     */
    private static function updateLastActivity($userId) {
        try {
            require_once __DIR__ . '/db.php';
            $db = new Database();
            $conn = $db->connect();
            
            $stmt = $conn->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
            $stmt->execute([$userId]);
        } catch (Exception $e) {
            error_log("Failed to update last activity: " . $e->getMessage());
        }
    }
    
    /**
     * Check if current user is superadmin
     */
    public static function isSuperAdmin() {
        return ($_SESSION['role'] ?? null) === 'superadmin';
    }
    
    /**
     * Check if current user is admin or higher
     */
    public static function isAdmin() {
        $role = $_SESSION['role'] ?? null;
        return in_array($role, ['admin', 'superadmin']);
    }
    
    /**
     * Check if current user is regular user
     */
    public static function isUser() {
        return ($_SESSION['role'] ?? null) === 'user';
    }
    
    /**
     * Get current user's role
     */
    public static function getCurrentRole() {
        return $_SESSION['role'] ?? null;
    }
    
    /**
     * Check if user can perform action on target user
     */
    public static function canManageUser($targetUserId) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $currentRole = $_SESSION['role'] ?? null;
        $currentUserId = $_SESSION['user_id'] ?? null;
        
        // Users can only manage themselves
        if ($currentRole === 'user') {
            return $currentUserId == $targetUserId;
        }
        
        // Admins can manage users but not other admins/superadmins
        if ($currentRole === 'admin') {
            $targetRole = self::getUserRole($targetUserId);
            return $targetRole === 'user';
        }
        
        // Superadmins can manage everyone except other superadmins
        if ($currentRole === 'superadmin') {
            $targetRole = self::getUserRole($targetUserId);
            return $targetRole !== 'superadmin' || $currentUserId == $targetUserId;
        }
        
        return false;
    }
    
    /**
     * Get user role by ID
     */
    private static function getUserRole($userId) {
        try {
            require_once __DIR__ . '/db.php';
            $db = new Database();
            $conn = $db->connect();
            
            $stmt = $conn->prepare("SELECT role FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $user ? $user['role'] : null;
        } catch (Exception $e) {
            error_log("Failed to get user role: " . $e->getMessage());
            return null;
        }
    }
}
?>
