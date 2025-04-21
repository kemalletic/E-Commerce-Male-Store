<?php
require_once __DIR__ . '/../dao/OrderItemDAO.php';

class OrderItemService {
    private $orderitemDAO;

    public function __construct() {
        $this->orderitemDAO = new OrderItemDAO();
    }

    public function getAllOrderItems() {
        return $this->orderitemDAO->readAll();
    }

    public function createOrderItem($data) {
        return $this->orderitemDAO->create(
            $data['order_id'],
            $data['product_id'],
            $data['quantity'],
            $data['price']
        );
    }

    public function updateOrderItem($id, $data) {
        return $this->orderitemDAO->update(
            $id,
            $data['order_id'],
            $data['product_id'],
            $data['quantity'],
            $data['price']
        );
    }

    public function deleteOrderItem($id) {
        return $this->orderitemDAO->delete($id);
    }
}
