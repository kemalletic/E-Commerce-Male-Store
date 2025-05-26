<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../services/OrderService.php';

$orderService = new OrderService();

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        if (isset($_GET['id'])) {
            $order = $orderService->getOrderById($_GET['id']);
            if ($order) {
                echo json_encode($order);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Order not found']);
            }
        } else {
            echo json_encode($orderService->getAllOrders());
        }
        break;

    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        if ($data) {
            $result = $orderService->createOrder($data);
            if (isset($result['success']) && $result['success']) {
                http_response_code(201);
                echo json_encode($result);
            } else {
                http_response_code(400);
                echo json_encode($result);
            }
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid JSON data']);
        }
        break;

    case 'PUT':
        if (isset($_GET['id'])) {
            $data = json_decode(file_get_contents('php://input'), true);
            if ($data) {
                $result = $orderService->updateOrder($_GET['id'], $data);
                if (isset($result['success']) && $result['success']) {
                    echo json_encode($result);
                } else {
                    http_response_code(400);
                    echo json_encode($result);
                }
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid JSON data']);
            }
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Order ID is required']);
        }
        break;

    case 'DELETE':
        if (isset($_GET['id'])) {
            $result = $orderService->deleteOrder($_GET['id']);
            if (isset($result['success']) && $result['success']) {
                echo json_encode($result);
            } else {
                http_response_code(400);
                echo json_encode($result);
            }
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Order ID is required']);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        break;
}
?> 