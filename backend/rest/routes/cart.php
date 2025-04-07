<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../services/CartService.php';

$cartService = new CartService();

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $id = $_GET['id'] ?? null;
        if ($id) {
            echo json_encode($cartService->getCartItemById($id));
        } else {
            echo json_encode($cartService->getAllCartItems());
        }
        break;
        
    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        if ($data === null) {
            http_response_code(400);
            echo json_encode([
                "success" => false,
                "error" => "Invalid JSON data"
            ]);
            break;
        }
        echo json_encode($cartService->createCartItem($data));
        break;
        
    case 'PUT':
        parse_str($_SERVER['QUERY_STRING'], $params);
        $id = $params['id'] ?? null;
        if (!$id) {
            http_response_code(400);
            echo json_encode([
                "success" => false,
                "error" => "Cart item ID is required"
            ]);
            break;
        }
        $data = json_decode(file_get_contents('php://input'), true);
        if ($data === null) {
            http_response_code(400);
            echo json_encode([
                "success" => false,
                "error" => "Invalid JSON data"
            ]);
            break;
        }
        echo json_encode($cartService->updateCartItem($id, $data));
        break;
        
    case 'DELETE':
        parse_str($_SERVER['QUERY_STRING'], $params);
        $id = $params['id'] ?? null;
        if (!$id) {
            http_response_code(400);
            echo json_encode([
                "success" => false,
                "error" => "Cart item ID is required"
            ]);
            break;
        }
        echo json_encode($cartService->deleteCartItem($id));
        break;
        
    default:
        http_response_code(405);
        echo json_encode([
            "success" => false,
            "error" => "Method not allowed"
        ]);
}
?> 