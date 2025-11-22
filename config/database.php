<?php
// admin/config/database.php
class Database {
    private $host = 'localhost';
    private $db_name = 'orphanfare';
    private $username = 'root';
    private $password = '';
    private $conn;

    public function getConnection() {
        if ($this->conn !== null) {
            return $this->conn;
        }
        
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4",
                $this->username,
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch(PDOException $exception) {
            die("Database connection failed: " . $exception->getMessage());
        }
        
        return $this->conn;
    }
}

// Initialize database connection
try {
    $database = new Database();
    $pdo = $database->getConnection();
} catch (Exception $e) {
    die("Database initialization failed: " . $e->getMessage());
}
?>