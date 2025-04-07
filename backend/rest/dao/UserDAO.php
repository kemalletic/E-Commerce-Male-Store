<?php
require_once __DIR__ . '/../config/database.php';

class UserDAO {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function create($name, $email, $password, $role) {
        try {
            $query = "INSERT INTO Users (Name, Email, Password, Role) 
                      VALUES (:name, :email, :password, :role)";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                ':name' => $name,
                ':email' => $email,
                ':password' => password_hash($password, PASSWORD_DEFAULT),
                ':role' => $role
            ]);
            return $this->conn->lastInsertId();
        } catch (PDOException $e) {
            throw new Exception("Error creating user: " . $e->getMessage());
        }
    }

    public function readAll() {
        try {
            $query = "SELECT * FROM Users";
            $stmt = $this->conn->query($query);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Error reading users: " . $e->getMessage());
        }
    }

    public function readById($id) {
        try {
            $query = "SELECT * FROM Users WHERE ID = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Error reading user: " . $e->getMessage());
        }
    }

    public function update($id, $name, $email, $password = null, $role) {
        try {
            $query = "UPDATE Users SET 
                        Name = :name, 
                        Email = :email, 
                        Role = :role";
            
            if ($password) {
                $query .= ", Password = :password";
            }
            
            $query .= " WHERE ID = :id";
            
            $params = [
                ':id' => $id,
                ':name' => $name,
                ':email' => $email,
                ':role' => $role
            ];
            
            if ($password) {
                $params[':password'] = password_hash($password, PASSWORD_DEFAULT);
            }
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            throw new Exception("Error updating user: " . $e->getMessage());
        }
    }

    public function delete($id) {
        try {
            $query = "DELETE FROM Users WHERE ID = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':id' => $id]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            throw new Exception("Error deleting user: " . $e->getMessage());
        }
    }
}
?> 