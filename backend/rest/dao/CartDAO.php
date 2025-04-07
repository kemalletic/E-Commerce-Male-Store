<?php
require_once __DIR__ . '/../config/database.php';

class CartDAO {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function create($user_id, $product_id, $quantity) {
        try {
            $query = "INSERT INTO Cart (User_ID, Product_ID, Quantity) 
                      VALUES (:user_id, :product_id, :quantity)";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                ':user_id' => $user_id,
                ':product_id' => $product_id,
                ':quantity' => $quantity
            ]);
            return $this->conn->lastInsertId();
        } catch (PDOException $e) {
            throw new Exception("Error creating cart item: " . $e->getMessage());
        }
    }

    public function readAll() {
        try {
            $query = "SELECT * FROM Cart";
            $stmt = $this->conn->query($query);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Error reading cart items: " . $e->getMessage());
        }
    }

    public function readById($id) {
        try {
            $query = "SELECT * FROM Cart WHERE ID = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Error reading cart item: " . $e->getMessage());
        }
    }

    public function update($id, $quantity) {
        try {
            $query = "UPDATE Cart SET Quantity = :quantity WHERE ID = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                ':id' => $id,
                ':quantity' => $quantity
            ]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            throw new Exception("Error updating cart item: " . $e->getMessage());
        }
    }

    public function delete($id) {
        try {
            $query = "DELETE FROM Cart WHERE ID = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':id' => $id]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            throw new Exception("Error deleting cart item: " . $e->getMessage());
        }
    }
}
?> 