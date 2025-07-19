<?php
// /includes/leaderboard.class.php
require_once 'db.php';
class Leaderboard {
    private $conn;
    public function __construct() {
        $db = new Database();
        $this->conn = $db->connect();
    }
    // Get top 10 users by total workouts this week
    public function getWeeklyLeaderboard() {
        $sql = "SELECT u.username, COUNT(w.id) as workouts FROM users u JOIN workouts w ON u.id = w.user_id WHERE YEARWEEK(w.date, 1) = YEARWEEK(CURDATE(), 1) GROUP BY u.id ORDER BY workouts DESC LIMIT 10";
        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
