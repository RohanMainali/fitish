<?php
// includes/system_analytics.class.php - Comprehensive System Analytics
class SystemAnalytics {
    private $conn;
    
    public function __construct() {
        require_once 'db.php';
        $db = new Database();
        $this->conn = $db->connect();
    }
    
    /**
     * Get comprehensive user statistics
     */
    public function getUserAnalytics() {
        try {
            $sql = "SELECT 
                        COUNT(*) as total_users,
                        SUM(CASE WHEN role = 'user' THEN 1 ELSE 0 END) as regular_users,
                        SUM(CASE WHEN role = 'admin' THEN 1 ELSE 0 END) as admins,
                        SUM(CASE WHEN role = 'superadmin' THEN 1 ELSE 0 END) as superadmins,
                        SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active_users,
                        SUM(CASE WHEN is_active = 0 THEN 1 ELSE 0 END) as inactive_users,
                        SUM(CASE WHEN DATE(created_at) = CURDATE() THEN 1 ELSE 0 END) as today_registrations,
                        SUM(CASE WHEN DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) THEN 1 ELSE 0 END) as week_registrations,
                        SUM(CASE WHEN DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) THEN 1 ELSE 0 END) as month_registrations,
                        SUM(CASE WHEN DATE(last_login) = CURDATE() THEN 1 ELSE 0 END) as today_active,
                        SUM(CASE WHEN DATE(last_login) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) THEN 1 ELSE 0 END) as week_active
                    FROM users";
            
            $stmt = $this->conn->query($sql);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("User analytics error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get workout statistics
     */
    public function getWorkoutAnalytics() {
        try {
            $sql = "SELECT 
                        COUNT(*) as total_workouts,
                        COUNT(DISTINCT user_id) as active_users,
                        AVG(duration) as avg_duration,
                        AVG(distance) as avg_distance,
                        AVG(calories) as avg_calories,
                        SUM(duration) as total_duration,
                        SUM(distance) as total_distance,
                        SUM(calories) as total_calories,
                        SUM(CASE WHEN DATE(date) = CURDATE() THEN 1 ELSE 0 END) as today_workouts,
                        SUM(CASE WHEN DATE(date) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) THEN 1 ELSE 0 END) as week_workouts,
                        SUM(CASE WHEN DATE(date) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) THEN 1 ELSE 0 END) as month_workouts
                    FROM workouts";
            
            $stmt = $this->conn->query($sql);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Workout analytics error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get workout type distribution
     */
    public function getWorkoutTypeDistribution() {
        try {
            $sql = "SELECT 
                        type,
                        COUNT(*) as count,
                        AVG(duration) as avg_duration,
                        AVG(distance) as avg_distance,
                        AVG(calories) as avg_calories,
                        ROUND((COUNT(*) * 100.0 / (SELECT COUNT(*) FROM workouts)), 2) as percentage
                    FROM workouts 
                    GROUP BY type 
                    ORDER BY count DESC";
            
            $stmt = $this->conn->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Workout type distribution error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get goals analytics
     */
    public function getGoalsAnalytics() {
        try {
            $sql = "SELECT 
                        COUNT(*) as total_goals,
                        SUM(CASE WHEN is_achieved = 1 THEN 1 ELSE 0 END) as achieved_goals,
                        SUM(CASE WHEN is_achieved = 0 THEN 1 ELSE 0 END) as active_goals,
                        ROUND((SUM(CASE WHEN is_achieved = 1 THEN 1 ELSE 0 END) * 100.0 / COUNT(*)), 2) as achievement_rate,
                        COUNT(DISTINCT user_id) as users_with_goals
                    FROM goals";
            
            $stmt = $this->conn->query($sql);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Goals analytics error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get daily activity trends (last 30 days)
     */
    public function getDailyActivityTrends() {
        try {
            $sql = "SELECT 
                        DATE(w.date) as activity_date,
                        COUNT(w.id) as workout_count,
                        COUNT(DISTINCT w.user_id) as active_users,
                        COALESCE(u.new_users, 0) as new_registrations
                    FROM workouts w
                    LEFT JOIN (
                        SELECT DATE(created_at) as reg_date, COUNT(*) as new_users 
                        FROM users 
                        WHERE DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                        GROUP BY DATE(created_at)
                    ) u ON DATE(w.date) = u.reg_date
                    WHERE DATE(w.date) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                    GROUP BY DATE(w.date)
                    ORDER BY activity_date DESC
                    LIMIT 30";
            
            $stmt = $this->conn->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Daily activity trends error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get user engagement metrics
     */
    public function getUserEngagement() {
        try {
            $sql = "SELECT 
                        u.id,
                        u.username,
                        u.email,
                        u.role,
                        u.created_at,
                        u.last_login,
                        COALESCE(w.workout_count, 0) as total_workouts,
                        COALESCE(g.goal_count, 0) as total_goals,
                        COALESCE(b.badge_count, 0) as total_badges,
                        DATEDIFF(CURDATE(), DATE(u.last_login)) as days_since_login
                    FROM users u
                    LEFT JOIN (SELECT user_id, COUNT(*) as workout_count FROM workouts GROUP BY user_id) w ON u.id = w.user_id
                    LEFT JOIN (SELECT user_id, COUNT(*) as goal_count FROM goals GROUP BY user_id) g ON u.id = g.user_id
                    LEFT JOIN (SELECT user_id, COUNT(*) as badge_count FROM user_badges GROUP BY user_id) b ON u.id = b.user_id
                    ORDER BY total_workouts DESC, u.last_login DESC";
            
            $stmt = $this->conn->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("User engagement error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get system health metrics
     */
    public function getSystemHealth() {
        try {
            $health = [];
            
            // Database size
            $sql = "SELECT 
                        table_name,
                        table_rows,
                        ROUND(((data_length + index_length) / 1024 / 1024), 2) as size_mb
                    FROM information_schema.TABLES 
                    WHERE table_schema = DATABASE()
                    ORDER BY (data_length + index_length) DESC";
            
            $stmt = $this->conn->query($sql);
            $health['database_tables'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Recent error count (if logs table exists)
            try {
                $sql = "SELECT COUNT(*) as error_count FROM admin_logs WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)";
                $stmt = $this->conn->query($sql);
                $health['recent_admin_activities'] = $stmt->fetch(PDO::FETCH_ASSOC)['error_count'] ?? 0;
            } catch (Exception $e) {
                $health['recent_admin_activities'] = 0;
            }
            
            // Active sessions (approximate)
            $sql = "SELECT COUNT(*) as active_sessions FROM users WHERE last_login >= DATE_SUB(NOW(), INTERVAL 30 MINUTE)";
            $stmt = $this->conn->query($sql);
            $health['active_sessions'] = $stmt->fetch(PDO::FETCH_ASSOC)['active_sessions'] ?? 0;
            
            return $health;
        } catch (Exception $e) {
            error_log("System health error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get top performers
     */
    public function getTopPerformers() {
        try {
            $sql = "SELECT 
                        u.username,
                        u.email,
                        COUNT(w.id) as workout_count,
                        SUM(w.duration) as total_duration,
                        SUM(w.distance) as total_distance,
                        SUM(w.calories) as total_calories,
                        COUNT(g.id) as goals_achieved
                    FROM users u
                    LEFT JOIN workouts w ON u.id = w.user_id
                    LEFT JOIN goals g ON u.id = g.user_id AND g.is_achieved = 1
                    WHERE u.role = 'user'
                    GROUP BY u.id, u.username, u.email
                    HAVING workout_count > 0
                    ORDER BY workout_count DESC, total_calories DESC
                    LIMIT 10";
            
            $stmt = $this->conn->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Top performers error: " . $e->getMessage());
            return [];
        }
    }
}
?>
