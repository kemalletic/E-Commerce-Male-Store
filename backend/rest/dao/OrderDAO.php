<?php
require_once __DIR__ . '/../config/database.php';

class OrderDAO {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function create($user_id, $total_amount, $status) {
        try {
            $query = "INSERT INTO Orders (User_ID, Total_price, Status) 
                      VALUES (:user_id, :total_amount, :status)";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                ':user_id' => $user_id,
                ':total_amount' => $total_amount,
                ':status' => $status
            ]);
            return $this->conn->lastInsertId();
        } catch (PDOException $e) {
            throw new Exception("Error creating order: " . $e->getMessage());
        }
    }

    public function readAll() {
        try {
            $query = "SELECT * FROM Orders";
            $stmt = $this->conn->query($query);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Error reading orders: " . $e->getMessage());
        }
    }

    public function readById($id) {
        try {
            $query = "SELECT * FROM Orders WHERE ID = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Error reading order: " . $e->getMessage());
        }
    }

    public function update($id, $status) {
        try {
            $query = "UPDATE Orders SET Status = :status WHERE ID = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                ':id' => $id,
                ':status' => $status
            ]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            throw new Exception("Error updating order: " . $e->getMessage());
        }
    }

    public function delete($id) {
        try {
            $query = "DELETE FROM Orders WHERE ID = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':id' => $id]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            throw new Exception("Error deleting order: " . $e->getMessage());
        }
    }
}
?> 