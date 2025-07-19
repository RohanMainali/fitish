
<?php
// /includes/stats.class.php
require_once 'db.php';
class Stats {
    // Get average stats for all users
    public function getAverages() {
        $sql = "SELECT AVG(weight) as avg_weight, AVG(height) as avg_height, AVG(age) as avg_age, AVG(bmi) as avg_bmi, AVG(bmr) as avg_bmr, AVG(tdee) as avg_tdee FROM stats";
        $stmt = $this->conn->query($sql);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    private $conn;
    public function __construct() {
        $db = new Database();
        $this->conn = $db->connect();
    }
    // Create stats
    public function create($user_id, $weight, $height, $age, $gender, $bmi, $bmr, $tdee, $date) {
        $sql = "INSERT INTO stats (user_id, weight, height, age, gender, bmi, bmr, tdee, date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$user_id, $weight, $height, $age, $gender, $bmi, $bmr, $tdee, $date]);
    }
    // Get stats by user
    public function getByUser($user_id) {
        $sql = "SELECT * FROM stats WHERE user_id = ? ORDER BY date DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    // Get latest stats
    public function getLatest($user_id) {
        $sql = "SELECT * FROM stats WHERE user_id = ? ORDER BY date DESC LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$user_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    // Update stats
    public function update($id, $data) {
        $fields = [];
        $values = [];
        foreach ($data as $key => $value) {
            $fields[] = "$key = ?";
            $values[] = $value;
        }
        $values[] = $id;
        $sql = "UPDATE stats SET " . implode(", ", $fields) . " WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute($values);
    }
    // Delete stats
    public function delete($id) {
        $sql = "DELETE FROM stats WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$id]);
    }
}
?>
