<?php
// /includes/workout.class.php
require_once 'db.php';
class Workout {
    // Get site-wide workout stats
    public function getSiteStats($type = '', $date_from = '', $date_to = '') {
        $sql = "SELECT COUNT(*) as total, COUNT(DISTINCT date) as days, SUM(duration) as total_duration, SUM(distance) as total_distance, SUM(calories) as total_calories FROM workouts WHERE 1";
        $params = [];
        if ($type) {
            $sql .= " AND type = ?";
            $params[] = $type;
        }
        if ($date_from) {
            $sql .= " AND date >= ?";
            $params[] = $date_from;
        }
        if ($date_to) {
            $sql .= " AND date <= ?";
            $params[] = $date_to;
        }
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    // Get workouts by user with filters
    public function getFilteredByUser($user_id, $filters = []) {
        $sql = "SELECT * FROM workouts WHERE user_id = ?";
        $params = [$user_id];
        if (!empty($filters['type'])) {
            $sql .= " AND type = ?";
            $params[] = $filters['type'];
        }
        if (!empty($filters['date_from'])) {
            $sql .= " AND date >= ?";
            $params[] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $sql .= " AND date <= ?";
            $params[] = $filters['date_to'];
        }
        if (!empty($filters['calories_min'])) {
            $sql .= " AND calories >= ?";
            $params[] = $filters['calories_min'];
        }
        if (!empty($filters['calories_max'])) {
            $sql .= " AND calories <= ?";
            $params[] = $filters['calories_max'];
        }
        $sql .= " ORDER BY date DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public $conn;
    public function __construct() {
        $db = new Database();
        $this->conn = $db->connect();
    }
    // Create workout
    public function create($user_id, $type, $duration, $distance, $calories, $met, $date) {
        $sql = "INSERT INTO workouts (user_id, type, duration, distance, calories, met, date) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$user_id, $type, $duration, $distance, $calories, $met, $date]);
    }
    // Get workouts by user
    public function getByUser($user_id) {
        $sql = "SELECT * FROM workouts WHERE user_id = ? ORDER BY date DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    // Get workout by ID
    public function getById($id) {
        $sql = "SELECT * FROM workouts WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    // Update workout
    public function update($id, $data) {
        $fields = [];
        $values = [];
        foreach ($data as $key => $value) {
            $fields[] = "$key = ?";
            $values[] = $value;
        }
        $values[] = $id;
        $sql = "UPDATE workouts SET " . implode(", ", $fields) . " WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute($values);
    }
    // Delete workout
    public function delete($id) {
        $sql = "DELETE FROM workouts WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$id]);
    }
    
    // Get total workout count
    public function getTotalCount() {
        $sql = "SELECT COUNT(*) as count FROM workouts";
        $stmt = $this->conn->query($sql);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'];
    }
    
    // Get today's workout count
    public function getTodayCount() {
        $sql = "SELECT COUNT(*) as count FROM workouts WHERE date = CURDATE()";
        $stmt = $this->conn->query($sql);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'];
    }
}
?>
