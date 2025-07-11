
<?php
// /includes/badge.class.php
require_once 'db.php';
class Badge {
    // Get total badge count
    public function getTotalCount() {
        $sql = "SELECT COUNT(*) as total FROM badges";
        $stmt = $this->conn->query($sql);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    // Get total user badges awarded
    public function getUserBadgeCount() {
        $sql = "SELECT COUNT(*) as total FROM user_badges";
        $stmt = $this->conn->query($sql);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    private $conn;
    public function __construct() {
        $db = new Database();
        $this->conn = $db->connect();
    }
    // Award badge to user
    public function award($user_id, $badge_id) {
        $sql = "INSERT INTO user_badges (user_id, badge_id) VALUES (?, ?)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$user_id, $badge_id]);
    }
    // Get badges for user
    public function getByUser($user_id) {
        $sql = "SELECT b.* FROM badges b JOIN user_badges ub ON b.id = ub.badge_id WHERE ub.user_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    // Check if user already has badge
    public function hasBadge($user_id, $badge_id) {
        $sql = "SELECT id FROM user_badges WHERE user_id = ? AND badge_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$user_id, $badge_id]);
        return $stmt->fetch() ? true : false;
    }
    // Get all badges
    public function getAll() {
        $sql = "SELECT * FROM badges";
        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
