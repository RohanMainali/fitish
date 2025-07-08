<?php
// superadmin/manage-admins.php - Promote users to admin (Superadmin only)
require_once '../includes/auth.class.php';
require_once '../includes/rbac.class.php';
require_once '../includes/user.class.php';
require_once '../includes/admin_logger.class.php';
require_once '../includes/csrf.class.php';

// Ensure only superadmin can access
RBAC::requireRole(['superadmin']);

$auth = new Auth();
$currentUser = $auth->currentUser();
$userObj = new User();
$logger = new AdminLogger();

$message = '';
$messageType = '';
$csrf_token = CSRF::generateToken();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF Protection
    if (!CSRF::validateToken($_POST['csrf_token'] ?? '')) {
        $message = 'Invalid security token. Please try again.';
        $messageType = 'error';
    } else {
        // Promote user to admin
        if (isset($_POST['promote_user_id'])) {
            $userId = (int)$_POST['promote_user_id'];
            $targetUser = $userObj->getById($userId);
            
            if (!$targetUser) {
                $message = 'User not found.';
                $messageType = 'error';
            } elseif ($targetUser['role'] === 'admin') {
                $message = 'User is already an admin.';
                $messageType = 'warning';
            } elseif ($targetUser['role'] === 'superadmin') {
                $message = 'Cannot modify superadmin users.';
                $messageType = 'error';
            } else {
                if ($userObj->promoteToAdmin($userId)) {
                    $message = "Successfully promoted {$targetUser['username']} to admin.";
                    $messageType = 'success';
                    
                    // Log the activity
                    $logger->logActivity(
                        $currentUser['id'],
                        'Promoted user to admin',
                        $userId,
                        "Promoted {$targetUser['username']} from {$targetUser['role']} to admin"
                    );
                } else {
                    $message = 'Failed to promote user. Please try again.';
                    $messageType = 'error';
                }
            }
        }
        
        // Demote admin to user
        elseif (isset($_POST['demote_admin_id'])) {
            $userId = (int)$_POST['demote_admin_id'];
            $targetUser = $userObj->getById($userId);
            
            if (!$targetUser) {
                $message = 'User not found.';
                $messageType = 'error';
            } elseif ($targetUser['role'] === 'user') {
                $message = 'User is already a regular user.';
                $messageType = 'warning';
            } elseif ($targetUser['role'] === 'superadmin') {
                $message = 'Cannot demote superadmin users.';
                $messageType = 'error';
            } else {
                if ($userObj->demoteToUser($userId)) {
                    $message = "Successfully demoted {$targetUser['username']} to regular user.";
                    $messageType = 'success';
                    
                    // Log the activity
                    $logger->logActivity(
                        $currentUser['id'],
                        'Demoted admin to user',
                        $userId,
                        "Demoted {$targetUser['username']} from admin to user"
                    );
                } else {
                    $message = 'Failed to demote user. Please try again.';
                    $messageType = 'error';
                }
            }
        }
    }
}

// Get user data
$searchTerm = $_GET['search'] ?? '';
$roleFilter = $_GET['role'] ?? '';

// Get regular users (for promotion)
$regularUsers = $userObj->getFiltered('user', '1', $searchTerm);

// Get current admins (for demotion)
$adminUsers = $userObj->getFiltered('admin', '', $searchTerm);

// Get superadmins (for display only)
$superadminUsers = $userObj->getFiltered('superadmin', '', $searchTerm);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Admins - Fitish Pro</title>
    <link rel="stylesheet" href="../assets/css/moderns.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .sidebar .logo {
            font-size: 1.3rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .sidebar .logo i {
            font-size: 1.2rem;
        }
        .sidebar .logo h2 {
            font-size: 1.3rem;
            margin: 0;
            font-weight: 700;
        }
        .search-section {
            background: var(--bg-card);
            padding: 1.5rem;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-md);
            margin-bottom: 2rem;
        }
        .search-form {
            display: flex;
            gap: 1rem;
            align-items: end;
        }
        .form-group {
            flex: 1;
        }
        .users-section {
            background: var(--bg-card);
            padding: 1.5rem;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-md);
            margin-bottom: 2rem;
        }
        .section-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: var(--text-primary);
            border-bottom: 2px solid var(--border-medium);
            padding-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .user-card {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem;
            border: 1px solid var(--border-medium);
            border-radius: var(--radius-md);
            margin-bottom: 0.5rem;
            background: var(--bg-muted);
            transition: var(--transition-fast);
        }
        .user-card:hover {
            background: var(--bg-hover);
            border-color: var(--border-primary);
        }
        .user-info {
            flex: 1;
        }
        .user-name {
            font-weight: 600;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.25rem;
        }
        .user-email {
            color: var(--text-tertiary);
            font-size: 0.875rem;
        }
        .user-meta {
            font-size: 0.75rem;
            color: var(--text-muted);
            margin-top: 0.25rem;
        }
        .user-actions {
            display: flex;
            gap: 0.5rem;
        }
        .btn-promote {
            background: var(--success);
            color: white;
            padding: 0.5rem 1rem;
            border: none;
            border-radius: var(--radius-md);
            font-size: 0.875rem;
            cursor: pointer;
            transition: var(--transition-fast);
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }
        .btn-promote:hover {
            background: var(--success-dark);
            transform: translateY(-1px);
        }
        .btn-demote {
            background: var(--danger);
            color: white;
            padding: 0.5rem 1rem;
            border: none;
            border-radius: var(--radius-md);
            font-size: 0.875rem;
            cursor: pointer;
            transition: var(--transition-fast);
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }
        .btn-demote:hover {
            background: #dc2626;
            transform: translateY(-1px);
        }
        .btn-search {
            background: var(--primary);
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: var(--radius-md);
            cursor: pointer;
            transition: var(--transition-fast);
        }
        .btn-search:hover {
            background: var(--primary-dark);
        }
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--primary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 1rem;
            flex-shrink: 0;
        }
        .user-avatar.admin {
            background: #f59e0b;
        }
        .user-avatar.superadmin {
            background: #7c3aed;
        }
        .role-badge {
            padding: 0.25rem 0.5rem;
            border-radius: var(--radius-sm);
            font-size: 0.75rem;
            font-weight: 500;
            text-transform: uppercase;
        }
        .role-user {
            background: #dbeafe;
            color: #1d4ed8;
        }
        .role-admin {
            background: #fde68a;
            color: #92400e;
        }
        .role-superadmin {
            background: #ede9fe;
            color: #7c3aed;
        }
        .empty-state {
            text-align: center;
            color: var(--text-muted);
            font-style: italic;
            padding: 2rem;
        }
    </style>
