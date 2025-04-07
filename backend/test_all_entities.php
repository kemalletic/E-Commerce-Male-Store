<?php
require_once 'rest/config/database.php';

class DatabaseTest {
    private $database;
    private $conn;

    public function __construct() {
        $this->database = new Database();
        $this->conn = $this->database->getConnection();
    }

    public function testAll() {
        if (!$this->database->isConnected()) {
            echo "Database connection failed!\n";
            return;
        }
        echo "Database connection successful!\n\n";

        try {
            // Test Users
            $this->testUsers();
            
            // Test Categories
            $this->testCategories();
            
            // Test Products
            $this->testProducts();
            
            // Test Orders
            $this->testOrders();
            
            echo "\nAll tests completed successfully!\n";
        } catch (PDOException $e) {
            echo "Error during testing: " . $e->getMessage() . "\n";
        } finally {
            $this->database->closeConnection();
        }
    }

    private function testUsers() {
        echo "\n=== Testing Users Table ===\n";
        
        // CREATE
        $stmt = $this->conn->prepare("INSERT INTO Users (Name, Email, Password, Role) VALUES (?, ?, ?, ?)");
        $stmt->execute(['Test User', 'test@example.com', password_hash('password123', PASSWORD_DEFAULT), 'user']);
        $userId = $this->conn->lastInsertId();
        echo "Created user with ID: $userId\n";
        
        // READ
        $stmt = $this->conn->prepare("SELECT * FROM Users WHERE ID = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        echo "Read user: " . json_encode($user) . "\n";
        
        // UPDATE
        $stmt = $this->conn->prepare("UPDATE Users SET Name = ? WHERE ID = ?");
        $stmt->execute(['Updated Test User', $userId]);
        echo "Updated user with ID: $userId\n";
        
        // DELETE
        $stmt = $this->conn->prepare("DELETE FROM Users WHERE ID = ?");
        $stmt->execute([$userId]);
        echo "Deleted user with ID: $userId\n";
    }

    private function testCategories() {
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
        
        return $categoryId; // Return for product testing
    }

    private function testProducts() {
        echo "\n=== Testing Products Table ===\n";
        
        // Get a category ID first
        $categoryId = $this->testCategories();
        
        // CREATE
        $stmt = $this->conn->prepare("INSERT INTO Products (Name, Description, Price, Stock, Category_ID) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute(['Test Product', 'A test product description', 99.99, 100, $categoryId]);
        $productId = $this->conn->lastInsertId();
        echo "Created product with ID: $productId\n";
        
        // READ
        $stmt = $this->conn->prepare("SELECT * FROM Products WHERE ID = ?");
        $stmt->execute([$productId]);
        $product = $stmt->fetch();
        echo "Read product: " . json_encode($product) . "\n";
        
        // UPDATE
        $stmt = $this->conn->prepare("UPDATE Products SET Price = ? WHERE ID = ?");
        $stmt->execute([149.99, $productId]);
        echo "Updated product with ID: $productId\n";
        
        // DELETE
        $stmt = $this->conn->prepare("DELETE FROM Products WHERE ID = ?");
        $stmt->execute([$productId]);
        echo "Deleted product with ID: $productId\n";
    }

    private function testOrders() {
        echo "\n=== Testing Orders Table ===\n";
        
        // Create a test user first
        $stmt = $this->conn->prepare("INSERT INTO Users (Name, Email, Password, Role) VALUES (?, ?, ?, ?)");
        $stmt->execute(['Order Test User', 'order@example.com', password_hash('password123', PASSWORD_DEFAULT), 'user']);
        $userId = $this->conn->lastInsertId();
        
        // CREATE Order
        $stmt = $this->conn->prepare("INSERT INTO Orders (User_ID, Total_price, Status) VALUES (?, ?, ?)");
        $stmt->execute([$userId, 199.99, 'pending']);
        $orderId = $this->conn->lastInsertId();
        echo "Created order with ID: $orderId\n";
        
        // READ
        $stmt = $this->conn->prepare("SELECT * FROM Orders WHERE ID = ?");
        $stmt->execute([$orderId]);
        $order = $stmt->fetch();
        echo "Read order: " . json_encode($order) . "\n";
        
        // UPDATE
        $stmt = $this->conn->prepare("UPDATE Orders SET Status = ? WHERE ID = ?");
        $stmt->execute(['shipped', $orderId]);
        echo "Updated order with ID: $orderId\n";
        
        // DELETE
        $stmt = $this->conn->prepare("DELETE FROM Orders WHERE ID = ?");
        $stmt->execute([$orderId]);
        echo "Deleted order with ID: $orderId\n";
        
        // Clean up test user
        $stmt = $this->conn->prepare("DELETE FROM Users WHERE ID = ?");
        $stmt->execute([$userId]);
    }
}

// Run the tests
$test = new DatabaseTest();
$test->testAll(); 