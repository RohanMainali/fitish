<?php
// /includes/db.php
// Database connection using PDO
class Database {
    private $host = 'localhost';
    private $db_name = 'fitishh';
    private $username = 'root';
    private $password = '';
    private $conn;

    public function connect() {
        $this->conn = null;
        try {
            $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8";
            $this->conn = new PDO($dsn, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            die('Database Connection Error: ' . $e->getMessage());
        }
        return $this->conn;
    }
}
?>
