<?php
// /includes/streak.class.php
require_once 'db.php';
class Streak {
    private $conn;
    public function __construct() {
        $db = new Database();
        $this->conn = $db->connect();
    }
    // Get current streak (consecutive workout days)
    public function getCurrentStreak($user_id) {
        $sql = "SELECT date FROM workouts WHERE user_id = ? ORDER BY date DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$user_id]);
        $dates = $stmt->fetchAll(PDO::FETCH_COLUMN);
        if (!$dates) return 0;
        $streak = 1;
        $prev = strtotime($dates[0]);
        for ($i = 1; $i < count($dates); $i++) {
            $curr = strtotime($dates[$i]);
            if ($prev - $curr == 86400) {
                $streak++;
                $prev = $curr;
            } else {
                break;
            }
        }
        return $streak;
    }
    // Get longest streak
    public function getLongestStreak($user_id) {
        $sql = "SELECT date FROM workouts WHERE user_id = ? ORDER BY date DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$user_id]);
        $dates = $stmt->fetchAll(PDO::FETCH_COLUMN);
        if (!$dates) return 0;
        $longest = $current = 1;
        $prev = strtotime($dates[0]);
        for ($i = 1; $i < count($dates); $i++) {
            $curr = strtotime($dates[$i]);
            if ($prev - $curr == 86400) {
                $current++;
                $prev = $curr;
            } else {
                if ($current > $longest) $longest = $current;
                $current = 1;
                $prev = $curr;
            }
        }
        if ($current > $longest) $longest = $current;
        return $longest;
    }
}
?>
