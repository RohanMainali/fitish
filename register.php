<?php
// register.php
require_once 'includes/user.class.php';
require_once 'includes/validator.class.php';
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm'] ?? '';
    if (!Validator::validateUsername($username)) {
        $message = 'Invalid username.';
    } elseif (!Validator::validateEmail($email)) {
        $message = 'Invalid email.';
    } elseif (!Validator::validatePassword($password)) {
        $message = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirm) {
        $message = 'Passwords do not match.';
    } else {
        $user = new User();
        if ($user->getByUsername($username)) {
            $message = 'Username already exists.';
        } elseif ($user->getByEmail($email)) {
            $message = 'Email already exists.';
        } else {
            $user->create($username, $email, $password);
            header('Location: login.php?registered=1');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account - Fitish Pro</title>
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
                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                    </svg>
                </div>
                <h1 class="auth-title">Join Fitish Pro</h1>
                <p class="auth-subtitle">Start your fitness transformation today</p>
            </div>
            
            <div class="auth-form">
                <?php if ($message): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>
                
                <form method="post" action="">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="username" class="form-label">
                                <i class="fas fa-user"></i>
                                Username
                            </label>
                            <input type="text" id="username" name="username" class="form-input" placeholder="Choose a unique username" required minlength="3">
                        </div>
                        
                        <div class="form-group">
                            <label for="email" class="form-label">
                                <i class="fas fa-envelope"></i>
                                Email Address
                            </label>
                            <input type="email" id="email" name="email" class="form-input" placeholder="Enter your email add" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="password" class="form-label">
                                <i class="fas fa-lock"></i>
                                Password
                            </label>
                            <div style="position: relative;">
                                <input type="password" id="password" name="password" class="form-input" placeholder="Create a strong password" required minlength="6">
                                <button type="button" onclick="togglePassword('password', 'toggleIcon1')" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; color: var(--text-muted); cursor: pointer;">
                                    <i class="fas fa-eye" id="toggleIcon1"></i>
                                </button>
                            </div>
                            <small style="color: var(--text-muted); font-size: 0.75rem; margin-top: 0.25rem; display: block;">
                                Must be 6+ characters
                            </small>
                        </div>
                        
                        <div class="form-group">
                            <label for="confirm" class="form-label">
                                <i class="fas fa-lock"></i>
                                Confirm Password
                            </label>
                            <div style="position: relative;">
                                <input type="password" id="confirm" name="confirm" class="form-input" placeholder="Confirm your pass" required>
                                <button type="button" onclick="togglePassword('confirm', 'toggleIcon2')" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; color: var(--text-muted); cursor: pointer;">
                                    <i class="fas fa-eye" id="toggleIcon2"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-lg w-full">
                        <i class="fas fa-user-plus"></i>
                        Create Account
                    </button>
                </form>
                
                <div class="auth-links">
                    <p>Already have an account? <a href="login.php" class="auth-link">Sign in here</a></p>
                    
                    <div style="margin-top: var(--spacing-lg); padding: var(--spacing-md); background: rgba(6, 182, 212, 0.1); border-left: 4px solid var(--secondary); border-radius: var(--radius-md);">
                        <p style="margin: 0; color: var(--secondary); font-size: 0.875rem;">
                            <i class="fas fa-info-circle"></i>
                            By creating an account, you agree to our terms of service and privacy policy.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        function togglePassword(inputId, iconId) {
            const passwordInput = document.getElementById(inputId);
            const toggleIcon = document.getElementById(iconId);
            
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
        
        // Password confirmation validation
        document.getElementById('confirm').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirm = this.value;
            const submitBtn = document.querySelector('button[type="submit"]');
            
            if (confirm && password !== confirm) {
                this.style.borderColor = 'var(--danger)';
                submitBtn.disabled = true;
                
                // Show error message
                let errorMsg = this.parentNode.querySelector('.password-error');
                if (!errorMsg) {
                    errorMsg = document.createElement('small');
                    errorMsg.className = 'password-error';
                    errorMsg.style.cssText = 'color: var(--danger); font-size: 0.75rem; margin-top: 0.25rem; display: block;';
                    this.parentNode.appendChild(errorMsg);
                }
                errorMsg.textContent = 'Passwords do not match';
            } else {
                this.style.borderColor = '';
                submitBtn.disabled = false;
                
                const errorMsg = this.parentNode.querySelector('.password-error');
                if (errorMsg) {
                    errorMsg.remove();
                }
            }
        });
        
        // Add loading state to form
        document.querySelector('form').addEventListener('submit', function(e) {
            const button = this.querySelector('button[type="submit"]');
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating Account...';
            button.disabled = true;
        });
    </script>
</body>
</html>
