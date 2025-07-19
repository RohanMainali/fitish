
<?php
// /includes/goal.class.php
require_once 'db.php';
class Goal {
    // Get site-wide goal stats
    public function getSiteStats() {
        $sql = "SELECT COUNT(*) as total, SUM(is_achieved=1) as achieved, SUM(is_achieved=0) as active FROM goals";
        $stmt = $this->conn->query($sql);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    // Get goals by user with filters
    public function getFilteredByUser($user_id, $filters = []) {
        $sql = "SELECT * FROM goals WHERE user_id = ?";
        $params = [$user_id];
        if (!empty($filters['goal_type'])) {
            $sql .= " AND goal_type = ?";
            $params[] = $filters['goal_type'];
        }
        if (!empty($filters['status'])) {
            if ($filters['status'] === 'active') {
                $sql .= " AND is_achieved = 0 AND deadline >= CURDATE()";
            } elseif ($filters['status'] === 'completed') {
                $sql .= " AND is_achieved = 1";
            } elseif ($filters['status'] === 'overdue') {
                $sql .= " AND is_achieved = 0 AND deadline < CURDATE()";
            }
        }
        $sql .= " ORDER BY deadline ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    private $conn;
    public function __construct() {
        $db = new Database();
        $this->conn = $db->connect();
    }
    // Create goal
    public function create($user_id, $goal_type, $target_value, $current_value, $deadline, $is_achieved = 0) {
        $sql = "INSERT INTO goals (user_id, goal_type, target_value, current_value, deadline, is_achieved) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$user_id, $goal_type, $target_value, $current_value, $deadline, $is_achieved]);
    }
    // Get goals by user
    public function getByUser($user_id) {
        $sql = "SELECT * FROM goals WHERE user_id = ? ORDER BY deadline ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    // Get goal by ID
    public function getById($id) {
        $sql = "SELECT * FROM goals WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    // Update goal
    public function update($id, $data) {
        $fields = [];
        $values = [];
        foreach ($data as $key => $value) {
            $fields[] = "$key = ?";
            $values[] = $value;
        }
        $values[] = $id;
        $sql = "UPDATE goals SET " . implode(", ", $fields) . " WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute($values);
    }
    // Delete goal
    public function delete($id) {
        $sql = "DELETE FROM goals WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$id]);
    }
}
?>
