<?php
require_once __DIR__ . '/../config/database.php';

class OrderItemDAO {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function create($order_id, $product_id, $quantity, $price) {
        $query = "INSERT INTO Order_Items (Order_ID, Product_ID, Quantity, Price) 
                  VALUES (:order_id, :product_id, :quantity, :price)";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            ':order_id' => $order_id,             ':product_id' => $product_id,             ':quantity' => $quantity,             ':price' => $price
        ]);
    }

    public function readAll() {
        $query = "SELECT * FROM Order_Items";
        $stmt = $this->conn->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function update($id, $order_id, $product_id, $quantity, $price) {
        $query = "UPDATE Order_Items SET 
                    Order_ID = :order_id, Product_ID = :product_id, Quantity = :quantity, Price = :price
                  WHERE ID = :id";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute(array_merge([
            ':id' => $id,
            ':order_id' => $order_id,             ':product_id' => $product_id,             ':quantity' => $quantity,             ':price' => $price
        ]));
    }

    public function delete($id) {
        $query = "DELETE FROM Order_Items WHERE ID = :id";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([':id' => $id]);
    }
}
?>