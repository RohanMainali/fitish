<?php
// admin/analytics.php - Comprehensive Analytics Dashboard for Admins
require_once '../includes/auth.class.php';
require_once '../includes/rbac.class.php';
require_once '../includes/system_analytics.class.php';

// Ensure only admin or superadmin can access
RBAC::requireRole(['admin', 'superadmin']);

$auth = new Auth();
$currentUser = $auth->currentUser();
$analytics = new SystemAnalytics();

// Get all analytics data
$userAnalytics = $analytics->getUserAnalytics();
$workoutAnalytics = $analytics->getWorkoutAnalytics();
$workoutTypes = $analytics->getWorkoutTypeDistribution();
$goalsAnalytics = $analytics->getGoalsAnalytics();
$dailyTrends = $analytics->getDailyActivityTrends();
$userEngagement = $analytics->getUserEngagement();
$topPerformers = $analytics->getTopPerformers();

// Get filter parameters
$timeFilter = $_GET['time'] ?? '30';
$viewType = $_GET['view'] ?? 'overview';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics Dashboard - Fitish Pro</title>
    <link rel="stylesheet" href="../assets/css/moderns.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        .analytics-nav {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            flex-wrap: wrap;
        }
        .nav-button {
            padding: 0.75rem 1.5rem;
            background: var(--bg-card);
            color: var(--text-secondary);
            text-decoration: none;
            border-radius: var(--radius-lg);
            font-weight: 500;
            transition: var(--transition-fast);
            border: 2px solid var(--border-medium);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .nav-button.active {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }
        .nav-button:hover {
            background: var(--bg-hover);
            transform: translateY(-1px);
        }
        .nav-button.active:hover {
            background: var(--primary-dark);
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        .stat-card {
            background: var(--bg-card);
            padding: 1.5rem;
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-lg);
            text-align: center;
            transition: var(--transition-fast);
            border: 1px solid var(--border-light);
        }
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-xl);
        }
        .stat-icon {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
            color: var(--primary);
        }
        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
        }
        .stat-label {
            color: var(--text-tertiary);
            font-weight: 500;
            font-size: 0.875rem;
        }
        .stat-change {
            font-size: 0.875rem;
            margin-top: 0.5rem;
        }
        .stat-change.positive {
            color: var(--success);
        }
        .stat-change.negative {
            color: var(--danger);
        }
        .chart-section {
            background: var(--bg-card);
            padding: 2rem;
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-lg);
            margin-bottom: 2rem;
            border: 1px solid var(--border-light);
        }
        .chart-container {
            position: relative;
            height: 400px;
            margin-top: 1rem;
        }
        .table-section {
            background: var(--bg-card);
            padding: 2rem;
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-lg);
            margin-bottom: 2rem;
            border: 1px solid var(--border-light);
        }
        .analytics-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }
        .analytics-table th,
        .analytics-table td {
            padding: 0.75rem;
            text-align: left;
            border-bottom: 1px solid var(--border-medium);
            vertical-align: middle;
        }
        .analytics-table th {
            background: var(--bg-muted);
            font-weight: 600;
            color: var(--text-secondary);
            position: sticky;
            top: 0;
        }
        .analytics-table tr:hover {
            background: var(--bg-hover);
        }
        .section-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .metric-badge {
            padding: 0.25rem 0.75rem;
            border-radius: var(--radius-full);
            font-size: 0.875rem;
            font-weight: 500;
        }
        .badge-high {
            background: var(--success-light);
            color: var(--success-dark);
        }
        .badge-medium {
            background: var(--warning-light);
            color: var(--warning);
        }
        .badge-low {
            background: var(--danger-light);
            color: var(--danger);
        }
        .export-section {
            background: var(--bg-card);
            padding: 2rem;
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-lg);
            text-align: center;
            margin-bottom: 2rem;
            border: 1px solid var(--border-light);
        }
        .btn-export {
            background: var(--primary);
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: var(--radius-lg);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            margin: 0 0.5rem;
            font-weight: 500;
            transition: var(--transition-fast);
        }
        .btn-export:hover {
            background: var(--primary-dark);
            transform: translateY(-1px);
        }
        .table-responsive {
            overflow-x: auto;
            border-radius: var(--radius-md);
        }
        .user-avatar {
            background: var(--primary);
        }
        .user-avatar.admin {
            background: #f59e0b;
        }
        .user-avatar.superadmin {
            background: #7c3aed;
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
    </style>
</head>
<body>
    <div class="dashboard-layout">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <div class="logo">
                    <i class="fas fa-user-cog"></i>
                    <h2>Admin Panel</h2>
                </div>
            </div>
            <nav class="sidebar-nav">
                <a href="dashboard.php" class="nav-item">
                    <i class="fas fa-tachometer-alt"></i>
                    Dashboard
                </a>
                <a href="manage-users.php" class="nav-item">
                    <i class="fas fa-users"></i>
                    Manage Users
                </a>
                <a href="analytics.php" class="nav-item active">
                    <i class="fas fa-chart-bar"></i>
                    Analytics
                </a>
            </nav>
            <div class="sidebar-footer">
                <div class="user-profile">
                    <div class="user-avatar admin">
                        <?php echo strtoupper(substr($currentUser['username'], 0, 1)); ?>
                    </div>
                    <div class="user-details">
                        <h4 class="user-name"><?php echo htmlspecialchars($currentUser['username']); ?></h4>
                        <p class="user-role">Admin</p>
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
                    <h1>Analytics Dashboard</h1>
                    <p>Comprehensive platform analytics and insights - Data as of <?php echo date('F j, Y g:i A'); ?></p>
                </div>
                <div class="top-actions">
                    <button class="theme-toggle" id="theme-toggle">
                        <i class="fas fa-moon"></i>
                    </button>
                </div>
            </div>

            <div class="dashboard-content">

                <!-- Navigation -->
                <div class="analytics-nav">
                    <a href="?view=overview" class="nav-button <?php echo $viewType === 'overview' ? 'active' : ''; ?>">
                        <i class="fas fa-chart-line"></i>
                        Overview
                    </a>
                    <a href="?view=users" class="nav-button <?php echo $viewType === 'users' ? 'active' : ''; ?>">
                        <i class="fas fa-users"></i>
                        Users
                    </a>
                    <a href="?view=workouts" class="nav-button <?php echo $viewType === 'workouts' ? 'active' : ''; ?>">
                        <i class="fas fa-dumbbell"></i>
                        Workouts
                    </a>
                    <a href="?view=engagement" class="nav-button <?php echo $viewType === 'engagement' ? 'active' : ''; ?>">
                        <i class="fas fa-trophy"></i>
                        Engagement
                    </a>
                </div>

                <?php if ($viewType === 'overview' || $viewType === ''): ?>
                    <!-- Overview Stats -->
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-icon"><i class="fas fa-users"></i></div>
                            <div class="stat-number"><?php echo number_format($userAnalytics['total_users'] ?? 0); ?></div>
                            <div class="stat-label">Total Users</div>
                            <div class="stat-change positive">+<?php echo $userAnalytics['week_registrations'] ?? 0; ?> this week</div>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-icon"><i class="fas fa-dumbbell"></i></div>
                            <div class="stat-number"><?php echo number_format($workoutAnalytics['total_workouts'] ?? 0); ?></div>
                            <div class="stat-label">Total Workouts</div>
                            <div class="stat-change positive">+<?php echo $workoutAnalytics['week_workouts'] ?? 0; ?> this week</div>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-icon"><i class="fas fa-fire"></i></div>
                            <div class="stat-number"><?php echo number_format($workoutAnalytics['total_calories'] ?? 0); ?></div>
                            <div class="stat-label">Total Calories</div>
                            <div class="stat-change">Burned by users</div>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-icon"><i class="fas fa-bullseye"></i></div>
                            <div class="stat-number"><?php echo number_format($goalsAnalytics['achievement_rate'] ?? 0, 1); ?>%</div>
                            <div class="stat-label">Goal Achievement Rate</div>
                            <div class="stat-change"><?php echo $goalsAnalytics['achieved_goals'] ?? 0; ?> goals achieved</div>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-icon"><i class="fas fa-mobile-alt"></i></div>
                            <div class="stat-number"><?php echo $userAnalytics['today_active'] ?? 0; ?></div>
                            <div class="stat-label">Active Today</div>
                            <div class="stat-change"><?php echo $userAnalytics['week_active'] ?? 0; ?> active this week</div>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-icon"><i class="fas fa-chart-line"></i></div>
                            <div class="stat-number"><?php echo $userAnalytics['month_registrations'] ?? 0; ?></div>
                            <div class="stat-label">Monthly Growth</div>
                            <div class="stat-change">New registrations</div>
                        </div>
                    </div>

                    <!-- Workout Types Chart -->
                    <div class="chart-section">
                        <div class="section-title">
                            <i class="fas fa-chart-pie"></i>
                            Workout Type Distribution
                        </div>
                        <div class="chart-container">
                            <canvas id="workoutTypesChart"></canvas>
                        </div>
                    </div>

                    <!-- Daily Activity Trends -->
                    <div class="chart-section">
                        <div class="section-title">
                            <i class="fas fa-chart-line"></i>
                            Daily Activity Trends (Last 30 Days)
                        </div>
                        <div class="chart-container">
                            <canvas id="dailyTrendsChart"></canvas>
                        </div>
                    </div>

                <?php elseif ($viewType === 'users'): ?>
                    <!-- User Analytics -->
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-icon"><i class="fas fa-user"></i></div>
                            <div class="stat-number"><?php echo $userAnalytics['regular_users'] ?? 0; ?></div>
                            <div class="stat-label">Regular Users</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
                            <div class="stat-number"><?php echo $userAnalytics['active_users'] ?? 0; ?></div>
                            <div class="stat-label">Active Users</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon"><i class="fas fa-calendar-day"></i></div>
                            <div class="stat-number"><?php echo $userAnalytics['today_registrations'] ?? 0; ?></div>
                            <div class="stat-label">Registered Today</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon"><i class="fas fa-chart-bar"></i></div>
                            <div class="stat-number"><?php echo $userAnalytics['month_registrations'] ?? 0; ?></div>
                            <div class="stat-label">This Month</div>
                        </div>
                    </div>

                    <!-- User Engagement Table -->
                    <div class="table-section">
                        <div class="section-title">
                            <i class="fas fa-users"></i>
                            User Engagement Overview
                        </div>
                        <div class="table-responsive">
                            <table class="analytics-table" id="userEngagementTable" data-filter>
                                <thead>
                                    <tr>
                                        <th>Username</th>
                                        <th>Role</th>
                                        <th>Workouts</th>
                                        <th>Goals</th>
                                        <th>Badges</th>
                                        <th>Last Login</th>
                                        <th>Engagement</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach (array_slice($userEngagement, 0, 20) as $user): ?>
                                        <tr>
                                            <td>
                                                <div style="display: flex; align-items: center; gap: 0.5rem;">
                                                    <div class="user-avatar <?php echo $user['role']; ?>" style="width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 600; font-size: 0.875rem;">
                                                        <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                                                    </div>
                                                    <?php echo htmlspecialchars($user['username']); ?>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="role-badge role-<?php echo $user['role']; ?>" style="padding: 0.25rem 0.5rem; border-radius: 0.375rem; font-size: 0.75rem; font-weight: 500; text-transform: uppercase;">
                                                    <?php echo ucfirst($user['role']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo $user['total_workouts']; ?></td>
                                            <td><?php echo $user['total_goals']; ?></td>
                                            <td><?php echo $user['total_badges']; ?></td>
                                            <td>
                                                <?php 
                                                if ($user['last_login']) {
                                                    echo date('M j, Y', strtotime($user['last_login']));
                                                } else {
                                                    echo 'Never';
                                                }
                                                ?>
                                            </td>
                                            <td>
                                                <?php 
                                                $engagement = $user['total_workouts'] + $user['total_goals'];
                                                if ($engagement >= 10) {
                                                    echo '<span class="metric-badge badge-high">High</span>';
                                                } elseif ($engagement >= 3) {
                                                    echo '<span class="metric-badge badge-medium">Medium</span>';
                                                } else {
                                                    echo '<span class="metric-badge badge-low">Low</span>';
                                                }
                                                ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                <?php elseif ($viewType === 'workouts'): ?>
                    <!-- Workout Analytics -->
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-icon"><i class="fas fa-clock"></i></div>
                            <div class="stat-number"><?php echo number_format($workoutAnalytics['avg_duration'] ?? 0, 1); ?></div>
                            <div class="stat-label">Avg Duration (min)</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon"><i class="fas fa-route"></i></div>
                            <div class="stat-number"><?php echo number_format($workoutAnalytics['avg_distance'] ?? 0, 1); ?></div>
                            <div class="stat-label">Avg Distance (km)</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon"><i class="fas fa-chart-line"></i></div>
                            <div class="stat-number"><?php echo $workoutAnalytics['today_workouts'] ?? 0; ?></div>
                            <div class="stat-label">Today's Workouts</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon"><i class="fas fa-users"></i></div>
                            <div class="stat-number"><?php echo $workoutAnalytics['active_users'] ?? 0; ?></div>
                            <div class="stat-label">Active Users</div>
                        </div>
                    </div>

                    <!-- Workout Types Table -->
                    <div class="table-section">
                        <div class="section-title">
                            <i class="fas fa-dumbbell"></i>
                            Workout Types Breakdown
                        </div>
                        <div class="table-responsive">
                            <table class="analytics-table" id="workoutTypesTable" data-filter>
                                <thead>
                                    <tr>
                                        <th>Workout Type</th>
                                        <th>Count</th>
                                        <th>Percentage</th>
                                        <th>Avg Duration</th>
                                        <th>Avg Distance</th>
                                        <th>Avg Calories</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($workoutTypes as $type): ?>
                                        <tr>
                                            <td>
                                                <div style="display: flex; align-items: center; gap: 0.5rem;">
                                                    <i class="fas fa-dumbbell" style="color: var(--primary);"></i>
                                                    <?php echo htmlspecialchars($type['type']); ?>
                                                </div>
                                            </td>
                                            <td><?php echo number_format($type['count']); ?></td>
                                            <td>
                                                <div style="display: flex; align-items: center; gap: 0.5rem;">
                                                    <div style="background: var(--primary); height: 8px; border-radius: 4px; width: <?php echo $type['percentage'] * 2; ?>px;"></div>
                                                    <?php echo $type['percentage']; ?>%
                                                </div>
                                            </td>
                                            <td><?php echo number_format($type['avg_duration'], 1); ?> min</td>
                                            <td><?php echo number_format($type['avg_distance'], 1); ?> km</td>
                                            <td><?php echo number_format($type['avg_calories'], 0); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                <?php elseif ($viewType === 'engagement'): ?>
                    <!-- Top Performers -->
                    <div class="table-section">
                        <div class="section-title">
                            <i class="fas fa-trophy"></i>
                            Top Performers
                        </div>
                        <div class="table-responsive">
                            <table class="analytics-table" id="topPerformersTable" data-filter>
                                <thead>
                                    <tr>
                                        <th>Rank</th>
                                        <th>Username</th>
                                        <th>Workouts</th>
                                        <th>Total Duration</th>
                                        <th>Total Distance</th>
                                        <th>Total Calories</th>
                                        <th>Goals Achieved</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($topPerformers as $index => $performer): ?>
                                        <tr>
                                            <td>
                                                <div style="display: flex; align-items: center; gap: 0.5rem; font-weight: 600;">
                                                    <?php 
                                                    $rank = $index + 1;
                                                    if ($rank <= 3) {
                                                        $icons = ['🥇', '🥈', '🥉'];
                                                        echo '<span style="font-size: 1.25rem;">' . $icons[$rank - 1] . '</span>';
                                                    }
                                                    echo $rank;
                                                    ?>
                                                </div>
                                            </td>
                                            <td>
                                                <div style="display: flex; align-items: center; gap: 0.5rem;">
                                                    <div class="user-avatar" style="width: 32px; height: 32px; border-radius: 50%; background: var(--primary); display: flex; align-items: center; justify-content: center; color: white; font-weight: 600; font-size: 0.875rem;">
                                                        <?php echo strtoupper(substr($performer['username'], 0, 1)); ?>
                                                    </div>
                                                    <?php echo htmlspecialchars($performer['username']); ?>
                                                </div>
                                            </td>
                                            <td>
                                                <span style="color: var(--primary); font-weight: 600;">
                                                    <?php echo $performer['workout_count']; ?>
                                                </span>
                                            </td>
                                            <td><?php echo number_format($performer['total_duration'], 1); ?> min</td>
                                            <td><?php echo number_format($performer['total_distance'], 1); ?> km</td>
                                            <td><?php echo number_format($performer['total_calories'], 0); ?></td>
                                            <td>
                                                <span style="color: var(--success); font-weight: 600;">
                                                    <?php echo $performer['goals_achieved']; ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Export Section -->
                <div class="export-section">
                    <h3 style="margin-bottom: 1.5rem; color: var(--text-primary); display: flex; align-items: center; justify-content: center; gap: 0.5rem;">
                        <i class="fas fa-download"></i>
                        Export Analytics Data
                    </h3>
                    <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                        <a href="../api/export.php?type=analytics&format=json" class="btn-export">
                            <i class="fas fa-file-code"></i>
                            Export JSON
                        </a>
                        <a href="../api/export.php?type=analytics&format=csv" class="btn-export">
                            <i class="fas fa-file-csv"></i>
                            Export CSV
                        </a>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="../assets/js/darkmode.js"></script>

    <script>
        // Workout Types Chart
        <?php if (!empty($workoutTypes)): ?>
        const workoutTypesCtx = document.getElementById('workoutTypesChart');
        if (workoutTypesCtx) {
            new Chart(workoutTypesCtx, {
                type: 'doughnut',
                data: {
                    labels: <?php echo json_encode(array_column($workoutTypes, 'type')); ?>,
                    datasets: [{
                        data: <?php echo json_encode(array_column($workoutTypes, 'count')); ?>,
                        backgroundColor: [
                            '#10b981', '#3b82f6', '#f59e0b', '#ef4444', '#8b5cf6',
                            '#06b6d4', '#84cc16', '#f97316', '#ec4899', '#6b7280'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right'
                        }
                    }
                }
            });
        }
        <?php endif; ?>

        // Daily Trends Chart
        <?php if (!empty($dailyTrends)): ?>
        const dailyTrendsCtx = document.getElementById('dailyTrendsChart');
        if (dailyTrendsCtx) {
            new Chart(dailyTrendsCtx, {
                type: 'line',
                data: {
                    labels: <?php echo json_encode(array_reverse(array_column($dailyTrends, 'activity_date'))); ?>,
                    datasets: [{
                        label: 'Workouts',
                        data: <?php echo json_encode(array_reverse(array_column($dailyTrends, 'workout_count'))); ?>,
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        tension: 0.4
                    }, {
                        label: 'Active Users',
                        data: <?php echo json_encode(array_reverse(array_column($dailyTrends, 'active_users'))); ?>,
                        borderColor: '#3b82f6',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }
        <?php endif; ?>
    </script>
    
    <script src="../assets/js/table-filters.js"></script>
</body>
</html>
