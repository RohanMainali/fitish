<?php
// profile.php
require_once 'includes/auth.class.php';
require_once 'includes/user.class.php';
require_once 'includes/stats.class.php';
require_once 'includes/fitnesslevel.class.php';
require_once 'includes/validator.class.php';
require_once 'includes/csrf.class.php';

$auth = new Auth();
$auth->requireLogin();
$user = $auth->currentUser();
$userObj = new User();

$message = '';
$csrf_token = CSRF::generateToken();

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!CSRF::validateToken($_POST['csrf_token'] ?? '')) {
        $message = 'Invalid CSRF token.';
    } elseif (isset($_POST['update_profile'])) {
        $new_username = trim($_POST['username'] ?? '');
        $new_email = trim($_POST['email'] ?? '');
        
        if (empty($new_username) || empty($new_email)) {
            $message = 'Username and email are required.';
        } elseif (!Validator::validateEmail($new_email)) {
            $message = 'Please enter a valid email address.';
        } elseif (strlen($new_username) < 3) {
            $message = 'Username must be at least 3 characters long.';
        } else {
            // Check if username/email already exists for other users
            $existingUser = $userObj->getByUsername($new_username);
            $existingEmail = $userObj->getByEmail($new_email);
            
            if ($existingUser && $existingUser['id'] != $user['id']) {
                $message = 'Username already exists.';
            } elseif ($existingEmail && $existingEmail['id'] != $user['id']) {
                $message = 'Email already exists.';
            } else {
                $updateData = [
                    'username' => $new_username,
                    'email' => $new_email
                ];
                $userObj->update($user['id'], $updateData);
                $message = 'Profile updated successfully!';
                // Update current user data
                $user['username'] = $new_username;
                $user['email'] = $new_email;
            }
        }
    } elseif (isset($_POST['change_password'])) {
        $current = $_POST['current_password'] ?? '';
        $new = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';
        
        if (!Validator::validatePassword($new)) {
            $message = 'New password must be at least 6 characters.';
        } elseif ($new !== $confirm) {
            $message = 'New passwords do not match.';
        } elseif (!$userObj->verifyPassword($user['username'], $current)) {
            $message = 'Current password is incorrect.';
        } else {
            $hashed = password_hash($new, PASSWORD_DEFAULT);
            $userObj->update($user['id'], ['password' => $hashed]);
            $message = 'Password updated successfully!';
        }
    } elseif (isset($_POST['toggle_email'])) {
        $reminder = isset($_POST['email_reminder']) ? 1 : 0;
        $userObj->update($user['id'], ['email_reminder' => $reminder]);
        $message = 'Email reminder preference updated.';
        $user['email_reminder'] = $reminder;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile & Settings - Fitish Pro</title>
    <link rel="stylesheet" href="assets/css/moderns.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="dashboard-layout">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <a href="dashboard.php" class="logo">
                    <i class="fas fa-dumbbell logo-icon"></i>
                    <span class="logo-text">Fitish Pro</span>
                </a>
            </div>
            
            <nav class="sidebar-nav">
                <div class="nav-section">
                    <h3 class="nav-section-title">
                        <i class="fas fa-compass"></i>
                        Navigation
                    </h3>
                    <a href="dashboard.php" class="nav-item">
                        <i class="fas fa-chart-line"></i>
                        Dashboard
                    </a>
                    <a href="add_workout.php" class="nav-item">
                        <i class="fas fa-plus-circle"></i>
                        Add Workout
                    </a>
                    <a href="view_workouts.php" class="nav-item">
                        <i class="fas fa-list"></i>
                        View Workouts
                    </a>
                    <a href="stats.php" class="nav-item">
                        <i class="fas fa-weight"></i>
                        Body Stats
                    </a>
                    <a href="goals.php" class="nav-item">
                        <i class="fas fa-bullseye"></i>
                        Goals
                    </a>
                    <a href="leaderboard.php" class="nav-item">
                        <i class="fas fa-trophy"></i>
                        Leaderboard
                    </a>
                </div>
                
                <div class="nav-section">
                    <h3 class="nav-section-title">
                        <i class="fas fa-user-circle"></i>
                        Account
                    </h3>
                    <a href="profile.php" class="nav-item active">
                        <i class="fas fa-user-edit"></i>
                        Profile
                    </a>
                    <?php if (in_array($user['role'], ['admin', 'superadmin'])): ?>
                        <a href="<?php echo $user['role'] === 'superadmin' ? 'superadmin' : 'admin'; ?>/dashboard.php" class="nav-item admin-panel">
                            <i class="fas fa-<?php echo $user['role'] === 'superadmin' ? 'crown' : 'shield-alt'; ?>"></i>
                            <?php echo $user['role'] === 'superadmin' ? 'Superadmin Panel' : 'Admin Panel'; ?>
                        </a>
                    <?php endif; ?>
                </div>
            </nav>
            
            <div class="sidebar-footer">
                <div class="user-profile">
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                    </div>
                    <div class="user-info">
                        <h4 class="user-name"><?php echo htmlspecialchars($user['username']); ?></h4>
                        <p class="user-email"><?php echo htmlspecialchars($user['email']); ?></p>
                    </div>
                </div>
                <a href="logout.php" class="nav-item logout">
                    <i class="fas fa-sign-out-alt"></i>
                    Logout
                </a>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="top-bar">
                <div class="page-title">
                    <h1>Profile & Settings</h1>
                    <p>Manage your account information and preferences</p>
                </div>
                <div class="top-actions">
                    <button type="button" class="btn btn-outline" id="darkmode-toggle">
                        <i class="fas fa-moon"></i>
                        Dark Mode
                    </button>
                </div>
            </div>

                        <div class="dashboard-content">
                <?php if ($message): ?>
                    <div class="alert <?php echo strpos($message, 'successfully') !== false ? 'alert-success' : 'alert-error'; ?>">
                        <i class="fas fa-<?php echo strpos($message, 'successfully') !== false ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>

                <!-- Profile Information Card -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-user-circle"></i>
                            Profile Information
                        </h3>
                    </div>
                    <div class="card-content">
                        <form method="post" action="" id="profileForm">
                            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-user"></i>
                                        Username
                                    </label>
                                    <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" class="form-input" required>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-envelope"></i>
                                        Email Address
                                    </label>
                                    <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" class="form-input" required>
                                </div>
                            </div>

                            <div class="form-actions">
                                <button type="submit" name="update_profile" class="btn btn-primary">
                                    <i class="fas fa-save"></i>
                                    Update Profile
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Change Password Card -->
                <div class="card card-spacing">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-lock"></i>
                            Change Password
                        </h3>
                    </div>
                    <div class="card-content">
                        <form method="post" action="" id="passwordForm">
                            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-key"></i>
                                        Current Password
                                    </label>
                                    <input type="password" name="current_password" class="form-input" required>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-key"></i>
                                        New Password
                                    </label>
                                    <input type="password" name="new_password" class="form-input" required>
                                    <small class="form-help">Must be at least 6 characters long</small>
                                </div>
                            </div>

                            <div class="form-actions">
                                <button type="submit" name="change_password" class="btn btn-primary">
                                    <i class="fas fa-shield-alt"></i>
                                    Change Password
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Account Information Card -->
                <div class="card card-spacing">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-info-circle"></i>
                            Account Information
                        </h3>
                    </div>
                    <div class="card-content">
                        <div class="account-info-grid">
                            <div class="info-item">
                                <div class="info-label">
                                    <i class="fas fa-calendar-plus"></i>
                                    Member Since
                                </div>
                                <div class="info-value">
                                    <?php echo date('F j, Y', strtotime($user['created_at'])); ?>
                                </div>
                            </div>
                            
                            <div class="info-item">
                                <div class="info-label">
                                    <i class="fas fa-user-tag"></i>
                                    Account Type
                                </div>
                                <div class="info-value">
                                    <span class="role-badge role-<?php echo $user['role']; ?>">
                                        <?php echo ucfirst($user['role']); ?>
                                    </span>
                                </div>
                            </div>
                            
                            <div class="info-item">
                                <div class="info-label">
                                    <i class="fas fa-id-card"></i>
                                    User ID
                                </div>
                                <div class="info-value">
                                    #<?php echo $user['id']; ?>
                                </div>
                            </div>
                            
                            <div class="info-item">
                                <div class="info-label">
                                    <i class="fas fa-clock"></i>
                                    Last Login
                                </div>
                                <div class="info-value">
                                    <?php echo isset($user['last_login']) ? date('M j, Y \a\t g:i A', strtotime($user['last_login'])) : 'Never'; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Dark Mode Script -->
    <script src="assets/js/darkmode.js"></script>
</body>
</html>
