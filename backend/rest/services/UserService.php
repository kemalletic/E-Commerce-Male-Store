<?php
require_once __DIR__ . '/../dao/UserDAO.php';

class UserService {
    private $userDAO;

    public function __construct() {
        $this->userDAO = new UserDAO();
    }

    public function getAllUsers() {
        try {
            $users = $this->userDAO->readAll();
            return ['success' => true, 'data' => $users];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function getUserById($id) {
        try {
            $user = $this->userDAO->readById($id);
            if ($user) {
                return ['success' => true, 'data' => $user];
            }
            return ['success' => false, 'error' => 'User not found'];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function createUser($data) {
        try {
            if (!isset($data['username']) || !isset($data['email']) || !isset($data['password'])) {
                return ['success' => false, 'error' => 'Missing required fields'];
            }
            
            $userId = $this->userDAO->create(
                $data['username'],
                $data['email'],
                $data['password'],
                $data['role'] ?? 'user'
            );
            
            return ['success' => true, 'message' => 'User created successfully', 'id' => $userId];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function updateUser($id, $data) {
        try {
            if (!isset($data['username']) || !isset($data['email'])) {
                return ['success' => false, 'error' => 'Missing required fields'];
            }
            
            $result = $this->userDAO->update(
                $id,
                $data['username'],
                $data['email'],
                $data['password'] ?? null,
                $data['role'] ?? 'user'
            );
            
            if ($result) {
                return ['success' => true, 'message' => 'User updated successfully'];
            }
            return ['success' => false, 'error' => 'User not found'];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function deleteUser($id) {
        try {
            $result = $this->userDAO->delete($id);
            if ($result) {
                return ['success' => true, 'message' => 'User deleted successfully'];
            }
            return ['success' => false, 'error' => 'User not found'];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
?> 