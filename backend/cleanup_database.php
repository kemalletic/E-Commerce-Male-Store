<?php
require_once 'config/database.php';

class DatabaseCleanup {
    private $database;
    private $conn;

    public function __construct() {
        $this->database = new Database();
        $this->conn = $this->database->getConnection();
    }

    public function cleanupAll() {
        echo "<h1>Database Cleanup</h1>";
        
        try {
            // Disable foreign key checks temporarily
            $this->conn->exec("SET FOREIGN_KEY_CHECKS = 0");
            
            // Clean up in reverse order of dependencies
            $this->cleanupTable("Cart");
            $this->cleanupTable("Order_Items");
            $this->cleanupTable("Orders");
            $this->cleanupTable("Products");
            $this->cleanupTable("Categories");
            $this->cleanupTable("Users");
            
            // Re-enable foreign key checks
            $this->conn->exec("SET FOREIGN_KEY_CHECKS = 1");
            
            echo "<h2 style='color: green;'>Database cleanup completed successfully!</h2>";
            
        } catch (PDOException $e) {
            echo "<h2 style='color: red;'>Error during cleanup: " . $e->getMessage() . "</h2>";
        } finally {
            $this->database->closeConnection();
        }
    }
    
    public function cleanupSpecificTable($tableName) {
        echo "<h1>Cleaning up table: $tableName</h1>";
        
        try {
            // Disable foreign key checks temporarily
            $this->conn->exec("SET FOREIGN_KEY_CHECKS = 0");
            
            $this->cleanupTable($tableName);
            
            // Re-enable foreign key checks
            $this->conn->exec("SET FOREIGN_KEY_CHECKS = 1");
            
            echo "<h2 style='color: green;'>Table cleanup completed successfully!</h2>";
            
        } catch (PDOException $e) {
            echo "<h2 style='color: red;'>Error during cleanup: " . $e->getMessage() . "</h2>";
        } finally {
            $this->database->closeConnection();
        }
    }

    private function cleanupTable($tableName) {
        try {
            // Get count before deletion
            $stmt = $this->conn->prepare("SELECT COUNT(*) FROM $tableName");
            $stmt->execute();
            $countBefore = $stmt->fetchColumn();
            
            // Delete all records
            $stmt = $this->conn->prepare("TRUNCATE TABLE $tableName");
            $stmt->execute();
            
            echo "Cleaned up table '$tableName': Deleted $countBefore records<br>";
        } catch (PDOException $e) {
            echo "Error cleaning up table '$tableName': " . $e->getMessage() . "<br>";
            throw $e;
        }
    }
}

// Check if a specific table was requested
$tableName = isset($_GET['table']) ? $_GET['table'] : null;

// Create cleanup instance
$cleanup = new DatabaseCleanup();

// Run the appropriate cleanup
if ($tableName) {
    $cleanup->cleanupSpecificTable($tableName);
} else {
    $cleanup->cleanupAll();
} 