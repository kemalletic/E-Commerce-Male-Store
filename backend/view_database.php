<?php
require_once 'config/database.php';

class DatabaseViewer {
    private $database;
    private $conn;

    public function __construct() {
        $this->database = new Database();
        $this->conn = $this->database->getConnection();
    }

    public function viewAllTables() {
        $tables = ['Users', 'Categories', 'Products', 'Orders', 'Order_Items', 'Cart'];
        
        echo "<h1>Database Contents</h1>";
        
        foreach ($tables as $table) {
            $this->viewTable($table);
        }
    }

    private function viewTable($tableName) {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM $tableName");
            $stmt->execute();
            $results = $stmt->fetchAll();

            echo "<h2>Table: $tableName</h2>";
            echo "<table border='1' cellpadding='5'>";
            
            // Headers
            if (!empty($results)) {
                echo "<tr>";
                foreach (array_keys($results[0]) as $header) {
                    echo "<th>$header</th>";
                }
                echo "</tr>";
                
                // Data
                foreach ($results as $row) {
                    echo "<tr>";
                    foreach ($row as $value) {
                        echo "<td>" . htmlspecialchars($value) . "</td>";
                    }
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='100%'>No data in table</td></tr>";
            }
            
            echo "</table><br><br>";
        } catch (PDOException $e) {
            echo "<p>Error viewing table $tableName: " . $e->getMessage() . "</p>";
        }
    }
}

// Create viewer and display tables
$viewer = new DatabaseViewer();
$viewer->viewAllTables(); 