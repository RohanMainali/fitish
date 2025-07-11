<?php
// /includes/auth.class.php
require_once 'user.class.php';
class Auth {
    private $user;
    public function __construct() {
        $this->user = new User();
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    // Login with role support
    public function login($username, $password) {
        $user = $this->user->verifyPassword($username, $password);
        if ($user && $user['is_active']) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            
            // Update last login
            $this->user->update($user['id'], ['last_login' => date('Y-m-d H:i:s')]);
            
            return true;
        }
        return false;
    }
    
    // Logout
    public function logout() {
        session_unset();
        session_destroy();
    }
    
    // Check if logged in
    public function isLoggedIn() {
        return isset($_SESSION['user_id']) && isset($_SESSION['role']);
    }
    
    // Get current user
    public function currentUser() {
        if ($this->isLoggedIn()) {
            return $this->user->getById($_SESSION['user_id']);
        }
        return null;
    }
    
    // Get current role
    public function currentRole() {
        return $_SESSION['role'] ?? null;
    }
    
    // Require login for protected pages
    public function requireLogin($redirectUrl = '/fitishh/login.php') {
        if (!$this->isLoggedIn()) {
            header("Location: $redirectUrl");
            exit;
        }
    }
    
    // Check if current user has specific role
    public function hasRole($role) {
        return $this->currentRole() === $role;
    }
    
    // Check if current user has any of the specified roles
    public function hasAnyRole($roles) {
        return in_array($this->currentRole(), $roles);
    }
}
?>
