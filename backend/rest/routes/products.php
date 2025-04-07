<?php
require_once __DIR__ . '/../services/ProductService.php';

$productService = new ProductService();

$method = $_SERVER['REQUEST_METHOD'];

// Get ID from URL
$id = null;
if (isset($_GET['id'])) {
    $id = $_GET['id'];
}

// Debug information
error_log("Method: " . $method);
error_log("ID: " . ($id ?? 'null'));
error_log("Query String: " . $_SERVER['QUERY_STRING']);

switch ($method) {
    case 'GET':
        echo json_encode($productService->getAllProducts());
        break;

    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        echo json_encode($productService->createProduct($data));
        break;

    case 'PUT':
        parse_str($_SERVER['QUERY_STRING'], $params);
        $id = $params['id'] ?? null;
        $data = json_decode(file_get_contents('php://input'), true);
        echo json_encode($productService->updateProduct($id, $data));
        break;

    case 'DELETE':
        parse_str($_SERVER['QUERY_STRING'], $params);
        $id = $params['id'] ?? null;
        echo json_encode($productService->deleteProduct($id));
        break;

    default:
        http_response_code(405);
        echo json_encode(["message" => "Method not allowed"]);
}
