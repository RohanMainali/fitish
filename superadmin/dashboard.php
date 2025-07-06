<?php
// superadmin/dashboard.php
require_once '../includes/auth.class.php';
require_once '../includes/rbac.class.php';
require_once '../includes/user.class.php';
require_once '../includes/admin_logger.class.php';

// Ensure only superadmin can access
RBAC::requireRole(['superadmin']);

$auth = new Auth();
$user = $auth->currentUser();
$userObj = new User();
$logger = new AdminLogger();

// Get user statistics
$userStats = $userObj->getStats();

// Get recent admin activities
$recentLogs = $logger->getLogs(10, 0);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <style>
        .action-cards {
            display: flex;
            flex-direction: column;
            gap: 2rem;
        }
        .action-card {
            margin-bottom: 0 !important;
        }
    </style>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Superadmin Dashboard - Fitish Pro</title>
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
                <a href="dashboard.php" class="nav-item active">
                    <i class="fas fa-tachometer-alt"></i>
                    Dashboard
                </a>
                <a href="manage-admins.php" class="nav-item">
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
            </nav>
            <div class="sidebar-footer">
                <div class="user-profile">
                    <div class="user-avatar admin">
                        <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                    </div>
                    <div class="user-info">
                        <h4 class="user-name"><?php echo htmlspecialchars($user['username']); ?></h4>
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
                    <h1>Superadmin Dashboard</h1>
                    <p>Complete system administration and user management</p>
                </div>
                <div class="top-actions">
                    <button class="theme-toggle" id="theme-toggle">
                        <i class="fas fa-moon"></i>
                    </button>
                </div>
            </div>

            <div class="dashboard-content">
                <!-- Welcome Section -->
                <div class="welcome-section">
                    <div class="welcome-card">
                        <div class="welcome-content">
                            <h2>👋 Welcome back, <?php echo htmlspecialchars($user['username']); ?>!</h2>
                            <p>Complete system administration and user management</p>
                            <div class="admin-badge">
                                <i class="fas fa-crown"></i>
                                Superadmin
                            </div>
                        </div>
                        <div class="welcome-graphic">
                            <i class="fas fa-chart-line"></i>
                        </div>
                    </div>
                </div>

                <!-- Statistics Overview -->
                <div class="stats-section">
                    <h3 class="section-title">Platform Overview</h3>
                    <div class="stats-grid">
                        <div class="stat-card primary">
                            <div class="stat-icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="stat-content">
                                <div class="stat-number"><?php echo number_format($userStats['total_users']); ?></div>
                                <div class="stat-label">Total Users</div>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-user"></i>
                            </div>
                            <div class="stat-content">
                                <div class="stat-number"><?php echo number_format($userStats['regular_users']); ?></div>
                                <div class="stat-label">Regular Users</div>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-user-shield"></i>
                            </div>
                            <div class="stat-content">
                                <div class="stat-number"><?php echo number_format($userStats['admins']); ?></div>
                                <div class="stat-label">Admins</div>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-crown"></i>
                            </div>
                            <div class="stat-content">
                                <div class="stat-number"><?php echo number_format($userStats['superadmins']); ?></div>
                                <div class="stat-label">Superadmins</div>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-user-check"></i>
                            </div>
                            <div class="stat-content">
                                <div class="stat-number"><?php echo number_format($userStats['active_users']); ?></div>
                                <div class="stat-label">Active Users</div>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-user-times"></i>
                            </div>
                            <div class="stat-content">
                                <div class="stat-number"><?php echo number_format($userStats['inactive_users']); ?></div>
                                <div class="stat-label">Inactive Users</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Cards -->
                <div class="action-cards">
                    <div class="action-card">
                        <div class="action-icon">
                            <i class="fas fa-chart-bar"></i>
                        </div>
                        <div class="action-content">
                            <h4>System Analytics</h4>
                            <p>View comprehensive platform analytics and insights</p>
                            <a href="analytics.php" class="btn btn-primary">
                                <i class="fas fa-arrow-right"></i>
                                View Analytics
                            </a>
                        </div>
                    </div>
                    <div class="action-card">
                        <div class="action-icon">
                            <i class="fas fa-user-cog"></i>
                        </div>
                        <div class="action-content">
                            <h4>Manage Admins</h4>
                            <p>Promote users to admin or manage existing admin accounts</p>
                            <a href="manage-admins.php" class="btn btn-primary">
                                <i class="fas fa-arrow-right"></i>
                                Manage Admins
                            </a>
                        </div>
                    </div>
                    <div class="action-card">
                        <div class="action-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="action-content">
                            <h4>Manage Users</h4>
                            <p>View, edit, or delete user accounts</p>
                            <a href="manage-users.php" class="btn btn-primary">
                                <i class="fas fa-arrow-right"></i>
                                Manage Users
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
