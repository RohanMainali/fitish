<?php
// view_workouts.php
require_once 'includes/auth.class.php';
require_once 'includes/workout.class.php';
$auth = new Auth();
$auth->requireLogin();
$user = $auth->currentUser();
$workoutObj = new Workout();
$message = '';
$message = '';
require_once 'includes/streak.class.php';
require_once 'includes/goal.class.php';
// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_workout'])) {
    $id = intval($_POST['workout_id']);
    $type = trim($_POST['type']);
    $duration = intval($_POST['duration']);
    $distance = isset($_POST['distance']) ? floatval($_POST['distance']) : null;
    $calories = floatval($_POST['calories']);
    $date = $_POST['date'];
    $data = [
        'type' => $type,
        'duration' => $duration,
        'distance' => $distance,
        'calories' => $calories,
        'date' => $date
    ];
    if ($workoutObj->update($id, $data)) {
        // Update streak goals after update
        $streakObj = new Streak();
        $goalObj = new Goal();
        $currentStreak = $streakObj->getCurrentStreak($user['id']);
        $streakGoals = $goalObj->getFilteredByUser($user['id'], ['goal_type' => 'Streak']);
        foreach ($streakGoals as $goal) {
            $goalObj->update($goal['id'], ['current_value' => $currentStreak]);
        }
        $message = 'Workout updated successfully!';
    } else {
        $message = 'Failed to update workout.';
    }
}
// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_workout'])) {
    $id = intval($_POST['workout_id']);
    if ($workoutObj->delete($id)) {
        // Update streak goals after delete
        $streakObj = new Streak();
        $goalObj = new Goal();
        $currentStreak = $streakObj->getCurrentStreak($user['id']);
        $streakGoals = $goalObj->getFilteredByUser($user['id'], ['goal_type' => 'Streak']);
        foreach ($streakGoals as $goal) {
            $updateData = ['current_value' => $currentStreak];
            // If no workouts left, set progress to 0 and mark as not achieved
            if ($currentStreak == 0) {
                $updateData['is_achieved'] = 0;
            }
            $goalObj->update($goal['id'], $updateData);
        }
        $message = 'Workout deleted successfully!';
    } else {
        $message = 'Failed to delete workout.';
    }
}
$workouts = $workoutObj->getByUser($user['id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Workouts - Fitish Pro</title>
    <link rel="stylesheet" href="assets/css/moderns.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .edit-workout-modal {
            background: var(--bg-card);
            border-radius: var(--radius-lg);
            padding: 1.5rem;
            box-shadow: var(--shadow-lg);
            border: 1px solid var(--border-medium);
            margin-top: 1rem;
        }
        
        .edit-workout-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--border-medium);
        }
        
        .edit-workout-title {
            margin: 0;
            color: var(--text-primary);
            font-size: 1.125rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .edit-workout-title i {
            color: var(--primary);
        }
        
        .close-edit-btn {
            background: none;
            border: none;
            color: var(--text-muted);
            font-size: 1.125rem;
            cursor: pointer;
            padding: 0.25rem;
            border-radius: var(--radius-md);
            transition: var(--transition-fast);
        }
        
        .close-edit-btn:hover {
            background: var(--bg-muted);
            color: var(--text-primary);
        }
        
        .edit-workout-form .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .edit-workout-form .form-group-full {
            grid-column: 1 / -1;
        }
        
        .edit-workout-form .form-actions {
            display: flex;
            gap: 0.75rem;
            justify-content: flex-end;
            padding-top: 1rem;
            border-top: 1px solid var(--border-medium);
        }
        
        .workout-stats {
            display: flex;
            gap: 1.5rem;
            flex-wrap: wrap;
        }
        
        .workout-stat {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            min-width: 80px;
        }
        
        .workout-stat-icon {
            font-size: 1.25rem;
            margin-bottom: 0.25rem;
        }
        
        .workout-stat-label {
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--text-secondary);
            margin-bottom: 0.25rem;
        }
        
        .workout-stat-value {
            font-size: 1rem;
            font-weight: 600;
            color: var(--text-primary);
        }
        
        .workout-card-actions {
            display: flex;
            gap: 0.5rem;
            align-items: center;
        }
        
        .workout-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1rem;
        }
        
        .workout-title-section {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }
        
        .workout-type {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--primary);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .workout-date {
            color: var(--text-tertiary);
            font-size: 0.875rem;
        }
        
        @media (max-width: 768px) {
            .workout-header {
                flex-direction: column;
                gap: 1rem;
                align-items: stretch;
            }
            
            .workout-card-actions {
                justify-content: stretch;
            }
            
            .workout-card-actions .btn {
                flex: 1;
            }
            
            .workout-stats {
                justify-content: space-around;
            }
        }
    </style>
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
                    <a href="view_workouts.php" class="nav-item active">
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
            <div class="top-bar">
                <div class="page-title">
                    <h1>View Workouts</h1>
                    <p>Manage and track your exercise history</p>
                </div>
                <div class="top-actions">
                    <a href="add_workout.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i>
                        Add New Workout
                    </a>
                    <button type="button" class="btn btn-outline" id="darkmode-toggle">
                        <i class="fas fa-moon"></i>
                        <span class="theme-text">Dark Mode</span>
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

                <!-- Workouts Card -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-list"></i>
                            All Workouts
                        </h3>
                        <div class="card-actions">
                            <div class="filter-controls">
                                <div class="search-wrapper">
                                    <i class="fas fa-search"></i>
                                    <input type="text" id="workoutFilter" class="form-input" placeholder="Search workouts..." />
                                </div>
                                <div class="select-wrapper">
                                    <select id="typeFilter" class="form-input">
                                        <option value="">All Types</option>
                                        <option value="Running">🏃 Running</option>
                                        <option value="Cycling">🚴 Cycling</option>
                                        <option value="Swimming">🏊 Swimming</option>
                                        <option value="Walking">🚶 Walking</option>
                                        <option value="Weightlifting">🏋️ Weightlifting</option>
                                        <option value="Yoga">🧘 Yoga</option>
                                        <option value="Other">🔄 Other</option>
                                    </select>
                                </div>
                                <div class="select-wrapper">
                                    <select id="sortWorkouts" class="form-input">
                                        <option value="date-desc">Newest First</option>
                                        <option value="date-asc">Oldest First</option>
                                        <option value="duration-desc">Longest First</option>
                                        <option value="duration-asc">Shortest First</option>
                                        <option value="calories-desc">Most Calories</option>
                                        <option value="calories-asc">Least Calories</option>
                                        <option value="type-asc">Type A-Z</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-content">
                        <?php if (!empty($workouts)): ?>
                        <div class="card-list" id="workoutsList">
                            <?php foreach ($workouts as $workout): ?>
                                <div class="card workout-item" style="margin-bottom: 16px;" 
                                     data-type="<?php echo htmlspecialchars($workout['type']); ?>"
                                     data-date="<?php echo $workout['date']; ?>"
                                     data-duration="<?php echo $workout['duration']; ?>"
                                     data-calories="<?php echo $workout['calories']; ?>"
                                     data-search="<?php echo strtolower(htmlspecialchars($workout['type']) . ' ' . $workout['date'] . ' ' . $workout['duration'] . ' ' . $workout['calories']); ?>">
                                    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                                        <div>
                                            <span style="font-size: 20px; font-weight: 600; color: #3b82f6;">
                                                <?php echo htmlspecialchars($workout['type']); ?>
                                            </span>
                                            <span style="color: #64748b; font-size: 14px; margin-left: 8px;">
                                                <?php echo date('M j, Y', strtotime($workout['date'])); ?>
                                            </span>
                                        </div>
                                        <div class="card-actions">
                                            <!-- Edit Button: toggles edit form -->
                                            <button onclick="document.getElementById('edit-form-<?php echo $workout['id']; ?>').style.display = (document.getElementById('edit-form-<?php echo $workout['id']; ?>').style.display === 'none' ? 'block' : 'none');" class="btn btn-outline btn-sm">
                                                <i class="fas fa-edit"></i>
                                                Edit
                                            </button>
                                            <!-- Delete Form -->
                                            <form method="post" action="" style="display:inline;">
                                                <input type="hidden" name="workout_id" value="<?php echo $workout['id']; ?>">
                                                <button type="submit" name="delete_workout" class="btn btn-danger btn-sm" onclick="return confirm('Delete this workout?');">
                                                    <i class="fas fa-trash"></i>
                                                    Delete
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                    <div class="card-content">
                                        <!-- Edit Form (hidden by default) -->
                                        <div id="edit-form-<?php echo $workout['id']; ?>" style="display:none; margin-top: 16px;">
                                            <div class="edit-workout-modal">
                                                <div class="edit-workout-header">
                                                    <h4 class="edit-workout-title">
                                                        <i class="fas fa-edit"></i>
                                                        Edit Workout
                                                    </h4>
                                                    <button type="button" class="close-edit-btn" onclick="document.getElementById('edit-form-<?php echo $workout['id']; ?>').style.display='none';">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </div>
                                                <form method="post" action="" class="edit-workout-form">
                                                    <input type="hidden" name="workout_id" value="<?php echo $workout['id']; ?>">
                                                    <input type="hidden" name="update_workout" value="1">
                                                    
                                                    <div class="form-grid">
                                                        <div class="form-group">
                                                            <label class="form-label">
                                                                <i class="fas fa-dumbbell"></i>
                                                                Exercise Type
                                                            </label>
                                                            <div class="select-wrapper">
                                                                <select name="type" class="form-input" required>
                                                                    <option value="">Select exercise type</option>
                                                                    <option value="Running" <?php if ($workout['type'] == 'Running') echo 'selected'; ?>>🏃 Running</option>
                                                                    <option value="Cycling" <?php if ($workout['type'] == 'Cycling') echo 'selected'; ?>>🚴 Cycling</option>
                                                                    <option value="Swimming" <?php if ($workout['type'] == 'Swimming') echo 'selected'; ?>>🏊 Swimming</option>
                                                                    <option value="Walking" <?php if ($workout['type'] == 'Walking') echo 'selected'; ?>>🚶 Walking</option>
                                                                    <option value="Yoga" <?php if ($workout['type'] == 'Yoga') echo 'selected'; ?>>🧘 Yoga</option>
                                                                    <option value="Strength Training" <?php if ($workout['type'] == 'Strength Training') echo 'selected'; ?>>💪 Strength Training</option>
                                                                    <option value="Other" <?php if ($workout['type'] == 'Other') echo 'selected'; ?>>🏋️ Other</option>
                                                                </select>
                                                                <i class="fas fa-chevron-down select-arrow"></i>
                                                            </div>
                                                        </div>
                                                        
                                                        <div class="form-group">
                                                            <label class="form-label">
                                                                <i class="fas fa-clock"></i>
                                                                Duration (minutes)
                                                            </label>
                                                            <input type="number" name="duration" class="form-input" value="<?php echo $workout['duration']; ?>" min="1" max="600" required>
                                                        </div>
                                                        
                                                        <div class="form-group">
                                                            <label class="form-label">
                                                                <i class="fas fa-route"></i>
                                                                Distance (km) - Optional
                                                            </label>
                                                            <input type="number" step="0.01" name="distance" class="form-input" value="<?php echo $workout['distance']; ?>" min="0" max="1000" placeholder="Enter distance">
                                                        </div>
                                                        
                                                        <div class="form-group">
                                                            <label class="form-label">
                                                                <i class="fas fa-fire"></i>
                                                                Calories
                                                            </label>
                                                            <input type="number" step="0.01" name="calories" class="form-input" value="<?php echo $workout['calories']; ?>" min="1" max="5000" required>
                                                        </div>
                                                        
                                                        <div class="form-group form-group-full">
                                                            <label class="form-label">
                                                                <i class="fas fa-calendar-alt"></i>
                                                                Date
                                                            </label>
                                                            <input type="date" name="date" class="form-input" value="<?php echo $workout['date']; ?>" required>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="form-actions">
                                                        <button type="submit" class="btn btn-primary">
                                                            <i class="fas fa-save"></i>
                                                            Save Workout
                                                        </button>
                                                        <button type="button" class="btn btn-outline" onclick="document.getElementById('edit-form-<?php echo $workout['id']; ?>').style.display='none';">
                                                            <i class="fas fa-times"></i>
                                                            Cancel
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                        <div style="display: flex; gap: 24px;">
                                            <div>
                                                <span style="color: #3b82f6;">⏱️</span>
                                                <strong>Duration</strong><br>
                                                <span style="color: #3b82f6; font-weight: 600;"><?php echo $workout['duration']; ?> min</span>
                                            </div>
                                            <div>
                                                <span style="color: #f59e0b;">🔥</span>
                                                <strong>Calories</strong><br>
                                                <span style="color: #f59e0b; font-weight: 600;"><?php echo $workout['calories']; ?></span>
                                            </div>
                                            <?php if ($workout['distance']): ?>
                                            <div>
                                                <span style="color: #10b981;">📏</span>
                                                <strong>Distance</strong><br>
                                                <span style="color: #10b981; font-weight: 600;"><?php echo $workout['distance']; ?> km</span>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <?php else: ?>
                        <div style="text-align: center; padding: 40px; color: #64748b;">
                            <div style="font-size: 48px; margin-bottom: 16px;">🏋️</div>
                            <h3>No workouts logged yet</h3>
                            <p>Start logging your workouts to see them here!</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>
    <script src="assets/js/darkmode.js"></script>
    <script>
    // Hide alert messages after 5 seconds
    window.addEventListener('DOMContentLoaded', function() {
        var alert = document.querySelector('.alert');
        if (alert) {
            setTimeout(function() {
                alert.style.display = 'none';
            }, 3000);
        }
    });
    
    // Workout Filtering and Sorting
    document.addEventListener('DOMContentLoaded', function() {
        const workoutFilter = document.getElementById('workoutFilter');
        const typeFilter = document.getElementById('typeFilter');
        const sortSelect = document.getElementById('sortWorkouts');
        const workoutsList = document.getElementById('workoutsList');
        
        if (!workoutsList) return;
        
        function filterAndSortWorkouts() {
            const workoutItems = Array.from(workoutsList.querySelectorAll('.workout-item'));
            const filterValue = workoutFilter ? workoutFilter.value.toLowerCase() : '';
            const typeValue = typeFilter ? typeFilter.value : '';
            const sortValue = sortSelect ? sortSelect.value : 'date-desc';
            
            // Filter workouts
            workoutItems.forEach(item => {
                const searchText = item.dataset.search;
                const workoutType = item.dataset.type;
                
                const matchesSearch = searchText.includes(filterValue);
                const matchesType = !typeValue || workoutType === typeValue;
                
                item.style.display = (matchesSearch && matchesType) ? '' : 'none';
            });
            
            // Sort visible workouts
            const visibleItems = workoutItems.filter(item => item.style.display !== 'none');
            visibleItems.sort((a, b) => {
                let aVal, bVal;
                
                switch(sortValue) {
                    case 'date-desc':
                    case 'date-asc':
                        aVal = new Date(a.dataset.date);
                        bVal = new Date(b.dataset.date);
                        break;
                    case 'duration-desc':
                    case 'duration-asc':
                        aVal = parseInt(a.dataset.duration);
                        bVal = parseInt(b.dataset.duration);
                        break;
                    case 'calories-desc':
                    case 'calories-asc':
                        aVal = parseFloat(a.dataset.calories);
                        bVal = parseFloat(b.dataset.calories);
                        break;
                    case 'type-asc':
                        aVal = a.dataset.type.toLowerCase();
                        bVal = b.dataset.type.toLowerCase();
                        return aVal.localeCompare(bVal);
                }
                
                if (sortValue.includes('-desc')) {
                    return bVal - aVal;
                } else {
                    return aVal - bVal;
                }
            });
            
            // Re-append sorted items
            visibleItems.forEach(item => workoutsList.appendChild(item));
        }
        
        if (workoutFilter) workoutFilter.addEventListener('input', filterAndSortWorkouts);
        if (typeFilter) typeFilter.addEventListener('change', filterAndSortWorkouts);
        if (sortSelect) sortSelect.addEventListener('change', filterAndSortWorkouts);
    });
    </script>
</body>
</html>
