<?php
require_once __DIR__ . '/../dao/OrderDAO.php';

class OrderService {
    private $orderDAO;

    public function __construct() {
        $this->orderDAO = new OrderDAO();
    }

    public function getAllOrders() {
        try {
            $orders = $this->orderDAO->readAll();
            return ['success' => true, 'data' => $orders];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function getOrderById($id) {
        try {
            $order = $this->orderDAO->readById($id);
            if ($order) {
                return ['success' => true, 'data' => $order];
            }
            return ['success' => false, 'error' => 'Order not found'];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function createOrder($data) {
        try {
            if (!isset($data['user_id']) || !isset($data['total_amount']) || !isset($data['status'])) {
                return ['success' => false, 'error' => 'Missing required fields'];
            }
            
            $orderId = $this->orderDAO->create(
                $data['user_id'],
                $data['total_amount'],
                $data['status'],
                $data['shipping_address'] ?? null,
                $data['payment_method'] ?? null
            );
            
            return ['success' => true, 'message' => 'Order created successfully', 'id' => $orderId];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function updateOrder($id, $data) {
        try {
            if (!isset($data['status'])) {
                return ['success' => false, 'error' => 'Missing required fields'];
            }
            
            $result = $this->orderDAO->update(
                $id,
                $data['status'],
                $data['shipping_address'] ?? null,
                $data['payment_method'] ?? null
            );
            
            if ($result) {
                return ['success' => true, 'message' => 'Order updated successfully'];
            }
            return ['success' => false, 'error' => 'Order not found'];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function deleteOrder($id) {
        try {
            $result = $this->orderDAO->delete($id);
            if ($result) {
                return ['success' => true, 'message' => 'Order deleted successfully'];
            }
            return ['success' => false, 'error' => 'Order not found'];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
?> 