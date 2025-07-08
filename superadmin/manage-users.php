<?php
// superadmin/manage-users.php - Complete user management (Superadmin)
require_once '../includes/auth.class.php';
require_once '../includes/rbac.class.php';
require_once '../includes/user.class.php';
require_once '../includes/admin_logger.class.php';
require_once '../includes/csrf.class.php';
require_once '../includes/validator.class.php';

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
        // Create new user
        if (isset($_POST['create_user'])) {
            $username = trim($_POST['username']);
            $email = trim($_POST['email']);
            $password = $_POST['password'];
            $role = $_POST['role'];
            $isActive = isset($_POST['is_active']) ? 1 : 0;
            
            // Validation
            if (!Validator::validateUsername($username)) {
                $message = 'Invalid username. Must be 3-50 characters, alphanumeric and underscores only.';
                $messageType = 'error';
            } elseif (!Validator::validateEmail($email)) {
                $message = 'Invalid email address.';
                $messageType = 'error';
            } elseif (!Validator::validatePassword($password)) {
                $message = 'Password must be at least 6 characters long.';
                $messageType = 'error';
            } elseif ($userObj->usernameExists($username)) {
                $message = 'Username already exists.';
                $messageType = 'error';
            } elseif ($userObj->emailExists($email)) {
                $message = 'Email already exists.';
                $messageType = 'error';
            } else {
                if ($userObj->create($username, $email, $password, $role)) {
                    // Set active status
                    $newUser = $userObj->getByUsername($username);
                    if ($newUser && !$isActive) {
                        $userObj->update($newUser['id'], ['is_active' => 0]);
                    }
                    
                    $message = "Successfully created user: $username";
                    $messageType = 'success';
                    
                    // Log the activity
                    $logger->logActivity(
                        $currentUser['id'],
                        'Created new user',
                        $newUser['id'],
                        "Created user $username with role $role"
                    );
                } else {
                    $message = 'Failed to create user. Please try again.';
                    $messageType = 'error';
                }
            }
        }
        
        // Update user
        elseif (isset($_POST['update_user'])) {
            $userId = (int)$_POST['user_id'];
            $username = trim($_POST['username']);
            $email = trim($_POST['email']);
            $role = $_POST['role'];
            $isActive = isset($_POST['is_active']) ? 1 : 0;
            $newPassword = $_POST['new_password'];
            
            $targetUser = $userObj->getById($userId);
            if (!$targetUser) {
                $message = 'User not found.';
                $messageType = 'error';
            } elseif ($targetUser['role'] === 'superadmin' && $userId != $currentUser['id']) {
                $message = 'Cannot modify other superadmin accounts.';
                $messageType = 'error';
            } else {
                // Validation
                if (!Validator::validateUsername($username)) {
                    $message = 'Invalid username.';
                    $messageType = 'error';
                } elseif (!Validator::validateEmail($email)) {
                    $message = 'Invalid email address.';
                    $messageType = 'error';
                } elseif ($userObj->usernameExists($username, $userId)) {
                    $message = 'Username already exists.';
                    $messageType = 'error';
                } elseif ($userObj->emailExists($email, $userId)) {
                    $message = 'Email already exists.';
                    $messageType = 'error';
                } else {
                    $updateData = [
                        'username' => $username,
                        'email' => $email,
                        'role' => $role,
                        'is_active' => $isActive
                    ];
                    
                    // Add password if provided
                    if (!empty($newPassword)) {
                        if (!Validator::validatePassword($newPassword)) {
                            $message = 'New password must be at least 6 characters long.';
                            $messageType = 'error';
                        } else {
                            $updateData['password'] = password_hash($newPassword, PASSWORD_DEFAULT);
                        }
                    }
                    
                    if ($messageType !== 'error' && $userObj->update($userId, $updateData)) {
                        $message = "Successfully updated user: $username";
                        $messageType = 'success';
                        
                        // Log the activity
                        $logger->logActivity(
                            $currentUser['id'],
                            'Updated user',
                            $userId,
                            "Updated user $username"
                        );
                    } elseif ($messageType !== 'error') {
                        $message = 'Failed to update user. Please try again.';
                        $messageType = 'error';
                    }
                }
            }
        }
        
        // Delete user
        elseif (isset($_POST['delete_user'])) {
            $userId = (int)$_POST['user_id'];
            $targetUser = $userObj->getById($userId);
            
            if (!$targetUser) {
                $message = 'User not found.';
                $messageType = 'error';
            } elseif ($targetUser['role'] === 'superadmin') {
                $message = 'Cannot delete superadmin accounts.';
                $messageType = 'error';
            } elseif ($userId == $currentUser['id']) {
                $message = 'Cannot delete your own account.';
                $messageType = 'error';
            } else {
                if ($userObj->delete($userId)) {
                    $message = "Successfully deleted user: {$targetUser['username']}";
                    $messageType = 'success';
                    
                    // Log the activity
                    $logger->logActivity(
                        $currentUser['id'],
                        'Deleted user',
                        null,
                        "Deleted user {$targetUser['username']} (ID: $userId)"
                    );
                } else {
                    $message = 'Failed to delete user. Please try again.';
                    $messageType = 'error';
                }
            }
        }
    }
}

