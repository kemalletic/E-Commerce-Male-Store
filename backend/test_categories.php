<?php
require_once 'rest/config/database.php';

class CategoriesTest {
    private $database;
    private $conn;

    public function __construct() {
        $this->database = new Database();
        $this->conn = $this->database->getConnection();
    }

    public function testCategories() {
        if (!$this->database->isConnected()) {
            echo "Database connection failed!\n";
            return;
        }
        echo "Database connection successful!\n\n";

        try {
            echo "\n=== Testing Categories Table ===\n";
            
            // CREATE
            $stmt = $this->conn->prepare("INSERT INTO Categories (Name, Description) VALUES (?, ?)");
            $stmt->execute(['Electronics', 'Electronic devices and accessories']);
            $categoryId = $this->conn->lastInsertId();
            echo "Created category with ID: $categoryId\n";
            
            // READ
            $stmt = $this->conn->prepare("SELECT * FROM Categories WHERE ID = ?");
            $stmt->execute([$categoryId]);
            $category = $stmt->fetch();
            echo "Read category: " . json_encode($category) . "\n";
            
            // UPDATE
            $stmt = $this->conn->prepare("UPDATE Categories SET Name = ? WHERE ID = ?");
            $stmt->execute(['Updated Electronics', $categoryId]);
            echo "Updated category with ID: $categoryId\n";
            
            // DELETE
            $stmt = $this->conn->prepare("DELETE FROM Categories WHERE ID = ?");
            $stmt->execute([$categoryId]);
            echo "Deleted category with ID: $categoryId\n";
            
            echo "\nCategories testing completed successfully!\n";
        } catch (PDOException $e) {
            echo "Error during testing: " . $e->getMessage() . "\n";
        } finally {
            $this->database->closeConnection();
        }
    }
}

// Run the tests
$test = new CategoriesTest();
$test->testCategories(); 