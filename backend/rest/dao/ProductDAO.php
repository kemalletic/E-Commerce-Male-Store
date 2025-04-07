<?php
require_once __DIR__ . '/../config/database.php';

class ProductDAO {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function create($name, $description, $price, $stock, $category_id) {
        $query = "INSERT INTO Products (Name, Description, Price, Stock, Category_ID) 
                  VALUES (:name, :description, :price, :stock, :category_id)";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            ':name' => $name,
            ':description' => $description,
            ':price' => $price,
            ':stock' => $stock,
            ':category_id' => $category_id
        ]);
    }

    public function readAll() {
        $query = "SELECT * FROM Products";
        $stmt = $this->conn->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function readById($id) {
        $query = "SELECT * FROM Products WHERE ID = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function update($id, $name, $description, $price, $stock, $category_id) {
        $query = "UPDATE Products SET 
                    Name = :name,
                    Description = :description,
                    Price = :price,
                    Stock = :stock,
                    Category_ID = :category_id
                  WHERE ID = :id";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            ':id' => $id,
            ':name' => $name,
            ':description' => $description,
            ':price' => $price,
            ':stock' => $stock,
            ':category_id' => $category_id
        ]);
    }

    public function delete($id) {
        $query = "DELETE FROM Products WHERE ID = :id";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([':id' => $id]);
    }
}
?>