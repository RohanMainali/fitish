<?php
// goals.php
require_once 'includes/auth.class.php';
require_once 'includes/goal.class.php';
require_once 'includes/csrf.class.php';

$auth = new Auth();
$auth->requireLogin();
$user = $auth->currentUser();
$goalObj = new Goal();
$message = '';
$csrf_token = CSRF::generateToken();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!CSRF::validateToken($_POST['csrf_token'] ?? '')) {
        $message = 'Invalid CSRF token.';
    } else {
        // Handle goal deletion
        if (isset($_POST['delete_goal']) && isset($_POST['goal_id'])) {
            $goal_id = intval($_POST['goal_id']);
            $goal = $goalObj->getById($goal_id);
            
            // Verify the goal belongs to the current user
            if ($goal && $goal['user_id'] == $user['id']) {
                if ($goalObj->delete($goal_id)) {
                    $message = 'Goal deleted successfully!';
                } else {
                    $message = 'Failed to delete goal.';
                }
            } else {
                $message = 'Goal not found or access denied.';
            }
        }
        // Handle goal creation
        elseif (isset($_POST['goal_type'])) {
            $goal_type = trim($_POST['goal_type'] ?? '');
            $target_value = floatval($_POST['target_value'] ?? 0);
            $current_value = floatval($_POST['current_value'] ?? 0);
            $deadline = $_POST['deadline'] ?? '';
            
            if ($goalObj->create($user['id'], $goal_type, $target_value, $current_value, $deadline)) {
                header('Location: dashboard.php');
                exit;
            } else {
                $message = 'Failed to add goal.';
            }
        }
    }
}

