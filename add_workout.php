<?php
// add_workout.php
require_once 'includes/workout.class.php';
require_once 'includes/auth.class.php';
require_once 'includes/metrics.class.php';
require_once 'includes/streak.class.php';
require_once 'includes/csrf.class.php';

$auth = new Auth();
$auth->requireLogin();
$auth = new Auth();
$user = $auth->currentUser();
$message = '';
$csrf_token = CSRF::generateToken();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!CSRF::validateToken($_POST['csrf_token'] ?? '')) {
        $message = 'Invalid CSRF token.';
    } else {
        $type = trim($_POST['type'] ?? '');
        $duration = intval($_POST['duration'] ?? 0);
        $distance = floatval($_POST['distance'] ?? 0);
        $met = floatval($_POST['met'] ?? 1);
        $date = $_POST['date'] ?? date('Y-m-d');
        $weight = floatval($_POST['weight'] ?? 0);
        
        // Calculate calories
        $calories = Metrics::calculateCalories($met, $weight, $duration);
        
        $workoutObj = new Workout();
        if ($workoutObj->create($user['id'], $type, $duration, $distance, $calories, $met, $date)) {
            // Update all 'Streak' goals for this user to match their current streak
            require_once 'includes/streak.class.php';
            require_once 'includes/goal.class.php';
            $streakObj = new Streak();
            $goalObj = new Goal();
            $currentStreak = $streakObj->getCurrentStreak($user['id']);
            $streakGoals = $goalObj->getFilteredByUser($user['id'], ['goal_type' => 'Streak']);
            foreach ($streakGoals as $goal) {
                $goalObj->update($goal['id'], ['current_value' => $currentStreak]);
            }
            header('Location: dashboard.php');
            exit;
        } else {
            $message = 'Failed to add workout.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Workout - Fitish Pro</title>
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
                    <a href="add_workout.php" class="nav-item active">
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
                        <strong><?php echo htmlspecialchars($user['username']); ?></strong>
                        <small><?php echo htmlspecialchars($user['email']); ?></small>
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
                    <h1>Add New Workout</h1>
                    <p>Log your exercise session and track your progress</p>
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
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>

                <!-- Workout Form -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-dumbbell"></i>
                            Workout Details
                        </h3>
                    </div>
                    <div class="card-content">
                        <form method="post" action="" id="addWorkoutForm">
                            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-running"></i>
                                        Exercise Type
                                    </label>
                                    <div class="select-wrapper">
                                        <select name="type" class="form-input" required>
                                            <option value="">Select exercise type</option>
                                            <option value="Running">🏃 Running</option>
                                            <option value="Cycling">🚴 Cycling</option>
                                            <option value="Swimming">🏊 Swimming</option>
                                            <option value="Walking">🚶 Walking</option>
                                            <option value="Yoga">🧘 Yoga</option>
                                            <option value="Strength Training">💪 Strength Training</option>
                                            <option value="Other">🏋️ Other</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-clock"></i>
                                        Duration (minutes)
                                    </label>
                                    <input type="number" name="duration" class="form-input" placeholder="e.g., 30" required>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-route"></i>
                                        Distance (km) - Optional
                                    </label>
                                    <input type="number" step="0.01" name="distance" class="form-input" placeholder="e.g., 5.2">
                                </div>

                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-bolt"></i>
                                        MET Value
                                    </label>
                                    <input type="number" step="0.01" name="met" value="1" class="form-input" required>
                                    <small class="form-help">Metabolic Equivalent of Task (auto-filled based on exercise)</small>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-calendar"></i>
                                        Date
                                    </label>
                                    <input type="date" name="date" value="<?php echo date('Y-m-d'); ?>" class="form-input" required>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-weight"></i>
                                        Your Weight (kg)
                                    </label>
                                    <input type="number" step="0.1" name="weight" class="form-input" placeholder="e.g., 70.5" required>
                                    <small class="form-help">Used for calorie calculation</small>
                                </div>
                            </div>

                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i>
                                    Save Workout
                                </button>
                                <a href="dashboard.php" class="btn btn-outline">
                                    <i class="fas fa-times"></i>
                                    Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Quick Tips -->
                <div class="card card-spacing">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-lightbulb"></i>
                            Quick Tips
                        </h3>
                    </div>
                    <div class="card-content">
                        <div class="tips-grid">
                            <div class="tip-item">
                                <div class="tip-icon">🏃</div>
                                <div class="tip-content">
                                    <strong>Running</strong>
                                    <span>MET value ~9.8</span>
                                    <small>High intensity cardio exercise</small>
                                </div>
                            </div>
                            <div class="tip-item">
                                <div class="tip-icon">🚴</div>
                                <div class="tip-content">
                                    <strong>Cycling</strong>
                                    <span>MET value ~7.5</span>
                                    <small>Moderate intensity exercise</small>
                                </div>
                            </div>
                            <div class="tip-item">
                                <div class="tip-icon">🏊</div>
                                <div class="tip-content">
                                    <strong>Swimming</strong>
                                    <span>MET value ~8.0</span>
                                    <small>Full body workout</small>
                                </div>
                            </div>
                            <div class="tip-item">
                                <div class="tip-icon">🧘</div>
                                <div class="tip-content">
                                    <strong>Yoga</strong>
                                    <span>MET value ~2.5</span>
                                    <small>Low intensity, flexibility focused</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="assets/js/workout-form.js"></script>
    <script src="assets/js/ajax.js"></script>
    <script>ajaxForm('#addWorkoutForm');</script>
    <script src="assets/js/darkmode.js"></script>
</body>
</html>
