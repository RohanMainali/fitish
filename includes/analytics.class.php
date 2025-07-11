<?php
// /includes/analytics.class.php
require_once 'db.php';
class Analytics {
    private $conn;
    public function __construct() {
        $db = new Database();
        $this->conn = $db->connect();
    }
    // Weekly improvement: % change in distance, calories, duration
    public function getWeeklyImprovement($user_id) {
        $sql = "SELECT WEEK(date, 1) as week, YEAR(date) as year, SUM(distance) as total_distance, SUM(calories) as total_calories, SUM(duration) as total_duration FROM workouts WHERE user_id = ? GROUP BY year, week ORDER BY year DESC, week DESC LIMIT 2";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$user_id]);
        $weeks = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (count($weeks) < 2) return null;
        $curr = $weeks[0];
        $prev = $weeks[1];
        $distance_change = $prev['total_distance'] > 0 ? round((($curr['total_distance'] - $prev['total_distance']) / $prev['total_distance']) * 100, 2) : null;
        $calories_change = $prev['total_calories'] > 0 ? round((($curr['total_calories'] - $prev['total_calories']) / $prev['total_calories']) * 100, 2) : null;
        $duration_change = $prev['total_duration'] > 0 ? round((($curr['total_duration'] - $prev['total_duration']) / $prev['total_duration']) * 100, 2) : null;
        return [
            'distance' => $distance_change,
            'calories' => $calories_change,
            'duration' => $duration_change
        ];
    }
    // Goal proximity: % progress toward each goal
    public function getGoalProximity($user_id) {
        $sql = "SELECT goal_type, target_value, current_value FROM goals WHERE user_id = ? AND is_achieved = 0";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$user_id]);
        $goals = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $proximity = [];
        foreach ($goals as $g) {
            $percent = $g['target_value'] > 0 ? round(($g['current_value'] / $g['target_value']) * 100, 2) : 0;
            $proximity[] = [
                'type' => $g['goal_type'],
                'progress' => $percent
            ];
        }
        return $proximity;
    }
}
?>
