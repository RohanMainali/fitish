<?php
// login.php
require_once 'includes/auth.class.php';
require_once 'includes/user.class.php';

$message = '';

// Check if superadmin setup is needed
$userObj = new User();
$superadmins = $userObj->getByRole('superadmin');
$needsSetup = empty($superadmins);

// Handle login form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $auth = new Auth();
    if ($auth->login($username, $password)) {
        // Redirect based on role
        $role = $auth->currentRole();
        if ($role === 'superadmin') {
            header('Location: /fitishh/superadmin/dashboard.php');
        } elseif ($role === 'admin') {
            header('Location: /fitishh/admin/dashboard.php');
        } else {
            header('Location: /fitishh/dashboard.php');
        }
        exit;
    } else {
        $message = 'Invalid username or password.';
    }
}

// Handle success messages
if (isset($_GET['created']) && $_GET['created'] === '1') {
    $message = 'Superadmin account created successfully! You can now login.';
}
if (isset($_GET['error']) && $_GET['error'] === 'setup_disabled') {
    $message = 'Superadmin setup is disabled. Superadmin account already exists.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Fitish Pro</title>
    <link rel="stylesheet" href="assets/css/moderns.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-background"></div>
        
        <a href="index.php" class="back-button">
            <i class="fas fa-arrow-left"></i>
        </a>
        
        <div class="auth-card fade-in">
            <div class="auth-header">
                <div class="auth-logo">
                    <svg viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                    </svg>
                </div>
                <h1 class="auth-title">Welcome Back</h1>
                <p class="auth-subtitle">Sign in to continue your fitness journey</p>
            </div>
            
            <div class="auth-form">
                <?php if ($message): ?>
                    <div class="alert <?php echo (strpos($message, 'successfully') !== false) ? 'alert-success' : 'alert-error'; ?>">
                        <i class="fas <?php echo (strpos($message, 'successfully') !== false) ? 'fa-check-circle' : 'fa-exclamation-circle'; ?>"></i>
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>
                
                <form method="post" action="">
                    <div class="form-group">
                        <label for="username" class="form-label">
                            <i class="fas fa-user"></i>
                            Username
                        </label>
                        <input type="text" id="username" name="username" class="form-input" placeholder="Enter your username" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password" class="form-label">
                            <i class="fas fa-lock"></i>
                            Password
                        </label>
                        <div style="position: relative;">
                            <input type="password" id="password" name="password" class="form-input" placeholder="Enter your password" required>
                            <button type="button" onclick="togglePassword()" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; color: var(--text-muted); cursor: pointer;">
                                <i class="fas fa-eye" id="toggleIcon"></i>
                            </button>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-lg w-full">
                        <i class="fas fa-sign-in-alt"></i>
                        Sign In
                    </button>
                </form>
                
                <div class="auth-links">
                    <p>Don't have an account? <a href="register.php" class="auth-link">Create one here</a></p>
                    
                    <?php if ($needsSetup): ?>
                        <div style="margin-top: var(--spacing-lg); padding: var(--spacing-md); background: rgba(99, 102, 241, 0.1); border-left: 4px solid var(--primary); border-radius: var(--radius-md);">
                            <p style="margin: 0; color: var(--primary); font-weight: 500;">
                                <i class="fas fa-info-circle"></i>
                                System setup required. <a href="superadmin/setup.php" class="auth-link">Initialize Fitish Pro</a>
                            </p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }
        
        // Add some loading states
        document.querySelector('form').addEventListener('submit', function(e) {
            const button = this.querySelector('button[type="submit"]');
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Signing In...';
            button.disabled = true;
        });
    </script>
</body>
</html>
