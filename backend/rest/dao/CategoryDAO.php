<?php
require_once __DIR__ . '/../config/database.php';

class CategoryDAO {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function create($name, $description) {
        $query = "INSERT INTO Categories (Name, Description) 
                  VALUES (:name, :description)";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            ':name' => $name,             ':description' => $description
        ]);
    }

    public function readAll() {
        $query = "SELECT * FROM Categories";
        $stmt = $this->conn->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function update($id, $name, $description) {
        $query = "UPDATE Categories SET 
                    Name = :name, Description = :description
                  WHERE ID = :id";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute(array_merge([
            ':id' => $id,
            ':name' => $name,             ':description' => $description
        ]));
    }

    public function delete($id) {
        $query = "DELETE FROM Categories WHERE ID = :id";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([':id' => $id]);
    }
}
?>