</head>
<body>
    <div class="dashboard-layout">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <div class="logo">
                    <i class="fas fa-user-shield"></i>
                    <h2>Superadmin</h2>
                </div>
            </div>
            <nav class="sidebar-nav">
                <a href="dashboard.php" class="nav-item">
                    <i class="fas fa-tachometer-alt"></i>
                    Dashboard
                </a>
                <a href="manage-admins.php" class="nav-item active">
                    <i class="fas fa-user-cog"></i>
                    Manage Admins
                </a>
                <a href="manage-users.php" class="nav-item">
                    <i class="fas fa-users"></i>
                    Manage Users
                </a>
                <a href="analytics.php" class="nav-item">
                    <i class="fas fa-chart-bar"></i>
                    Analytics
                </a>
            </nav>
            <div class="sidebar-footer">
                <div class="user-profile">
                    <div class="user-avatar admin">
                        <?php echo strtoupper(substr($currentUser['username'], 0, 1)); ?>
                    </div>
                    <div class="user-info">
                        <h4 class="user-name"><?php echo htmlspecialchars($currentUser['username']); ?></h4>
                        <p class="user-role">Superadmin</p>
                    </div>
                </div>
                <a href="../logout.php" class="nav-item logout">
                    <i class="fas fa-sign-out-alt"></i>
                    Logout
                </a>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Top Bar -->
            <div class="top-bar">
                <div class="page-title">
                    <h1>Manage Admins</h1>
                    <p>Promote users to admin or demote admins to regular users</p>
                </div>
                <div class="top-actions">
                    <button class="theme-toggle" id="theme-toggle">
                        <i class="fas fa-moon"></i>
                    </button>
                </div>
            </div>

            <div class="dashboard-content">
                <!-- Messages -->
                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $messageType; ?>">
                        <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : ($messageType === 'error' ? 'exclamation-circle' : 'exclamation-triangle'); ?>"></i>
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>

                <!-- Search Section -->
                <div class="search-section">
                    <h3 class="section-title">
                        <i class="fas fa-search"></i>
                        Search Users
                    </h3>
                    <form method="GET" class="search-form">
                        <div class="form-group">
                            <label for="search">Search by username or email</label>
                            <input type="text" id="search" name="search" 
                                   value="<?php echo htmlspecialchars($searchTerm); ?>" 
                                   placeholder="Enter username or email..." class="form-control">
                        </div>
                        <button type="submit" class="btn-search">
                            <i class="fas fa-search"></i>
                            Search
                        </button>
                        <?php if ($searchTerm): ?>
                            <a href="manage-admins.php" class="btn btn-secondary">
                                <i class="fas fa-times"></i>
                                Clear
                            </a>
                        <?php endif; ?>
                    </form>
                </div>

                <!-- Regular Users (Can be promoted) -->
                <div class="users-section">
                    <h3 class="section-title">
                        <i class="fas fa-arrow-up"></i>
                        Regular Users (Can be promoted to Admin)
                    </h3>
                    
                    <?php if (empty($regularUsers)): ?>
                        <div class="empty-state">
                            <i class="fas fa-users" style="font-size: 3rem; color: var(--text-muted); margin-bottom: 1rem;"></i>
                            <p>
                                <?php if ($searchTerm): ?>
                                    No regular users found matching "<?php echo htmlspecialchars($searchTerm); ?>"
                                <?php else: ?>
                                    No regular users available for promotion
                                <?php endif; ?>
                            </p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($regularUsers as $user): ?>
                            <div class="user-card">
                                <div class="user-info">
                                    <div class="user-name">
                                        <div class="user-avatar">
                                            <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                                        </div>
                                        <div>
                                            <span><?php echo htmlspecialchars($user['username']); ?></span>
                                            <span class="role-badge role-user">User</span>
                                        </div>
                                    </div>
                                    <div class="user-email">
                                        <i class="fas fa-envelope"></i>
                                        <?php echo htmlspecialchars($user['email']); ?>
                                    </div>
                                    <div class="user-meta">
                                        <i class="fas fa-calendar-plus"></i>
                                        Joined: <?php echo date('M j, Y', strtotime($user['created_at'])); ?>
                                        <?php if ($user['last_login']): ?>
                                            | <i class="fas fa-sign-in-alt"></i> Last login: <?php echo date('M j, Y', strtotime($user['last_login'])); ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="user-actions">
                                    <form method="POST" style="display: inline;" 
                                          onsubmit="return confirm('Are you sure you want to promote <?php echo htmlspecialchars($user['username']); ?> to admin?');">
                                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                        <input type="hidden" name="promote_user_id" value="<?php echo $user['id']; ?>">
                                        <button type="submit" class="btn-promote">
                                            <i class="fas fa-arrow-up"></i>
                                            Promote to Admin
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Current Admins (Can be demoted) -->
                <div class="users-section">
                    <h3 class="section-title">
                        <i class="fas fa-arrow-down"></i>
                        Current Admins (Can be demoted to User)
                    </h3>
                    
                    <?php if (empty($adminUsers)): ?>
                        <div class="empty-state">
                            <i class="fas fa-user-shield" style="font-size: 3rem; color: var(--text-muted); margin-bottom: 1rem;"></i>
                            <p>
                                <?php if ($searchTerm): ?>
                                    No admins found matching "<?php echo htmlspecialchars($searchTerm); ?>"
                                <?php else: ?>
                                    No admin users found
                                <?php endif; ?>
                            </p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($adminUsers as $user): ?>
                            <div class="user-card">
                                <div class="user-info">
                                    <div class="user-name">
                                        <div class="user-avatar admin">
                                            <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                                        </div>
                                        <div>
                                            <span><?php echo htmlspecialchars($user['username']); ?></span>
                                            <span class="role-badge role-admin">Admin</span>
                                        </div>
                                    </div>
                                    <div class="user-email">
                                        <i class="fas fa-envelope"></i>
                                        <?php echo htmlspecialchars($user['email']); ?>
                                    </div>
                                    <div class="user-meta">
                                        <i class="fas fa-calendar-plus"></i>
                                        Joined: <?php echo date('M j, Y', strtotime($user['created_at'])); ?>
                                        <?php if ($user['last_login']): ?>
                                            | <i class="fas fa-sign-in-alt"></i> Last login: <?php echo date('M j, Y', strtotime($user['last_login'])); ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="user-actions">
                                    <form method="POST" style="display: inline;" 
                                          onsubmit="return confirm('Are you sure you want to demote <?php echo htmlspecialchars($user['username']); ?> to regular user?');">
                                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                        <input type="hidden" name="demote_admin_id" value="<?php echo $user['id']; ?>">
                                        <button type="submit" class="btn-demote">
                                            <i class="fas fa-arrow-down"></i>
                                            Demote to User
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Superadmins (Display only) -->
                <?php if (!empty($superadminUsers)): ?>
                    <div class="users-section">
                        <h3 class="section-title">
                            <i class="fas fa-crown"></i>
                            Superadmins (Protected)
                        </h3>
                        
                        <?php foreach ($superadminUsers as $user): ?>
                            <div class="user-card">
                                <div class="user-info">
                                    <div class="user-name">
                                        <div class="user-avatar superadmin">
                                            <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                                        </div>
                                        <div>
                                            <span><?php echo htmlspecialchars($user['username']); ?></span>
                                            <span class="role-badge role-superadmin">Superadmin</span>
                                            <?php if ($user['id'] == $currentUser['id']): ?>
                                                <span style="color: var(--primary); font-weight: 600;">(You)</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="user-email">
                                        <i class="fas fa-envelope"></i>
                                        <?php echo htmlspecialchars($user['email']); ?>
                                    </div>
                                    <div class="user-meta">
                                        <i class="fas fa-calendar-plus"></i>
                                        Joined: <?php echo date('M j, Y', strtotime($user['created_at'])); ?>
                                        <?php if ($user['last_login']): ?>
                                            | <i class="fas fa-sign-in-alt"></i> Last login: <?php echo date('M j, Y', strtotime($user['last_login'])); ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="user-actions">
                                    <span style="color: var(--text-muted); font-size: 0.875rem; display: flex; align-items: center; gap: 0.25rem;">
                                        <i class="fas fa-shield-alt"></i>
                                        Protected Role
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script src="../assets/js/darkmode.js"></script>
</body>
</html>
