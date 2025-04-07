<?php
require_once 'config/database.php';

class EcommerceTest {
    private $database;
    private $conn;
    private $testData = [];

    public function __construct() {
        $this->database = new Database();
        $this->conn = $this->database->getConnection();
    }

    public function runAllTests() {
        echo "<h1>E-commerce Database Test Results</h1>";
        
        try {
            // Test Users
            $this->testUsers();
            
            // Test Categories
            $this->testCategories();
            
            // Test Products
            $this->testProducts();
            
            // Test Orders
            $this->testOrders();
            
            // Test Order Items
            $this->testOrderItems();
            
            // Test Cart
            $this->testCart();
            
            echo "<h2 style='color: green;'>All tests completed successfully!</h2>";
            
        } catch (PDOException $e) {
            echo "<h2 style='color: red;'>Error during testing: " . $e->getMessage() . "</h2>";
        } finally {
            $this->database->closeConnection();
        }
    }

    private function testUsers() {
        echo "<h2>Testing Users Table</h2>";
        
        $users = [
            [
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'password' => 'password123',
                'role' => 'user'
            ],
            [
                'name' => 'Jane Smith',
                'email' => 'jane@example.com',
                'password' => 'password123',
                'role' => 'user'
            ],
            [
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'password' => 'admin123',
                'role' => 'admin'
            ]
        ];

        foreach ($users as $user) {
            $stmt = $this->conn->prepare("INSERT INTO Users (Name, Email, Password, Role) VALUES (?, ?, ?, ?)");
            $stmt->execute([
                $user['name'],
                $user['email'],
                password_hash($user['password'], PASSWORD_DEFAULT),
                $user['role']
            ]);
            $userId = $this->conn->lastInsertId();
            $this->testData['users'][] = $userId;
            echo "Created user: {$user['name']} (ID: $userId)<br>";
        }
    }

    private function testCategories() {
        echo "<h2>Testing Categories Table</h2>";
        
        $categories = [
            [
                'name' => 'Electronics',
                'description' => 'Latest electronic devices and gadgets'
            ],
            [
                'name' => 'Clothing',
                'description' => 'Fashion and apparel'
            ],
            [
                'name' => 'Books',
                'description' => 'Books and educational materials'
            ]
        ];

        foreach ($categories as $category) {
            $stmt = $this->conn->prepare("INSERT INTO Categories (Name, Description) VALUES (?, ?)");
            $stmt->execute([$category['name'], $category['description']]);
            $categoryId = $this->conn->lastInsertId();
            $this->testData['categories'][] = $categoryId;
            echo "Created category: {$category['name']} (ID: $categoryId)<br>";
        }
    }

    private function testProducts() {
        echo "<h2>Testing Products Table</h2>";
        
        $products = [
            [
                'name' => 'Smartphone X',
                'description' => 'Latest smartphone with advanced features',
                'price' => 999.99,
                'stock' => 50,
                'category_id' => $this->testData['categories'][0] // Electronics
            ],
            [
                'name' => 'Designer T-Shirt',
                'description' => 'Comfortable cotton t-shirt',
                'price' => 29.99,
                'stock' => 100,
                'category_id' => $this->testData['categories'][1] // Clothing
            ],
            [
                'name' => 'Programming Guide',
                'description' => 'Complete guide to programming',
                'price' => 49.99,
                'stock' => 30,
                'category_id' => $this->testData['categories'][2] // Books
            ]
        ];

        foreach ($products as $product) {
            $stmt = $this->conn->prepare("INSERT INTO Products (Name, Description, Price, Stock, Category_ID) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([
                $product['name'],
                $product['description'],
                $product['price'],
                $product['stock'],
                $product['category_id']
            ]);
            $productId = $this->conn->lastInsertId();
            $this->testData['products'][] = $productId;
            echo "Created product: {$product['name']} (ID: $productId)<br>";
        }
    }

    private function testOrders() {
        echo "<h2>Testing Orders Table</h2>";
        
        $orders = [
            [
                'user_id' => $this->testData['users'][0],
                'total_price' => 1049.98,
                'status' => 'pending'
            ],
            [
                'user_id' => $this->testData['users'][1],
                'total_price' => 79.98,
                'status' => 'shipped'
            ]
        ];

        foreach ($orders as $order) {
            $stmt = $this->conn->prepare("INSERT INTO Orders (User_ID, Total_price, Status) VALUES (?, ?, ?)");
            $stmt->execute([
                $order['user_id'],
                $order['total_price'],
                $order['status']
            ]);
            $orderId = $this->conn->lastInsertId();
            $this->testData['orders'][] = $orderId;
            echo "Created order (ID: $orderId) for user ID: {$order['user_id']}<br>";
        }
    }

    private function testOrderItems() {
        echo "<h2>Testing Order Items Table</h2>";
        
        $orderItems = [
            [
                'order_id' => $this->testData['orders'][0],
                'product_id' => $this->testData['products'][0],
                'quantity' => 1,
                'price' => 999.99
            ],
            [
                'order_id' => $this->testData['orders'][1],
                'product_id' => $this->testData['products'][1],
                'quantity' => 2,
                'price' => 29.99
            ]
        ];

        foreach ($orderItems as $item) {
            $stmt = $this->conn->prepare("INSERT INTO Order_Items (Order_ID, Product_ID, Quantity, Price) VALUES (?, ?, ?, ?)");
            $stmt->execute([
                $item['order_id'],
                $item['product_id'],
                $item['quantity'],
                $item['price']
            ]);
            $itemId = $this->conn->lastInsertId();
            echo "Created order item (ID: $itemId) for order ID: {$item['order_id']}<br>";
        }
    }

    private function testCart() {
        echo "<h2>Testing Cart Table</h2>";
        
        $cartItems = [
            [
                'user_id' => $this->testData['users'][0],
                'product_id' => $this->testData['products'][2],
                'quantity' => 1
            ],
            [
                'user_id' => $this->testData['users'][1],
                'product_id' => $this->testData['products'][0],
                'quantity' => 1
            ]
        ];

        foreach ($cartItems as $item) {
            $stmt = $this->conn->prepare("INSERT INTO Cart (User_ID, Product_ID, Quantity) VALUES (?, ?, ?)");
            $stmt->execute([
                $item['user_id'],
                $item['product_id'],
                $item['quantity']
            ]);
            $cartId = $this->conn->lastInsertId();
            echo "Created cart item (ID: $cartId) for user ID: {$item['user_id']}<br>";
        }
    }
}

// Run the tests
$test = new EcommerceTest();
$test->runAllTests(); 