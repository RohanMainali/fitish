<?php
// leaderboard.php
require_once 'includes/leaderboard.class.php';
require_once 'includes/auth.class.php';

$auth = new Auth();
$auth->requireLogin();
$user = $auth->currentUser();
$leaderboardObj = new Leaderboard();
$topUsers = $leaderboardObj->getWeeklyLeaderboard();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leaderboard - Fitish Pro</title>
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
                    <a href="goals.php" class="nav-item">
                        <i class="fas fa-bullseye"></i>
                        Goals
                    </a>
                    <a href="leaderboard.php" class="nav-item active">
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
                    <h1>Weekly Leaderboard</h1>
                    <p>Compete with other users and climb the rankings</p>
                </div>
                <div class="top-actions">
                    <button type="button" class="btn btn-outline" id="darkmode-toggle">
                        <i class="fas fa-moon"></i>
                        Dark Mode
                    </button>
                </div>
            </div>

            <div class="dashboard-content">

            <div class="dashboard-content">
                <!-- Weekly Champions Card -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-trophy"></i>
                            Weekly Champions
                        </h3>
                        <div class="card-actions">
                            <div class="filter-controls">
                                <div class="search-wrapper">
                                    <i class="fas fa-search"></i>
                                    <input type="text" id="leaderboardFilter" class="form-input" placeholder="Search users...">
                                </div>
                                <div class="select-wrapper">
                                    <select id="sortLeaderboard" class="form-input">
                                        <option value="workouts-desc">Most Workouts</option>
                                        <option value="workouts-asc">Fewest Workouts</option>
                                        <option value="name-asc">Name A-Z</option>
                                        <option value="name-desc">Name Z-A</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-content">
                        <?php if (!empty($topUsers)): ?>
                            <div class="leaderboard-container">
                                <!-- Top 3 Podium -->
                                <?php if (count($topUsers) >= 3): ?>
                                <div class="podium-section">
                                    <div class="podium">
                                        <!-- 2nd Place -->
                                        <div class="podium-item podium-second">
                                            <div class="podium-user">
                                                <div class="podium-avatar">
                                                    <?php echo strtoupper(substr($topUsers[1]['username'], 0, 1)); ?>
                                                </div>
                                                <h4 class="podium-name"><?php echo htmlspecialchars($topUsers[1]['username']); ?></h4>
                                                <p class="podium-score"><?php echo $topUsers[1]['workouts']; ?> workouts</p>
                                            </div>
                                            <div class="podium-rank">
                                                <i class="fas fa-medal silver-medal"></i>
                                                <span class="rank-number">2</span>
                                            </div>
                                        </div>
                                        
                                        <!-- 1st Place -->
                                        <div class="podium-item podium-first">
                                            <div class="podium-user">
                                                <div class="podium-avatar champion">
                                                    <?php echo strtoupper(substr($topUsers[0]['username'], 0, 1)); ?>
                                                </div>
                                                <h4 class="podium-name"><?php echo htmlspecialchars($topUsers[0]['username']); ?></h4>
                                                <p class="podium-score"><?php echo $topUsers[0]['workouts']; ?> workouts</p>
                                                <div class="champion-crown">
                                                    <i class="fas fa-crown"></i>
                                                </div>
                                            </div>
                                            <div class="podium-rank">
                                                <i class="fas fa-trophy gold-medal"></i>
                                                <span class="rank-number">1</span>
                                            </div>
                                        </div>
                                        
                                        <!-- 3rd Place -->
                                        <div class="podium-item podium-third">
                                            <div class="podium-user">
                                                <div class="podium-avatar">
                                                    <?php echo strtoupper(substr($topUsers[2]['username'], 0, 1)); ?>
                                                </div>
                                                <h4 class="podium-name"><?php echo htmlspecialchars($topUsers[2]['username']); ?></h4>
                                                <p class="podium-score"><?php echo $topUsers[2]['workouts']; ?> workouts</p>
                                            </div>
                                            <div class="podium-rank">
                                                <i class="fas fa-medal bronze-medal"></i>
                                                <span class="rank-number">3</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <!-- Complete Rankings Table -->
                                <div class="leaderboard-table-wrapper">
                                    <table class="leaderboard-table" id="leaderboardTable">
                                        <thead>
                                            <tr>
                                                <th><i class="fas fa-hashtag"></i> Rank</th>
                                                <th><i class="fas fa-user"></i> User</th>
                                                <th><i class="fas fa-dumbbell"></i> Workouts</th>
                                                <th><i class="fas fa-medal"></i> Status</th>
                                                <th><i class="fas fa-chart-line"></i> Progress</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($topUsers as $i => $leaderUser): ?>
                                                <tr class="leaderboard-row <?php echo $leaderUser['username'] === $user['username'] ? 'current-user' : ''; ?>">
                                                    <td class="rank-cell">
                                                        <div class="rank-container">
                                                            <?php if ($i === 0): ?>
                                                                <i class="fas fa-crown rank-icon gold"></i>
                                                            <?php elseif ($i === 1): ?>
                                                                <i class="fas fa-medal rank-icon silver"></i>
                                                            <?php elseif ($i === 2): ?>
                                                                <i class="fas fa-medal rank-icon bronze"></i>
                                                            <?php else: ?>
                                                                <span class="rank-number">#<?php echo $i + 1; ?></span>
                                                            <?php endif; ?>
                                                        </div>
                                                    </td>
                                                    <td class="user-cell">
                                                        <div class="user-info-row">
                                                            <div class="user-avatar-small">
                                                                <?php echo strtoupper(substr($leaderUser['username'], 0, 1)); ?>
                                                            </div>
                                                            <div class="user-details">
                                                                <span class="username"><?php echo htmlspecialchars($leaderUser['username']); ?></span>
                                                                <?php if ($leaderUser['username'] === $user['username']): ?>
                                                                    <span class="you-badge">You</span>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="workouts-cell">
                                                        <div class="workout-count">
                                                            <span class="count-number"><?php echo $leaderUser['workouts']; ?></span>
                                                            <span class="count-label">workouts</span>
                                                        </div>
                                                    </td>
                                                    <td class="status-cell">
                                                        <?php if ($i === 0): ?>
                                                            <span class="status-badge champion">
                                                                <i class="fas fa-crown"></i>
                                                                Champion
                                                            </span>
                                                        <?php elseif ($i < 3): ?>
                                                            <span class="status-badge podium">
                                                                <i class="fas fa-medal"></i>
                                                                Top 3
                                                            </span>
                                                        <?php elseif ($i < 10): ?>
                                                            <span class="status-badge top10">
                                                                <i class="fas fa-star"></i>
                                                                Top 10
                                                            </span>
                                                        <?php else: ?>
                                                            <span class="status-badge active">
                                                                <i class="fas fa-dumbbell"></i>
                                                                Active
                                                            </span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="progress-cell">
                                                        <div class="progress-bar">
                                                            <?php 
                                                            $maxWorkouts = max(array_column($topUsers, 'workouts'));
                                                            $percentage = $maxWorkouts > 0 ? ($leaderUser['workouts'] / $maxWorkouts) * 100 : 0;
                                                            ?>
                                                            <div class="progress-fill" style="width: <?php echo $percentage; ?>%"></div>
                                                            <span class="progress-text"><?php echo round($percentage); ?>%</span>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="empty-state">
                                <div class="empty-icon">
                                    <i class="fas fa-trophy"></i>
                                </div>
                                <h3>No Competition Data Yet</h3>
                                <p>Be the first to log a workout this week and claim the top spot!</p>
                                <a href="add_workout.php" class="btn btn-primary">
                                    <i class="fas fa-plus"></i>
                                    Log Your First Workout
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                </div>

                <!-- Competition Statistics -->
                <div class="card card-spacing">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-chart-bar"></i>
                            Competition Statistics
                        </h3>
                    </div>
                    <div class="card-content">
                        <div class="stats-overview">
                            <div class="stat-item">
                                <div class="stat-icon">
                                    <i class="fas fa-users"></i>
                                </div>
                                <div class="stat-content">
                                    <h4 class="stat-number"><?php echo count($topUsers); ?></h4>
                                    <p class="stat-label">Active Competitors</p>
                                </div>
                            </div>
                            
                            <div class="stat-item">
                                <div class="stat-icon">
                                    <i class="fas fa-dumbbell"></i>
                                </div>
                                <div class="stat-content">
                                    <h4 class="stat-number"><?php echo !empty($topUsers) ? array_sum(array_column($topUsers, 'workouts')) : 0; ?></h4>
                                    <p class="stat-label">Total Workouts This Week</p>
                                </div>
                            </div>
                            
                            <div class="stat-item">
                                <div class="stat-icon">
                                    <i class="fas fa-fire"></i>
                                </div>
                                <div class="stat-content">
                                    <h4 class="stat-number"><?php echo !empty($topUsers) ? max(array_column($topUsers, 'workouts')) : 0; ?></h4>
                                    <p class="stat-label">Highest Individual Score</p>
                                </div>
                            </div>
                            
                            <div class="stat-item">
                                <div class="stat-icon">
                                    <i class="fas fa-chart-line"></i>
                                </div>
                                <div class="stat-content">
                                    <h4 class="stat-number"><?php echo !empty($topUsers) ? round(array_sum(array_column($topUsers, 'workouts')) / count($topUsers), 1) : 0; ?></h4>
                                    <p class="stat-label">Average Workouts</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Motivational Call-to-Action -->
                <div class="card card-spacing">
                    <div class="card-content">
                        <div class="cta-section">
                            <div class="cta-content">
                                <h3>Ready to Climb the Rankings?</h3>
                                <p>Every workout counts! Log your exercises and compete with the community to stay motivated and reach your fitness goals.</p>
                            </div>
                            <div class="cta-actions">
                                <a href="add_workout.php" class="btn btn-primary">
                                    <i class="fas fa-plus-circle"></i>
                                    Log Workout
                                </a>
                                <a href="dashboard.php" class="btn btn-outline">
                                    <i class="fas fa-chart-line"></i>
                                    View Dashboard
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Dark Mode Script -->
    <script src="assets/js/darkmode.js"></script>
    
    <script>
    // Enhanced Leaderboard Table Filtering and Sorting
    document.addEventListener('DOMContentLoaded', function() {
        const filterInput = document.getElementById('leaderboardFilter');
        const sortSelect = document.getElementById('sortLeaderboard');
        const table = document.getElementById('leaderboardTable');
        
        if (!table) return;
        
        function filterAndSortTable() {
            const tbody = table.querySelector('tbody');
            if (!tbody) return;
            
            const rows = Array.from(tbody.querySelectorAll('tr'));
            const filterValue = filterInput.value.toLowerCase().trim();
            const sortValue = sortSelect.value;
            
            // Filter rows
            rows.forEach(row => {
                const usernameCell = row.querySelector('.username');
                const username = usernameCell ? usernameCell.textContent.toLowerCase() : '';
                const shouldShow = !filterValue || username.includes(filterValue);
                row.style.display = shouldShow ? '' : 'none';
            });
            
            // Get visible rows for sorting
            const visibleRows = rows.filter(row => row.style.display !== 'none');
            
            // Sort visible rows
            visibleRows.sort((a, b) => {
                let aVal, bVal;
                
                switch(sortValue) {
                    case 'workouts-desc':
                    case 'workouts-asc':
                        const aWorkouts = a.querySelector('.count-number');
                        const bWorkouts = b.querySelector('.count-number');
                        aVal = aWorkouts ? parseInt(aWorkouts.textContent) : 0;
                        bVal = bWorkouts ? parseInt(bWorkouts.textContent) : 0;
                        break;
                    case 'name-asc':
                    case 'name-desc':
                        const aName = a.querySelector('.username');
                        const bName = b.querySelector('.username');
                        aVal = aName ? aName.textContent.toLowerCase() : '';
                        bVal = bName ? bName.textContent.toLowerCase() : '';
                        return sortValue === 'name-asc' ? aVal.localeCompare(bVal) : bVal.localeCompare(aVal);
                }
                
                return sortValue.includes('-desc') ? bVal - aVal : aVal - bVal;
            });
            
            // Re-append sorted rows and update rank numbers
            visibleRows.forEach((row, index) => {
                tbody.appendChild(row);
                
                // Update rank display
                const rankContainer = row.querySelector('.rank-container');
                if (rankContainer && index >= 3) {
                    rankContainer.innerHTML = `<span class="rank-number">#${index + 1}</span>`;
                }
                
                // Update progress bars
                const progressFill = row.querySelector('.progress-fill');
                const progressText = row.querySelector('.progress-text');
                if (progressFill && progressText && visibleRows.length > 0) {
                    const workoutCount = parseInt(row.querySelector('.count-number').textContent);
                    const maxWorkouts = Math.max(...visibleRows.map(r => parseInt(r.querySelector('.count-number').textContent)));
                    const percentage = maxWorkouts > 0 ? (workoutCount / maxWorkouts) * 100 : 0;
                    
                    progressFill.style.width = percentage + '%';
                    progressText.textContent = Math.round(percentage) + '%';
                }
            });
        }
        
        // Add event listeners
        if (filterInput) {
            filterInput.addEventListener('input', filterAndSortTable);
        }
        
        if (sortSelect) {
            sortSelect.addEventListener('change', filterAndSortTable);
        }
        
        // Initialize progress bars on page load
        const progressBars = document.querySelectorAll('.progress-bar');
        if (progressBars.length > 0) {
            setTimeout(() => {
                progressBars.forEach(bar => {
                    const fill = bar.querySelector('.progress-fill');
                    if (fill) {
                        const width = fill.style.width;
                        fill.style.width = '0%';
                        setTimeout(() => {
                            fill.style.width = width;
                        }, 100);
                    }
                });
            }, 300);
        }
    });
    </script>
</body>
</html>
