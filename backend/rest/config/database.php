<?php
header('Content-Type: application/json; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', 1);

class Database {
    private $connection;
    private $host = "localhost";
    private $db   = "ecommerce"; // Changed to match your SQL file
    private $user = "root";
    private $pass = ""; // Default XAMPP password is empty

    public function __construct() {
        try {
            $this->connection = new PDO("mysql:host=$this->host;dbname=$this->db;charset=utf8", $this->user, $this->pass);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo json_encode([
                "success" => false,
                "message" => "Connection failed: " . $e->getMessage()
            ]);
            exit;
        }
    }

    public function getConnection() {
        return $this->connection;
    }
    
    public function isConnected() {
        return $this->connection !== null;
    }
    
    public function closeConnection() {
        $this->connection = null;
    }
}
