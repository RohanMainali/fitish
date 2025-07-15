<?php
// stats.php
require_once 'includes/auth.class.php';
require_once 'includes/stats.class.php';
require_once 'includes/metrics.class.php';
require_once 'includes/csrf.class.php';

$auth = new Auth();
$auth->requireLogin();
$user = $auth->currentUser();
$message = '';
$csrf_token = CSRF::generateToken();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!CSRF::validateToken($_POST['csrf_token'] ?? '')) {
        $message = 'Invalid CSRF token.';
    } else {
        $weight = floatval($_POST['weight'] ?? 0);
        $height = floatval($_POST['height'] ?? 0);
        $age = intval($_POST['age'] ?? 0);
        $gender = $_POST['gender'] ?? 'other';
        $activity = $_POST['activity'] ?? 'sedentary';
        $date = $_POST['date'] ?? date('Y-m-d');
        
        $bmi = Metrics::calculateBMI($weight, $height);
        $bmr = Metrics::calculateBMR($weight, $height, $age, $gender);
        $tdee = Metrics::calculateTDEE($bmr, $activity);
        
        $statsObj = new Stats();
        if ($statsObj->create($user['id'], $weight, $height, $age, $gender, $bmi, $bmr, $tdee, $date)) {
            header('Location: dashboard.php');
            exit;
        } else {
            $message = 'Failed to add stats.';
        }
    }
}