$goals = $goalObj->getByUser($user['id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Goals - Fitish Pro</title>
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
                    <a href="goals.php" class="nav-item active">
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
            <div class="top-bar">
                <div class="page-title">
                    <h1>Fitness Goals</h1>
                    <p>Set, track, and achieve your fitness objectives</p>
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

                <!-- Create New Goal Card -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-plus-circle"></i>
                            Create New Goal
                        </h3>
                    </div>
                    <div class="card-content">
                        <form method="post" action="" id="addGoalForm">
                            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-bullseye"></i>
                                        Goal Type
                                    </label>
                                    <div class="select-wrapper">
                                        <select name="goal_type" class="form-input" required>
                                            <option value="">Select your goal type</option>
                                            <option value="Weight Loss">
                                                <i class="fas fa-weight"></i> Weight Loss
                                            </option>
                                            <option value="Weight Gain">Weight Gain</option>
                                            <option value="Distance">Distance Target</option>
                                            <option value="Calories Burned">Calories Burned</option>
                                            <option value="Workout Streak">Workout Streak</option>
                                            <option value="Strength">Strength Goal</option>
                                            <option value="Other">Custom Goal</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-target"></i>
                                        Target Value
                                    </label>
                                    <input type="number" step="0.01" name="target_value" class="form-input" placeholder="e.g., 75" required>
                                    <small class="form-help">What value do you want to achieve?</small>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-play-circle"></i>
                                        Current Value
                                    </label>
                                    <input type="number" step="0.01" name="current_value" class="form-input" placeholder="e.g., 80" required>
                                    <small class="form-help">Your starting point or current progress</small>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-calendar-alt"></i>
                                        Target Date
                                    </label>
                                    <input type="date" name="deadline" class="form-input" min="<?php echo date('Y-m-d'); ?>" required>
                                    <small class="form-help">When do you want to achieve this goal?</small>
                                </div>
                            </div>

                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-rocket"></i>
                                    Create Goal
                                </button>
                                <a href="dashboard.php" class="btn btn-outline">
                                    <i class="fas fa-arrow-left"></i>
                                    Back to Dashboard
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Your Goals -->
                <?php if (!empty($goals)): ?>
                <div class="card card-spacing">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-chart-line"></i>
                            Your Active Goals
                        </h3>
                        <div class="card-actions">
                            <span class="goals-count"><?php echo count($goals); ?> Goal<?php echo count($goals) === 1 ? '' : 's'; ?></span>
                        </div>
                    </div>
                    <div class="card-content">
                        <div class="goals-grid">
                            <?php foreach ($goals as $goal): ?>
                                <?php 
                                $progress = $goal['target_value'] > 0 ? ($goal['current_value'] / $goal['target_value']) * 100 : 0;
                                $isCompleted = $goal['is_achieved'] || $progress >= 100;
                                $isOverdue = !$isCompleted && strtotime($goal['deadline']) < time();
                                $daysLeft = max(0, floor((strtotime($goal['deadline']) - time()) / (60 * 60 * 24)));
                                ?>
                                <div class="goal-card <?php echo $isCompleted ? 'completed' : ($isOverdue ? 'overdue' : 'active'); ?>">
                                    <div class="goal-header">
                                        <div class="goal-type">
                                            <div class="goal-icon">
                                                <?php 
                                                $iconMap = [
                                                    'Weight Loss' => 'fas fa-weight',
                                                    'Weight Gain' => 'fas fa-dumbbell',
                                                    'Distance' => 'fas fa-route',
                                                    'Calories Burned' => 'fas fa-fire',
                                                    'Workout Streak' => 'fas fa-calendar-check',
                                                    'Strength' => 'fas fa-fist-raised',
                                                    'Other' => 'fas fa-star'
                                                ];
                                                $icon = $iconMap[$goal['goal_type']] ?? 'fas fa-bullseye';
                                                ?>
                                                <i class="<?php echo $icon; ?>"></i>
                                            </div>
                                            <div class="goal-info">
                                                <h4 class="goal-title"><?php echo htmlspecialchars($goal['goal_type']); ?></h4>
                                                <p class="goal-deadline">
                                                    <?php if ($isOverdue): ?>
                                                        <i class="fas fa-exclamation-triangle"></i>
                                                        Overdue by <?php echo abs($daysLeft - floor((strtotime($goal['deadline']) - time()) / (60 * 60 * 24))); ?> days
                                                    <?php elseif ($isCompleted): ?>
                                                        <i class="fas fa-check-circle"></i>
                                                        Completed!
                                                    <?php else: ?>
                                                        <i class="fas fa-clock"></i>
                                                        <?php echo $daysLeft; ?> days left
                                                    <?php endif; ?>
                                                </p>
                                            </div>
                                        </div>
                                        
                                        <div class="goal-status">
                                            <?php if ($isCompleted): ?>
                                                <span class="status-badge completed">
                                                    <i class="fas fa-trophy"></i>
                                                    Achieved
                                                </span>
                                            <?php elseif ($isOverdue): ?>
                                                <span class="status-badge overdue">
                                                    <i class="fas fa-exclamation-triangle"></i>
                                                    Overdue
                                                </span>
                                            <?php else: ?>
                                                <span class="status-badge active">
                                                    <i class="fas fa-play-circle"></i>
                                                    In Progress
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
                                        
                                        <?php if (!$isCompleted): ?>
                                        <div class="progress-insights">
                                            <?php 
                                            $remaining = $goal['target_value'] - $goal['current_value'];
                                            $dailyRate = $daysLeft > 0 ? $remaining / $daysLeft : $remaining;
                                            ?>
                                            <div class="insight-item">
                                                <i class="fas fa-bullseye"></i>
                                                <span><?php echo number_format(abs($remaining), 1); ?> to go</span>
                                            </div>
                                            <?php if ($daysLeft > 0 && $remaining > 0): ?>
                                            <div class="insight-item">
                                                <i class="fas fa-calendar-day"></i>
                                                <span><?php echo number_format($dailyRate, 1); ?> per day needed</span>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                        <?php endif; ?>
                                    </div>

                                    <div class="goal-actions">
                                        <button class="btn btn-outline btn-sm" onclick="editGoal(<?php echo $goal['id']; ?>)">
                                            <i class="fas fa-edit"></i>
                                            Update Progress
                                        </button>
                                        <button class="btn btn-danger btn-sm" onclick="deleteGoal(<?php echo $goal['id']; ?>)">
                                            <i class="fas fa-trash"></i>
                                            Delete
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php else: ?>
                <div class="card card-spacing">
                    <div class="card-content">
                        <div class="empty-state">
                            <div class="empty-icon">
                                <i class="fas fa-bullseye"></i>
                            </div>
                            <h3>No Goals Set Yet</h3>
                            <p>Ready to start your fitness journey? Create your first goal and begin tracking your progress!</p>
                            <button class="btn btn-primary" onclick="document.getElementById('addGoalForm').scrollIntoView()">
                                <i class="fas fa-plus-circle"></i>
                                Create Your First Goal
                            </button>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Goals Overview Card -->
                <?php if (!empty($goals)): ?>
                <div class="card card-spacing">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-chart-pie"></i>
                            Goals Overview
                        </h3>
                    </div>
                    <div class="card-content">
                        <div class="goals-overview">
                            <?php 
                            $completed = array_filter($goals, function($g) { return $g['is_achieved'] || ($g['target_value'] > 0 && ($g['current_value'] / $g['target_value']) >= 1); });
                            $overdue = array_filter($goals, function($g) { return !$g['is_achieved'] && strtotime($g['deadline']) < time(); });
                            $active = array_filter($goals, function($g) { return !$g['is_achieved'] && strtotime($g['deadline']) >= time(); });
                            ?>
                            <div class="overview-stat">
                                <div class="stat-icon completed">
                                    <i class="fas fa-trophy"></i>
                                </div>
                                <div class="stat-content">
                                    <h4 class="stat-number"><?php echo count($completed); ?></h4>
                                    <p class="stat-label">Completed</p>
                                </div>
                            </div>
                            
                            <div class="overview-stat">
                                <div class="stat-icon active">
                                    <i class="fas fa-play-circle"></i>
                                </div>
                                <div class="stat-content">
                                    <h4 class="stat-number"><?php echo count($active); ?></h4>
                                    <p class="stat-label">Active</p>
                                </div>
                            </div>
                            
                            <div class="overview-stat">
                                <div class="stat-icon overdue">
                                    <i class="fas fa-exclamation-triangle"></i>
                                </div>
                                <div class="stat-content">
                                    <h4 class="stat-number"><?php echo count($overdue); ?></h4>
                                    <p class="stat-label">Overdue</p>
                                </div>
                            </div>
                            
                            <div class="overview-stat">
                                <div class="stat-icon total">
                                    <i class="fas fa-bullseye"></i>
                                </div>
                                <div class="stat-content">
                                    <h4 class="stat-number"><?php echo count($goals); ?></h4>
                                    <p class="stat-label">Total Goals</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <!-- Scripts -->
    <script src="assets/js/darkmode.js"></script>
    <script>
    // Modern Goal Management Functions
    function editGoal(goalId) {
        // For now, show an alert - you can implement a modal or inline editing
        const currentValue = prompt('Enter new current value:');
        if (currentValue !== null && currentValue !== '') {
            // Create and submit form
            const form = document.createElement('form');
            form.method = 'post';
            form.action = '';
            
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = 'csrf_token';
            csrfInput.value = '<?php echo $csrf_token; ?>';
            
            const goalIdInput = document.createElement('input');
            goalIdInput.type = 'hidden';
            goalIdInput.name = 'goal_id';
            goalIdInput.value = goalId;
            
            const currentValueInput = document.createElement('input');
            currentValueInput.type = 'hidden';
            currentValueInput.name = 'update_current_value';
            currentValueInput.value = currentValue;
            
            const updateInput = document.createElement('input');
            updateInput.type = 'hidden';
            updateInput.name = 'update_goal';
            updateInput.value = '1';
            
            form.appendChild(csrfInput);
            form.appendChild(goalIdInput);
            form.appendChild(currentValueInput);
            form.appendChild(updateInput);
            
            document.body.appendChild(form);
            form.submit();
        }
    }
    
    function deleteGoal(goalId) {
        if (confirm('Are you sure you want to delete this goal? This action cannot be undone.')) {
            const form = document.createElement('form');
            form.method = 'post';
            form.action = '';
            
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = 'csrf_token';
            csrfInput.value = '<?php echo $csrf_token; ?>';
            
            const goalIdInput = document.createElement('input');
            goalIdInput.type = 'hidden';
            goalIdInput.name = 'goal_id';
            goalIdInput.value = goalId;
            
            const deleteInput = document.createElement('input');
            deleteInput.type = 'hidden';
            deleteInput.name = 'delete_goal';
            deleteInput.value = '1';
            
            form.appendChild(csrfInput);
            form.appendChild(goalIdInput);
            form.appendChild(deleteInput);
            
            document.body.appendChild(form);
            form.submit();
        }
    }

    // Animate progress bars on page load
    document.addEventListener('DOMContentLoaded', function() {
        const progressBars = document.querySelectorAll('.progress-bar-fill');
        
        // Add entrance animation
        setTimeout(() => {
            progressBars.forEach((bar, index) => {
                setTimeout(() => {
                    const width = bar.style.width;
                    bar.style.width = '0%';
                    bar.style.transition = 'width 1s ease-out';
                    
                    setTimeout(() => {
                        bar.style.width = width;
                    }, 50);
                }, index * 200);
            });
        }, 300);

        // Add form validation
        const form = document.getElementById('addGoalForm');
        if (form) {
            form.addEventListener('submit', function(e) {
                const targetValue = parseFloat(document.querySelector('input[name="target_value"]').value);
                const currentValue = parseFloat(document.querySelector('input[name="current_value"]').value);
                const deadline = new Date(document.querySelector('input[name="deadline"]').value);
                const today = new Date();
                
                if (deadline <= today) {
                    e.preventDefault();
                    alert('Please select a future date for your goal deadline.');
                    return false;
                }
                
                if (targetValue <= 0) {
                    e.preventDefault();
                    alert('Target value must be greater than 0.');
                    return false;
                }
            });
        }
    });
    </script>
</body>
</html>