// Get users with filtering
$searchTerm = $_GET['search'] ?? '';
$roleFilter = $_GET['role'] ?? '';
$activeFilter = $_GET['active'] ?? '';

$users = $userObj->getFiltered($roleFilter, $activeFilter, $searchTerm);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Fitish Pro</title>
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
        .section {
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
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1rem;
        }
        .form-row {
            display: flex;
            gap: 1rem;
            align-items: end;
            flex-wrap: wrap;
        }
        .user-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }
        .user-table th,
        .user-table td {
            padding: 0.75rem;
            text-align: left;
            border-bottom: 1px solid var(--border-medium);
            vertical-align: middle;
        }
        .user-table th {
            background: var(--bg-muted);
            font-weight: 600;
            color: var(--text-secondary);
            position: sticky;
            top: 0;
        }
        .user-table tr:hover {
            background: var(--bg-hover);
        }
        .user-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: var(--primary);
            color: white;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.875rem;
            margin-right: 0.5rem;
            flex-shrink: 0;
        }
        .user-avatar.admin {
            background: #f59e0b;
        }
        .user-avatar.superadmin {
            background: #7c3aed;
        }
        .user-info {
            display: flex;
            align-items: center;
        }
        .role-badge {
            padding: 0.25rem 0.5rem;
            border-radius: var(--radius-sm);
            font-size: 0.75rem;
            font-weight: 500;
            text-transform: uppercase;
        }
        .role-user { background: #dbeafe; color: #1d4ed8; }
        .role-admin { background: #fde68a; color: #92400e; }
        .role-superadmin { background: #ede9fe; color: #7c3aed; }
        .status-active { 
            color: var(--success); 
            display: flex; 
            align-items: center; 
            gap: 0.25rem;
        }
        .status-inactive { 
            color: var(--danger); 
            display: flex; 
            align-items: center; 
            gap: 0.25rem;
        }
        .btn-group {
            display: flex;
            gap: 0.5rem;
        }
        .btn-sm {
            padding: 0.375rem 0.75rem;
            font-size: 0.75rem;
            border: none;
            border-radius: var(--radius-md);
            cursor: pointer;
            transition: var(--transition-fast);
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }
        .btn-edit { 
            background: var(--primary); 
            color: white; 
        }
        .btn-edit:hover { 
            background: var(--primary-dark); 
            transform: translateY(-1px);
        }
        .btn-delete { 
            background: var(--danger); 
            color: white; 
        }
        .btn-delete:hover { 
            background: #dc2626; 
            transform: translateY(-1px);
        }
        .filter-controls {
            display: flex;
            gap: 1rem;
            align-items: center;
            margin-bottom: 1rem;
            flex-wrap: wrap;
        }
        .filter-controls input,
        .filter-controls select {
            padding: 0.5rem;
            border: 2px solid var(--border-medium);
            border-radius: var(--radius-md);
            font-size: 0.875rem;
            transition: var(--transition-fast);
        }
        .filter-controls input:focus,
        .filter-controls select:focus {
            outline: none;
            border-color: var(--primary);
        }
        .filter-controls input[type="text"] {
            flex: 1;
            min-width: 200px;
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        .modal-content {
            background-color: var(--bg-card);
            margin: 5% auto;
            padding: 2rem;
            border-radius: var(--radius-lg);
            width: 90%;
            max-width: 500px;
            box-shadow: var(--shadow-xl);
        }
        .close {
            color: var(--text-muted);
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            transition: var(--transition-fast);
        }
        .close:hover { 
            color: var(--text-primary); 
        }
        .empty-state {
            text-align: center;
            color: var(--text-muted);
            padding: 3rem;
        }
        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: var(--text-muted);
        }
        .table-responsive {
            overflow-x: auto;
            border-radius: var(--radius-md);
        }
        .font-weight-600 {
            font-weight: 600;
        }
        .text-muted {
            color: var(--text-muted);
        }
        .text-primary {
            color: var(--primary);
        }
        .fa-sm {
            font-size: 0.875em;
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
                <a href="manage-admins.php" class="nav-item">
                    <i class="fas fa-user-cog"></i>
                    Manage Admins
                </a>
                <a href="manage-users.php" class="nav-item active">
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
                    <div class="user-avatar superadmin">
                        <?php echo strtoupper(substr($currentUser['username'], 0, 1)); ?>
                    </div>
                    <div class="user-details">
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
                    <h1>Manage Users</h1>
                    <p>Complete user management - Create, Edit, Delete users (Total: <?php echo count($users); ?>)</p>
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

                <!-- Create User Section -->
                <div class="section">
                    <h3 class="section-title">
                        <i class="fas fa-plus"></i>
                        Create New User
                    </h3>
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="username">Username</label>
                                <input type="text" name="username" id="username" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" name="email" id="email" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="password">Password</label>
                                <input type="password" name="password" id="password" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="role">Role</label>
                                <select name="role" id="role" class="form-control" required>
                                    <option value="user">User</option>
                                    <option value="admin">Admin</option>
                                    <option value="superadmin">Superadmin</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="is_active" value="1" checked>
                                <span class="checkmark"></span>
                                Account Active
                            </label>
                        </div>
                        <button type="submit" name="create_user" class="btn btn-primary">
                            <i class="fas fa-plus"></i>
                            Create User
                        </button>
                    </form>
                </div>

                <!-- Filter Section -->
                <div class="section">
                    <h3 class="section-title">
                        <i class="fas fa-filter"></i>
                        Filter Users
                    </h3>
                    <form method="GET" class="form-row">
                        <div class="form-group">
                            <label for="search">Search</label>
                            <input type="text" name="search" id="search" class="form-control" value="<?php echo htmlspecialchars($searchTerm); ?>" placeholder="Username or email...">
                        </div>
                        <div class="form-group">
                            <label for="role_filter">Role</label>
                            <select name="role" id="role_filter" class="form-control">
                                <option value="">All Roles</option>
                                <option value="user"<?php echo $roleFilter === 'user' ? ' selected' : ''; ?>>User</option>
                                <option value="admin"<?php echo $roleFilter === 'admin' ? ' selected' : ''; ?>>Admin</option>
                                <option value="superadmin"<?php echo $roleFilter === 'superadmin' ? ' selected' : ''; ?>>Superadmin</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="active_filter">Status</label>
                            <select name="active" id="active_filter" class="form-control">
                                <option value="">All Status</option>
                                <option value="1"<?php echo $activeFilter === '1' ? ' selected' : ''; ?>>Active</option>
                                <option value="0"<?php echo $activeFilter === '0' ? ' selected' : ''; ?>>Inactive</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i>
                            Filter
                        </button>
                        <a href="manage-users.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i>
                            Clear
                        </a>
                    </form>
                </div>

                <!-- Users Table -->
                <div class="section">
                    <h3 class="section-title">
                        <i class="fas fa-users"></i>
                        Users List
                    </h3>
                    
                    <!-- Enhanced Filter Controls -->
                    <div class="filter-controls">
                        <input type="text" id="userFilter" placeholder="🔍 Search users..." class="form-control">
                        <select id="roleFilter" class="form-control">
                            <option value="">👥 All Roles</option>
                            <option value="user">👤 User</option>
                            <option value="admin">👨‍💼 Admin</option>
                            <option value="superadmin">🔧 Super Admin</option>
                        </select>
                        <select id="statusFilter" class="form-control">
                            <option value="">📊 All Status</option>
                            <option value="active">✅ Active</option>
                            <option value="inactive">❌ Inactive</option>
                        </select>
                        <select id="sortUsers" class="form-control">
                            <option value="id-asc">🔢 ID Low to High</option>
                            <option value="id-desc">🔢 ID High to Low</option>
                            <option value="username-asc">🔤 Username A-Z</option>
                            <option value="username-desc">🔤 Username Z-A</option>
                            <option value="email-asc">📧 Email A-Z</option>
                            <option value="created-desc">📅 Newest First</option>
                            <option value="created-asc">📅 Oldest First</option>
                            <option value="login-desc">🕒 Recent Login</option>
                        </select>
                    </div>
                    
                    <?php if (empty($users)): ?>
                        <div class="empty-state">
                            <i class="fas fa-users"></i>
                            <h3>No users found</h3>
                            <p>No users found matching your criteria.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="user-table" id="usersTable">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>User</th>
                                        <th>Email</th>
                                        <th>Role</th>
                                        <th>Status</th>
                                        <th>Joined</th>
                                        <th>Last Login</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($users as $user): ?>
                                        <tr>
                                            <td>
                                                <span class="text-muted">#<?php echo $user['id']; ?></span>
                                            </td>
                                            <td>
                                                <div class="user-info">
                                                    <div class="user-avatar <?php echo $user['role']; ?>">
                                                        <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                                                    </div>
                                                    <div>
                                                        <div class="font-weight-600">
                                                            <?php echo htmlspecialchars($user['username']); ?>
                                                            <?php if ($user['id'] == $currentUser['id']): ?>
                                                                <span class="text-primary font-weight-600">(You)</span>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="text-muted">
                                                    <i class="fas fa-envelope fa-sm"></i>
                                                    <?php echo htmlspecialchars($user['email']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="role-badge role-<?php echo $user['role']; ?>">
                                                    <?php echo ucfirst($user['role']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="<?php echo $user['is_active'] ? 'status-active' : 'status-inactive'; ?>">
                                                    <i class="fas fa-<?php echo $user['is_active'] ? 'check-circle' : 'times-circle'; ?>"></i>
                                                    <?php echo $user['is_active'] ? 'Active' : 'Inactive'; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="text-muted">
                                                    <i class="fas fa-calendar-alt fa-sm"></i>
                                                    <?php echo date('M j, Y', strtotime($user['created_at'])); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="text-muted">
                                                    <i class="fas fa-clock fa-sm"></i>
                                                    <?php echo $user['last_login'] ? date('M j, Y', strtotime($user['last_login'])) : 'Never'; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <?php if ($user['role'] !== 'superadmin' || $user['id'] == $currentUser['id']): ?>
                                                        <button class="btn-sm btn-edit" onclick="editUser(<?php echo htmlspecialchars(json_encode($user)); ?>)">
                                                            <i class="fas fa-edit"></i>
                                                            Edit
                                                        </button>
                                                    <?php endif; ?>
                                                    
                                                    <?php if ($user['role'] !== 'superadmin' && $user['id'] != $currentUser['id']): ?>
                                                        <form method="POST" style="display: inline;" 
                                                              onsubmit="return confirm('Are you sure you want to delete <?php echo htmlspecialchars($user['username']); ?>?');">
                                                            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                            <button type="submit" name="delete_user" class="btn-sm btn-delete">
                                                                <i class="fas fa-trash"></i>
                                                                Delete
                                                            </button>
                                                        </form>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <!-- Edit User Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h3 style="margin-bottom: 1.5rem; color: var(--text-primary); display: flex; align-items: center; gap: 0.5rem;">
                <i class="fas fa-edit"></i>
                Edit User
            </h3>
            <form method="POST" id="editForm">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <input type="hidden" name="user_id" id="edit_user_id">
                
                <div class="form-group">
                    <label for="edit_username">Username</label>
                    <input type="text" name="username" id="edit_username" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="edit_email">Email</label>
                    <input type="email" name="email" id="edit_email" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="edit_role">Role</label>
                    <select name="role" id="edit_role" class="form-control" required>
                        <option value="user">User</option>
                        <option value="admin">Admin</option>
                        <option value="superadmin">Superadmin</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="edit_password">New Password (leave blank to keep current)</label>
                    <input type="password" name="new_password" id="edit_password" class="form-control">
                </div>
                
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="is_active" id="edit_active" value="1">
                        <span class="checkmark"></span>
                        Account Active
                    </label>
                </div>
                
                <div style="text-align: right; margin-top: 1.5rem; display: flex; gap: 0.5rem; justify-content: flex-end;">
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">
                        <i class="fas fa-times"></i>
                        Cancel
                    </button>
                    <button type="submit" name="update_user" class="btn btn-primary">
                        <i class="fas fa-save"></i>
                        Update User
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="../assets/js/darkmode.js"></script>

    <script>
        function editUser(user) {
            document.getElementById('edit_user_id').value = user.id;
            document.getElementById('edit_username').value = user.username;
            document.getElementById('edit_email').value = user.email;
            document.getElementById('edit_role').value = user.role;
            document.getElementById('edit_active').checked = user.is_active == 1;
            document.getElementById('edit_password').value = '';
            
            document.getElementById('editModal').style.display = 'block';
        }
        
        function closeModal() {
            document.getElementById('editModal').style.display = 'none';
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('editModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }
        
        // User Table Filtering and Sorting
        const userFilter = document.getElementById('userFilter');
        const roleFilter = document.getElementById('roleFilter');
        const statusFilter = document.getElementById('statusFilter');
        const sortSelect = document.getElementById('sortUsers');
        const table = document.getElementById('usersTable');
        
        if (table) {
            function filterAndSortTable() {
                const tbody = table.querySelector('tbody');
                if (!tbody) return;
                
                const rows = Array.from(tbody.querySelectorAll('tr'));
                const filterValue = userFilter.value.toLowerCase();
                const roleValue = roleFilter.value;
                const statusValue = statusFilter.value;
                const sortValue = sortSelect.value;
                
                // Filter rows
                rows.forEach(row => {
                    const username = (row.cells[1] ? row.cells[1].textContent : '').toLowerCase();
                    const email = (row.cells[2] ? row.cells[2].textContent : '').toLowerCase();
                    const role = (row.cells[3] ? row.cells[3].textContent : '').toLowerCase();
                    const status = (row.cells[4] ? row.cells[4].textContent : '').toLowerCase();
                    
                    const matchesSearch = username.includes(filterValue) || email.includes(filterValue);
                    const matchesRole = !roleValue || role.includes(roleValue.toLowerCase());
                    const matchesStatus = !statusValue || status.includes(statusValue.toLowerCase());
                    
                    row.style.display = (matchesSearch && matchesRole && matchesStatus) ? '' : 'none';
                });
                
                // Sort visible rows
                const visibleRows = rows.filter(row => row.style.display !== 'none');
                visibleRows.sort((a, b) => {
                    let aVal, bVal;
                    
                    switch(sortValue) {
                        case 'id-desc':
                        case 'id-asc':
                            aVal = parseInt(a.cells[0] ? a.cells[0].textContent : '0');
                            bVal = parseInt(b.cells[0] ? b.cells[0].textContent : '0');
                            break;
                        case 'username-asc':
                        case 'username-desc':
                            aVal = (a.cells[1] ? a.cells[1].textContent : '').toLowerCase();
                            bVal = (b.cells[1] ? b.cells[1].textContent : '').toLowerCase();
                            return sortValue === 'username-asc' ? aVal.localeCompare(bVal) : bVal.localeCompare(aVal);
                        case 'email-asc':
                            aVal = (a.cells[2] ? a.cells[2].textContent : '').toLowerCase();
                            bVal = (b.cells[2] ? b.cells[2].textContent : '').toLowerCase();
                            return aVal.localeCompare(bVal);
                        case 'created-desc':
                        case 'created-asc':
                            aVal = new Date(a.cells[5] ? a.cells[5].textContent : '');
                            bVal = new Date(b.cells[5] ? b.cells[5].textContent : '');
                            break;
                        case 'login-desc':
                            aVal = new Date(a.cells[6] ? a.cells[6].textContent : '');
                            bVal = new Date(b.cells[6] ? b.cells[6].textContent : '');
                            break;
                    }
                    
                    if (sortValue.includes('-desc')) {
                        return bVal - aVal;
                    } else {
                        return aVal - bVal;
                    }
                });
                
                // Re-append sorted rows
                visibleRows.forEach(row => tbody.appendChild(row));
            }
            
            userFilter.addEventListener('input', filterAndSortTable);
            roleFilter.addEventListener('change', filterAndSortTable);
            statusFilter.addEventListener('change', filterAndSortTable);
            sortSelect.addEventListener('change', filterAndSortTable);
        }
    </script>
</body>
</html>
