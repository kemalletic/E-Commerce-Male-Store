<?php
require_once __DIR__ . '/../services/OrderItemService.php';

$orderitemService = new OrderItemService();

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        echo json_encode($orderitemService->getAllOrderItems());
        break;

    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        echo json_encode($orderitemService->createOrderItem($data));
        break;

    case 'PUT':
        parse_str($_SERVER['QUERY_STRING'], $params);
        $id = $params['id'] ?? null;
        $data = json_decode(file_get_contents('php://input'), true);
        echo json_encode($orderitemService->updateOrderItem($id, $data));
        break;

    case 'DELETE':
        parse_str($_SERVER['QUERY_STRING'], $params);
        $id = $params['id'] ?? null;
        echo json_encode($orderitemService->deleteOrderItem($id));
        break;

    default:
        http_response_code(405);
        echo json_encode(["message" => "Method not allowed"]);
}