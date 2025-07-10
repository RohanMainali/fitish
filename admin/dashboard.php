<?php
// admin/dashboard.php - Admin Dashboard
require_once '../includes/auth.class.php';
require_once '../includes/rbac.class.php';
require_once '../includes/user.class.php';
require_once '../includes/workout.class.php';

// Ensure only admin or superadmin can access
RBAC::requireRole(['admin', 'superadmin']);

$auth = new Auth();
$currentUser = $auth->currentUser();
$userObj = new User();
$workoutObj = new Workout();

// Get basic statistics
$totalUsers = $userObj->getTotalCount();
$totalWorkouts = $workoutObj->getTotalCount();
$activeUsers = $userObj->getActiveUsersCount();
$todayWorkouts = $workoutObj->getTodayCount();

// No recent activities display
$recentActivities = [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Fitish Pro</title>
    <link rel="stylesheet" href="../assets/css/moderns.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="dashboard-layout">
        <!-- Admin Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <div class="logo">
                    <i class="fas fa-user-shield"></i>
                    <h2>Admin</h2>
                </div>
            </div>
            
            <nav class="sidebar-nav">
                <a href="dashboard.php" class="nav-item active">
                    <i class="fas fa-tachometer-alt"></i>
                    Dashboard
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
                        <p class="user-role">Administrator</p>
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
                    <h1>Admin Dashboard</h1>
                    <p>Monitor and manage your fitness platform</p>
                </div>
                
                <div class="top-actions">
                    <button class="theme-toggle" id="theme-toggle">
                        <i class="fas fa-moon"></i>
                    </button>
                </div>
            </div>
            
            <!-- Dashboard Content -->
            <div class="dashboard-content">
                <!-- Admin Welcome Section -->
                <div class="welcome-section">
                    <div class="welcome-card">
                        <div class="welcome-content">
                            <h2>👋 Welcome back, <?php echo htmlspecialchars($currentUser['username']); ?>!</h2>
                            <p>Here's what's happening on your fitness platform today</p>
                            <div class="admin-badge">
                                <i class="fas fa-shield-alt"></i>
                                <?php echo ucfirst($currentUser['role']); ?>
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
                                <div class="stat-number"><?php echo number_format($totalUsers); ?></div>
                                <div class="stat-label">Total Users</div>
                                <div class="stat-trend positive">
                                    <i class="fas fa-arrow-up"></i>
                                    <span>+12% this month</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="stat-card secondary">
                            <div class="stat-icon">
                                <i class="fas fa-dumbbell"></i>
                            </div>
                            <div class="stat-content">
                                <div class="stat-number"><?php echo number_format($totalWorkouts); ?></div>
                                <div class="stat-label">Total Workouts</div>
                                <div class="stat-trend positive">
                                    <i class="fas fa-arrow-up"></i>
                                    <span>+24% this month</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="stat-card accent">
                            <div class="stat-icon">
                                <i class="fas fa-user-check"></i>
                            </div>
                            <div class="stat-content">
                                <div class="stat-number"><?php echo number_format($activeUsers); ?></div>
                                <div class="stat-label">Active Users</div>
                                <div class="stat-trend positive">
                                    <i class="fas fa-arrow-up"></i>
                                    <span>+8% this week</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="stat-card warning">
                            <div class="stat-icon">
                                <i class="fas fa-calendar-day"></i>
                            </div>
                            <div class="stat-content">
                                <div class="stat-number"><?php echo number_format($todayWorkouts); ?></div>
                                <div class="stat-label">Today's Workouts</div>
                                <div class="stat-trend neutral">
                                    <i class="fas fa-minus"></i>
                                    <span>Same as yesterday</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="actions-section">
                    <h3 class="section-title">Quick Actions</h3>
                    <div class="action-cards">
                        <div class="action-card">
                            <div class="action-icon">
                                <i class="fas fa-chart-bar"></i>
                            </div>
                            <div class="action-content">
                                <h4>System Analytics</h4>
                                <p>View platform analytics and user insights</p>
                                <a href="analytics.php" class="btn btn-primary">
                                    <i class="fas fa-arrow-right"></i>
                                    View Analytics
                                </a>
                            </div>
                        </div>
                        
                        <div class="action-card">
                            <div class="action-icon">
                                <i class="fas fa-users-cog"></i>
                            </div>
                            <div class="action-content">
                                <h4>Manage Users</h4>
                                <p>View, edit, or delete user accounts</p>
                                <a href="manage-users.php" class="btn btn-secondary">
                                    <i class="fas fa-arrow-right"></i>
                                    Manage Users
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Theme Toggle Script -->
    <script src="../assets/js/darkmode.js"></script>
</body>
</html>
        </div>
    </div>
</body>
</html>
