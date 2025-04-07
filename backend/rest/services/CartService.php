<?php
require_once __DIR__ . '/../dao/CartDAO.php';

class CartService {
    private $cartDAO;

    public function __construct() {
        $this->cartDAO = new CartDAO();
    }

    public function getAllCartItems() {
        try {
            $items = $this->cartDAO->readAll();
            return [
                "success" => true,
                "data" => $items
            ];
        } catch (Exception $e) {
            return [
                "success" => false,
                "error" => $e->getMessage()
            ];
        }
    }

    public function getCartItemById($id) {
        try {
            $item = $this->cartDAO->readById($id);
            if (!$item) {
                return [
                    "success" => false,
                    "error" => "Cart item not found"
                ];
            }
            return [
                "success" => true,
                "data" => $item
            ];
        } catch (Exception $e) {
            return [
                "success" => false,
                "error" => $e->getMessage()
            ];
        }
    }

    public function createCartItem($data) {
        try {
            if (!isset($data['user_id']) || !isset($data['product_id']) || !isset($data['quantity'])) {
                return [
                    "success" => false,
                    "error" => "Missing required fields"
                ];
            }

            $id = $this->cartDAO->create(
                $data['user_id'],
                $data['product_id'],
                $data['quantity']
            );

            return [
                "success" => true,
                "message" => "Cart item created successfully",
                "id" => $id
            ];
        } catch (Exception $e) {
            return [
                "success" => false,
                "error" => $e->getMessage()
            ];
        }
    }

    public function updateCartItem($id, $data) {
        try {
            if (!isset($data['quantity'])) {
                return [
                    "success" => false,
                    "error" => "Quantity is required"
                ];
            }

            $success = $this->cartDAO->update(
                $id,
                $data['quantity']
            );

            if (!$success) {
                return [
                    "success" => false,
                    "error" => "Cart item not found"
                ];
            }

            return [
                "success" => true,
                "message" => "Cart item updated successfully"
            ];
        } catch (Exception $e) {
            return [
                "success" => false,
                "error" => $e->getMessage()
            ];
        }
    }

    public function deleteCartItem($id) {
        try {
            $success = $this->cartDAO->delete($id);
            if (!$success) {
                return [
                    "success" => false,
                    "error" => "Cart item not found"
                ];
            }
            return [
                "success" => true,
                "message" => "Cart item deleted successfully"
            ];
        } catch (Exception $e) {
            return [
                "success" => false,
                "error" => $e->getMessage()
            ];
        }
    }
}
?> 