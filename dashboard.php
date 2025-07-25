<?php
// dashboard.php
require_once 'includes/auth.class.php';
require_once 'includes/workout.class.php';
require_once 'includes/stats.class.php';
require_once 'includes/goal.class.php';
require_once 'includes/metrics.class.php';
require_once 'includes/badge.class.php';
require_once 'includes/streak.class.php';
require_once 'includes/analytics.class.php';
require_once 'includes/fitnesslevel.class.php';

$auth = new Auth();
$auth->requireLogin();
$user = $auth->currentUser();
$workoutObj = new Workout();
$statsObj = new Stats();
$goalObj = new Goal();
$badgeObj = new Badge();
$streakObj = new Streak();
$analyticsObj = new Analytics();

// Get user data
$stats = $statsObj->getLatest($user['id']);
$workouts = $workoutObj->getByUser($user['id']);
$goals = $goalObj->getByUser($user['id']);
$badges = $badgeObj->getByUser($user['id']);
$currentStreak = $streakObj->getCurrentStreak($user['id']);
$longestStreak = $streakObj->getLongestStreak($user['id']);

// Calculate metrics
$totalWorkouts = count($workouts);
$totalDuration = array_sum(array_column($workouts, 'duration'));
$totalCalories = array_sum(array_column($workouts, 'calories'));
$avgDuration = $totalWorkouts > 0 ? round($totalDuration / $totalWorkouts) : 0;

// Get last workout
$lastWorkout = !empty($workouts) ? $workouts[0] : null;

// Calculate BMI, BMR, TDEE if stats available
$bmi = $stats ? $stats['bmi'] : null;
$bmr = $stats ? $stats['bmr'] : null;
$tdee = $stats ? $stats['tdee'] : null;
$currentWeight = $stats ? $stats['weight'] : null;

$fitnessLevel = FitnessLevel::calculate($bmi, $totalCalories, $currentStreak);