$statsObj = new Stats();
$userStats = $statsObj->getByUser($user['id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Body Stats - Fitish Pro</title>
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
                    <a href="stats.php" class="nav-item active">
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
                    <h1>Body Stats</h1>
                    <p>Track your physical metrics and health indicators</p>
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

                <!-- Add New Stats Card -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-plus-circle"></i>
                            Add New Measurements
                        </h3>
                    </div>
                    <div class="card-content">
                        <form method="post" action="" id="addStatsForm">
                            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-weight"></i>
                                        Weight (kg)
                                    </label>
                                    <input type="number" step="0.1" name="weight" class="form-input" placeholder="e.g., 70.5" required>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-ruler-vertical"></i>
                                        Height (cm)
                                    </label>
                                    <input type="number" step="0.1" name="height" class="form-input" placeholder="e.g., 175" required>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-birthday-cake"></i>
                                        Age
                                    </label>
                                    <input type="number" name="age" class="form-input" placeholder="e.g., 25" required>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-venus-mars"></i>
                                        Gender
                                    </label>
                                    <div class="select-wrapper">
                                        <select name="gender" class="form-input" required>
                                            <option value="male">Male</option>
                                            <option value="female">Female</option>
                                            <option value="other">Other</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-running"></i>
                                        Activity Level
                                    </label>
                                    <div class="select-wrapper">
                                        <select name="activity" class="form-input" required>
                                            <option value="sedentary">Sedentary (little/no exercise)</option>
                                            <option value="light">Light (light exercise 1-3 days/week)</option>
                                            <option value="moderate">Moderate (moderate exercise 3-5 days/week)</option>
                                            <option value="active">Active (hard exercise 6-7 days/week)</option>
                                            <option value="very_active">Very Active (very hard exercise, physical job)</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-calendar"></i>
                                        Date
                                    </label>
                                    <input type="date" name="date" value="<?php echo date('Y-m-d'); ?>" class="form-input" required>
                                </div>
                            </div>

                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i>
                                    Save Stats
                                </button>
                                <a href="dashboard.php" class="btn btn-outline">
                                    <i class="fas fa-arrow-left"></i>
                                    Back to Dashboard
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Stats History -->
                <?php if (!empty($userStats)): ?>
                <div class="card card-spacing">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-chart-line"></i>
                            Your Stats History
                        </h3>
                        <div class="card-actions">
                            <div class="filter-controls">
                                <div class="search-wrapper">
                                    <i class="fas fa-search"></i>
                                    <input type="text" id="statsFilter" class="form-input" placeholder="Search by date...">
                                </div>
                                <div class="select-wrapper">
                                    <select id="sortStats" class="form-input">
                                        <option value="date-desc">Newest First</option>
                                        <option value="date-asc">Oldest First</option>
                                        <option value="weight-desc">Weight High to Low</option>
                                        <option value="weight-asc">Weight Low to High</option>
                                        <option value="bmi-desc">BMI High to Low</option>
                                        <option value="bmi-asc">BMI Low to High</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-content">
                        <div class="stats-table-wrapper">
                            <table class="stats-table" id="statsTable">
                                <thead>
                                    <tr>
                                        <th><i class="fas fa-calendar"></i> Date</th>
                                        <th><i class="fas fa-weight"></i> Weight</th>
                                        <th><i class="fas fa-chart-bar"></i> BMI</th>
                                        <th><i class="fas fa-fire"></i> BMR</th>
                                        <th><i class="fas fa-bolt"></i> TDEE</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($userStats as $stat): ?>
                                        <tr class="stats-row">
                                            <td class="date-cell">
                                                <span class="date-primary"><?php echo date('M j, Y', strtotime($stat['date'])); ?></span>
                                                <span class="date-secondary"><?php echo date('l', strtotime($stat['date'])); ?></span>
                                            </td>
                                            <td class="weight-cell">
                                                <span class="metric-value"><?php echo $stat['weight']; ?></span>
                                                <span class="metric-unit">kg</span>
                                            </td>
                                            <td class="bmi-cell">
                                                <div class="bmi-container">
                                                    <div class="bmi-value-row">
                                                        <span class="metric-value"><?php echo number_format($stat['bmi'], 1); ?></span>
                                                        <?php if ($stat['bmi'] < 18.5): ?>
                                                            <span class="bmi-indicator bmi-underweight">
                                                                <i class="fas fa-arrow-down"></i>
                                                                Underweight
                                                            </span>
                                                        <?php elseif ($stat['bmi'] < 25): ?>
                                                            <span class="bmi-indicator bmi-normal">
                                                                <i class="fas fa-check-circle"></i>
                                                                Normal
                                                            </span>
                                                        <?php elseif ($stat['bmi'] < 30): ?>
                                                            <span class="bmi-indicator bmi-overweight">
                                                                <i class="fas fa-exclamation-circle"></i>
                                                                Overweight
                                                            </span>
                                                        <?php else: ?>
                                                            <span class="bmi-indicator bmi-obese">
                                                                <i class="fas fa-exclamation-triangle"></i>
                                                                Obese
                                                            </span>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="bmr-cell">
                                                <span class="metric-value"><?php echo number_format($stat['bmr']); ?></span>
                                                <span class="metric-unit">cal/day</span>
                                            </td>
                                            <td class="tdee-cell">
                                                <span class="metric-value"><?php echo number_format($stat['tdee']); ?></span>
                                                <span class="metric-unit">cal/day</span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <?php else: ?>
                <div class="card card-spacing">
                    <div class="card-content">
                        <div class="empty-state">
                            <div class="empty-icon">
                                <i class="fas fa-chart-line"></i>
                            </div>
                            <h3>No Stats Recorded Yet</h3>
                            <p>Start tracking your body metrics by adding your first measurement above.</p>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Understanding Your Metrics -->
                <div class="card card-spacing">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-lightbulb"></i>
                            Understanding Your Metrics
                        </h3>
                    </div>
                    <div class="card-content">
                        <div class="metrics-info-grid">
                            <div class="metric-info-card">
                                <div class="metric-info-header">
                                    <i class="fas fa-chart-bar metric-icon bmi-icon"></i>
                                    <h4>BMI (Body Mass Index)</h4>
                                </div>
                                <p class="metric-description">
                                    Measures body fat based on height and weight ratio. 
                                    It's a useful screening tool but doesn't directly measure body fat.
                                </p>
                                <div class="bmi-ranges">
                                    <div class="range-item">
                                        <span class="range-indicator bmi-underweight"></span>
                                        <span class="range-text">Under 18.5: Underweight</span>
                                    </div>
                                    <div class="range-item">
                                        <span class="range-indicator bmi-normal"></span>
                                        <span class="range-text">18.5-24.9: Normal weight</span>
                                    </div>
                                    <div class="range-item">
                                        <span class="range-indicator bmi-overweight"></span>
                                        <span class="range-text">25-29.9: Overweight</span>
                                    </div>
                                    <div class="range-item">
                                        <span class="range-indicator bmi-obese"></span>
                                        <span class="range-text">30+: Obese</span>
                                    </div>
                                </div>
                            </div>

                            <div class="metric-info-card">
                                <div class="metric-info-header">
                                    <i class="fas fa-fire metric-icon bmr-icon"></i>
                                    <h4>BMR (Basal Metabolic Rate)</h4>
                                </div>
                                <p class="metric-description">
                                    The number of calories your body needs to maintain basic physiological functions at rest.
                                </p>
                                <div class="metric-tips">
                                    <div class="tip-item">
                                        <i class="fas fa-info-circle"></i>
                                        <span>Accounts for 60-75% of total daily energy expenditure</span>
                                    </div>
                                    <div class="tip-item">
                                        <i class="fas fa-heartbeat"></i>
                                        <span>Includes breathing, circulation, and cellular functions</span>
                                    </div>
                                </div>
                            </div>

                            <div class="metric-info-card">
                                <div class="metric-info-header">
                                    <i class="fas fa-bolt metric-icon tdee-icon"></i>
                                    <h4>TDEE (Total Daily Energy Expenditure)</h4>
                                </div>
                                <p class="metric-description">
                                    Total calories burned including physical activity. Use this for calorie planning.
                                </p>
                                <div class="activity-levels">
                                    <div class="activity-item">
                                        <span class="activity-label">Sedentary:</span>
                                        <span class="activity-multiplier">BMR × 1.2</span>
                                    </div>
                                    <div class="activity-item">
                                        <span class="activity-label">Light:</span>
                                        <span class="activity-multiplier">BMR × 1.375</span>
                                    </div>
                                    <div class="activity-item">
                                        <span class="activity-label">Moderate:</span>
                                        <span class="activity-multiplier">BMR × 1.55</span>
                                    </div>
                                    <div class="activity-item">
                                        <span class="activity-label">Active:</span>
                                        <span class="activity-multiplier">BMR × 1.725</span>
                                    </div>
                                    <div class="activity-item">
                                        <span class="activity-label">Very Active:</span>
                                        <span class="activity-multiplier">BMR × 1.9</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="assets/js/ajax.js"></script>
    <script>ajaxForm('#addStatsForm');</script>
    <script src="assets/js/darkmode.js"></script>
    
    <script>
    // Stats Table Filtering and Sorting
    document.addEventListener('DOMContentLoaded', function() {
        const filterInput = document.getElementById('statsFilter');
        const sortSelect = document.getElementById('sortStats');
        const table = document.getElementById('statsTable');
        
        if (!table) return;
        
        function filterAndSortTable() {
            const tbody = table.querySelector('tbody');
            if (!tbody) return;
            
            const rows = Array.from(tbody.querySelectorAll('tr'));
            const filterValue = filterInput.value.toLowerCase();
            const sortValue = sortSelect.value;
            
            // Filter rows
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(filterValue) ? '' : 'none';
            });
            
            // Sort visible rows
            const visibleRows = rows.filter(row => row.style.display !== 'none');
            visibleRows.sort((a, b) => {
                let aVal, bVal;
                
                switch(sortValue) {
                    case 'date-desc':
                    case 'date-asc':
                        aVal = new Date(a.cells[0].textContent);
                        bVal = new Date(b.cells[0].textContent);
                        break;
                    case 'weight-desc':
                    case 'weight-asc':
                        aVal = parseFloat(a.cells[1].textContent);
                        bVal = parseFloat(b.cells[1].textContent);
                        break;
                    case 'bmi-desc':
                    case 'bmi-asc':
                        aVal = parseFloat(a.cells[2].textContent);
                        bVal = parseFloat(b.cells[2].textContent);
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
        
        filterInput.addEventListener('input', filterAndSortTable);
        sortSelect.addEventListener('change', filterAndSortTable);
    });
    </script>
</body>
</html>
