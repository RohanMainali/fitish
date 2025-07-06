<?php
// superadmin/setup.php - One-time superadmin creation
require_once '../includes/user.class.php';
require_once '../includes/validator.class.php';
require_once '../config.php';

$config = include '../config.php';
$message = '';
$messageType = '';

// Check if any superadmin already exists
$userObj = new User();
$existingSuperadmins = $userObj->getByRole('superadmin');

if (!empty($existingSuperadmins)) {
    // Superadmin already exists, redirect
    header('Location: ../login.php?error=setup_disabled');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $setupKey = $_POST['setup_key'] ?? '';
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    // Validate setup key from config
    $validSetupKey = $config['SUPERADMIN_SETUP_KEY'];
    
    if ($setupKey !== $validSetupKey) {
        $message = 'Invalid setup key. Contact system administrator.';
        $messageType = 'error';
    } elseif (!Validator::validateUsername($username)) {
        $message = 'Invalid username. Must be 3-50 characters, alphanumeric and underscores only.';
        $messageType = 'error';
    } elseif (!Validator::validateEmail($email)) {
        $message = 'Invalid email address.';
        $messageType = 'error';
    } elseif (!Validator::validatePassword($password)) {
        $message = 'Password must be at least 6 characters long.';
        $messageType = 'error';
    } elseif ($password !== $confirmPassword) {
        $message = 'Passwords do not match.';
        $messageType = 'error';
    } elseif ($userObj->usernameExists($username)) {
        $message = 'Username already exists.';
        $messageType = 'error';
    } elseif ($userObj->emailExists($email)) {
        $message = 'Email already exists.';
        $messageType = 'error';
    } else {
        // Create superadmin account
        if ($userObj->create($username, $email, $password, 'superadmin')) {
            $message = 'Superadmin account created successfully! You can now login.';
            $messageType = 'success';
            
            // Log this important event
            error_log(date('Y-m-d H:i:s') . " - Superadmin account created: $username from IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
            
            // Redirect after 3 seconds
            echo "<script>
                setTimeout(function() {
                    window.location.href = '../login.php?created=1';
                }, 3000);
            </script>";
        } else {
            $message = 'Failed to create superadmin account. Please try again.';
            $messageType = 'error';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Superadmin Setup - Fitish Pro</title>
    <link rel="stylesheet" href="../assets/css/moderns.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .setup-container {
            max-width: 500px;
            margin: 5% auto;
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .setup-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .setup-title {
            font-size: 2rem;
            font-weight: bold;
            color: #1e40af;
            margin-bottom: 0.5rem;
        }
        .setup-subtitle {
            color: #6b7280;
            margin-bottom: 1rem;
        }
        .warning-box {
            background: #fef3c7;
            border: 1px solid #fde68a;
            color: #92400e;
            padding: 1rem;
            border-radius: 6px;
            margin-bottom: 1rem;
            font-size: 0.875rem;
        }
        .form-group {
            margin-bottom: 1rem;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #374151;
        }
        .form-group input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 1rem;
        }
        .form-group input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        .btn-setup {
            width: 100%;
            background: #1e40af;
            color: white;
            padding: 0.75rem;
            border: none;
            border-radius: 6px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        .btn-setup:hover {
            background: #1d4ed8;
        }
        .alert {
            padding: 1rem;
            border-radius: 6px;
            margin-bottom: 1rem;
        }
        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }
        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
        }
        .back-link {
            text-align: center;
            margin-top: 1rem;
        }
        .back-link a {
            color: #6b7280;
            text-decoration: none;
            font-size: 0.875rem;
        }
        .back-link a:hover {
            color: #374151;
        }
    </style>
</head>
<body style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh;">
    <div class="setup-container">
        <div class="setup-header">
            <div class="setup-title">🛡️ Superadmin Setup</div>
            <div class="setup-subtitle">Create the first superadmin account</div>
        </div>

        <div class="warning-box">
            <strong>⚠️ Security Notice:</strong> This page will be automatically disabled after creating the first superadmin account. Only run this setup once.
        </div>

        <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="setup_key">Setup Key</label>
                <input type="password" id="setup_key" name="setup_key" required 
                       placeholder="Enter the setup key...">
                <small style="color: #6b7280; font-size: 0.875rem;">
                    Contact your system administrator for the setup key
                </small>
            </div>

            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required 
                       placeholder="Choose a username...">
            </div>

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" required 
                       placeholder="your@email.com">
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required 
                       placeholder="Choose a strong password...">
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required 
                       placeholder="Confirm your password...">
            </div>

            <button type="submit" class="btn-setup">
                Create Superadmin Account
            </button>
        </form>

        <div class="back-link">
            <a href="../index.php">← Back to Home</a>
        </div>
    </div>

    <script>
        // Add some basic client-side validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match!');
                return false;
            }
            
            if (password.length < 6) {
                e.preventDefault();
                alert('Password must be at least 6 characters long!');
                return false;
            }
        });
    </script>
</body>
</html>