// Get favorite exercise type
$exerciseTypes = array_count_values(array_column($workouts, 'type'));
$favoriteExercise = !empty($exerciseTypes) ? array_keys($exerciseTypes, max($exerciseTypes))[0] : 'None';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Fitish Pro</title>
    <link rel="stylesheet" href="assets/css/moderns.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="dashboard-layout">
        <!-- Modern Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <div class="logo">
                    <i class="fas fa-dumbbell logo-icon"></i>
                    <span class="logo-text">Fitish Pro</span>
                </div>
            </div>
            
            <nav class="sidebar-nav">
                <div class="nav-section">
                    <h3 class="nav-section-title">
                        <i class="fas fa-compass"></i>
                        Navigation
                    </h3>
                    <a href="dashboard.php" class="nav-item active">
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
                    <a href="profile.php" class="nav-item">
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
            <!-- Top Bar -->
            <div class="top-bar">
                <div class="page-title">
                    <h1>Dashboard</h1>
                    <p>Your comprehensive fitness overview</p>
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
                            <h2>Welcome back, <?php echo htmlspecialchars($user['username']); ?>! 👋</h2>
                            <p>Ready to continue your fitness journey? Here's your daily overview.</p>
                            <?php if ($lastWorkout): ?>
                                <div class="last-activity">
                                    <i class="fas fa-clock"></i>
                                    Last workout: <strong><?php echo htmlspecialchars($lastWorkout['type']); ?></strong> on <?php echo date('M j, Y', strtotime($lastWorkout['date'])); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="welcome-actions">
                            <a href="add_workout.php" class="btn btn-primary">
                                <i class="fas fa-plus"></i>
                                Log Workout
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Stats Grid -->
                <div class="stats-section">
                    <h3 class="section-title">
                        <i class="fas fa-chart-bar"></i>
                        Your Stats
                    </h3>
                    
                    <div class="stats-grid">
                        <div class="stat-card primary">
                            <div class="stat-icon">
                                <i class="fas fa-dumbbell"></i>
                            </div>
                            <div class="stat-content">
                                <h3 class="stat-value"><?php echo $totalWorkouts; ?></h3>
                                <p class="stat-label">Total Workouts</p>
                                <span class="stat-change positive">
                                    <i class="fas fa-arrow-up"></i>
                                    1 this week
                                </span>
                            </div>
                        </div>

                        <div class="stat-card success">
                            <div class="stat-icon">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div class="stat-content">
                                <h3 class="stat-value"><?php echo $totalDuration; ?><span class="stat-unit">min</span></h3>
                                <p class="stat-label">Total Duration</p>
                                <span class="stat-change neutral">
                                    <?php echo round($totalDuration / 60, 1); ?> hours total
                                </span>
                            </div>
                        </div>

                        <div class="stat-card warning">
                            <div class="stat-icon">
                                <i class="fas fa-weight"></i>
                            </div>
                            <div class="stat-content">
                                <h3 class="stat-value"><?php echo $currentWeight ? $currentWeight : '--'; ?><span class="stat-unit"><?php echo $currentWeight ? 'kg' : ''; ?></span></h3>
                                <p class="stat-label">Current Weight</p>
                                <span class="stat-change neutral">
                                    <?php echo $currentWeight ? 'Stable' : 'Not set'; ?>
                                </span>
                            </div>
                        </div>

                        <div class="stat-card danger">
                            <div class="stat-icon">
                                <i class="fas fa-fire"></i>
                            </div>
                            <div class="stat-content">
                                <h3 class="stat-value"><?php echo $totalCalories; ?></h3>
                                <p class="stat-label">Calories Burned</p>
                                <span class="stat-change positive">
                                    <i class="fas fa-arrow-up"></i>
                                    This week
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Insights Grid -->
                <div class="insights-section">
                    <h3 class="section-title">
                        <i class="fas fa-lightbulb"></i>
                        Quick Insights
                    </h3>
                    
                    <div class="insights-grid">
                        <div class="insight-card streak">
                            <div class="insight-icon">
                                <i class="fas fa-fire"></i>
                            </div>
                            <div class="insight-content">
                                <h4 class="insight-value"><?php echo $currentStreak; ?></h4>
                                <p class="insight-label">Day Streak</p>
                                <span class="insight-detail">Keep it going! 🔥</span>
                            </div>
                        </div>

                        <div class="insight-card favorite">
                            <div class="insight-icon">
                                <i class="fas fa-heart"></i>
                            </div>
                            <div class="insight-content">
                                <h4 class="insight-value"><?php echo htmlspecialchars($favoriteExercise); ?></h4>
                                <p class="insight-label">Favorite Exercise</p>
                                <span class="insight-detail">
                                    <?php echo $favoriteExercise !== 'None' ? (isset($exerciseTypes[$favoriteExercise]) ? $exerciseTypes[$favoriteExercise] : 0) . ' sessions' : 'No workouts yet'; ?>
                                </span>
                            </div>
                        </div>

                        <div class="insight-card average">
                            <div class="insight-icon">
                                <i class="fas fa-chart-line"></i>
                            </div>
                            <div class="insight-content">
                                <h4 class="insight-value"><?php echo $avgDuration; ?><span class="insight-unit">min</span></h4>
                                <p class="insight-label">Avg Duration</p>
                                <span class="insight-detail">Per workout</span>
                            </div>
                        </div>

                        <div class="insight-card fitness">
                            <div class="insight-icon">
                                <i class="fas fa-trophy"></i>
                            </div>
                            <div class="insight-content">
                                <h4 class="insight-value"><?php echo htmlspecialchars($fitnessLevel); ?></h4>
                                <p class="insight-label">Fitness Level</p>
                                <span class="insight-detail">Based on activity</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="actions-section">
                    <h3 class="section-title">
                        <i class="fas fa-bolt"></i>
                        Quick Actions
                    </h3>
                    
                    <div class="actions-grid">
                        <a href="add_workout.php" class="action-card primary">
                            <div class="action-icon">
                                <i class="fas fa-plus-circle"></i>
                            </div>
                            <div class="action-content">
                                <h4>Log New Workout</h4>
                                <p>Record your latest training session</p>
                            </div>
                            <div class="action-arrow">
                                <i class="fas fa-arrow-right"></i>
                            </div>
                        </a>

                        <a href="stats.php" class="action-card success">
                            <div class="action-icon">
                                <i class="fas fa-chart-bar"></i>
                            </div>
                            <div class="action-content">
                                <h4>Update Body Stats</h4>
                                <p>Track your physical measurements</p>
                            </div>
                            <div class="action-arrow">
                                <i class="fas fa-arrow-right"></i>
                            </div>
                        </a>

                        <a href="goals.php" class="action-card warning">
                            <div class="action-icon">
                                <i class="fas fa-bullseye"></i>
                            </div>
                            <div class="action-content">
                                <h4>Set New Goal</h4>
                                <p>Define your next fitness target</p>
                            </div>
                            <div class="action-arrow">
                                <i class="fas fa-arrow-right"></i>
                            </div>
                        </a>
                    </div>
                </div>

                <!-- Recent Workouts -->
                <?php if (!empty($workouts)): ?>
                <div class="workouts-section">
                    <div class="section-header">
                        <h3 class="section-title">
                            <i class="fas fa-history"></i>
                            Recent Workouts
                        </h3>
                        <a href="view_workouts.php" class="btn btn-outline">
                            <i class="fas fa-eye"></i>
                            View All
                        </a>
                    </div>
                    
                    <div class="workouts-grid">
                        <?php foreach (array_slice($workouts, 0, 4) as $workout): ?>
                            <div class="workout-card">
                                <div class="workout-header">
                                    <div class="workout-type">
                                        <i class="fas fa-<?php echo strtolower($workout['type']) === 'running' ? 'running' : (strtolower($workout['type']) === 'cycling' ? 'bicycle' : 'dumbbell'); ?>"></i>
                                        <?php echo htmlspecialchars($workout['type']); ?>
                                    </div>
                                    <div class="workout-date">
                                        <i class="fas fa-calendar"></i>
                                        <?php echo date('M j', strtotime($workout['date'])); ?>
                                    </div>
                                </div>
                                
                                <div class="workout-stats">
                                    <div class="workout-stat">
                                        <div class="stat-icon duration">
                                            <i class="fas fa-clock"></i>
                                        </div>
                                        <div class="stat-info">
                                            <span class="stat-value"><?php echo $workout['duration']; ?></span>
                                            <span class="stat-label">min</span>
                                        </div>
                                    </div>
                                    
                                    <div class="workout-stat">
                                        <div class="stat-icon calories">
                                            <i class="fas fa-fire"></i>
                                        </div>
                                        <div class="stat-info">
                                            <span class="stat-value"><?php echo $workout['calories']; ?></span>
                                            <span class="stat-label">cal</span>
                                        </div>
                                    </div>
                                    
                                    <?php if ($workout['distance']): ?>
                                    <div class="workout-stat">
                                        <div class="stat-icon distance">
                                            <i class="fas fa-route"></i>
                                        </div>
                                        <div class="stat-info">
                                            <span class="stat-value"><?php echo $workout['distance']; ?></span>
                                            <span class="stat-label">km</span>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php else: ?>
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="fas fa-dumbbell"></i>
                    </div>
                    <h3>No workouts yet</h3>
                    <p>Start your fitness journey by logging your first workout!</p>
                    <a href="add_workout.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i>
                        Log Your First Workout
                    </a>
                </div>
                <?php endif; ?>

                <!-- Goals Progress -->
                <?php if (!empty($goals)): ?>
                <div class="goals-section">
                    <div class="section-header">
                        <h3 class="section-title">
                            <i class="fas fa-bullseye"></i>
                            Goal Progress
                        </h3>
                        <a href="goals.php" class="btn btn-outline">
                            <i class="fas fa-cog"></i>
                            Manage Goals
                        </a>
                    </div>
                    
                    <div class="goals-grid">
                        <?php foreach (array_slice($goals, 0, 3) as $goal): ?>
                            <?php 
                            $progress = $goal['target_value'] > 0 ? ($goal['current_value'] / $goal['target_value']) * 100 : 0;
                            $isCompleted = $goal['is_achieved'];
                            $isOverdue = !$isCompleted && strtotime($goal['deadline']) < time();
                            ?>
                            <div class="goal-card <?php echo $isCompleted ? 'completed' : ($isOverdue ? 'overdue' : 'active'); ?>">
                                <div class="goal-header">
                                    <div class="goal-info">
                                        <h4 class="goal-title"><?php echo htmlspecialchars($goal['goal_type']); ?></h4>
                                        <p class="goal-deadline">
                                            <i class="fas fa-calendar-alt"></i>
                                            Due: <?php echo date('M j, Y', strtotime($goal['deadline'])); ?>
                                        </p>
                                    </div>
                                    <div class="goal-status">
                                        <?php if ($isCompleted): ?>
                                            <span class="status-badge completed">
                                                <i class="fas fa-check-circle"></i>
                                                Completed
                                            </span>
                                        <?php elseif ($isOverdue): ?>
                                            <span class="status-badge overdue">
                                                <i class="fas fa-exclamation-triangle"></i>
                                                Overdue
                                            </span>
                                        <?php else: ?>
                                            <span class="status-badge active">
                                                <i class="fas fa-play-circle"></i>
                                                Active
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <div class="goal-progress">
                                    <div class="progress-stats">
                                        <div class="current-value">
                                            <span class="value-number"><?php echo number_format($goal['current_value'], 1); ?></span>
                                            <span class="value-separator">/</span>
                                            <span class="target-value"><?php echo number_format($goal['target_value'], 1); ?></span>
                                        </div>
                                        <div class="progress-percentage">
                                            <?php echo round($progress); ?>%
                                        </div>
                                    </div>
                                    
                                    <div class="progress-bar-container">
                                        <div class="progress-bar-track">
                                            <div class="progress-bar-fill <?php echo $isCompleted ? 'completed' : ($isOverdue ? 'overdue' : 'active'); ?>" 
                                                 style="width: <?php echo min(100, max(0, $progress)); ?>%">
                                                <div class="progress-bar-shine"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script>
        // Theme toggle functionality
        document.getElementById('theme-toggle').addEventListener('click', function() {
            const html = document.documentElement;
            const icon = this.querySelector('i');
            
            if (html.classList.contains('dark-theme')) {
                html.classList.remove('dark-theme');
                icon.className = 'fas fa-moon';
                localStorage.setItem('theme', 'light');
            } else {
                html.classList.add('dark-theme');
                icon.className = 'fas fa-sun';
                localStorage.setItem('theme', 'dark');
            }
        });
        
        // Apply saved theme
        const savedTheme = localStorage.getItem('theme');
        if (savedTheme === 'dark') {
            document.documentElement.classList.add('dark-theme');
            document.getElementById('theme-toggle').querySelector('i').className = 'fas fa-sun';
        }
        
        // Add smooth transitions after page load
        window.addEventListener('load', function() {
            document.body.style.transition = 'all 0.3s ease';
        });
    </script>
</body>
</html>
