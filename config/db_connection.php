<?php
/**
 * Database connection configuration
 * Path: config/db_connection.php
 */

class Database {
    // Database credentials
    private $host = "localhost";
    private $db_name = "quotation_system";
    private $username = "root";  // Change to your MySQL username
    private $password = "";      // Change to your MySQL password
    public $conn;

    // Get database connection
    public function getConnection() {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            $this->conn->exec("set names utf8");
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            echo "Connection error: " . $e->getMessage();
        }

        return $this->conn;
    }
}