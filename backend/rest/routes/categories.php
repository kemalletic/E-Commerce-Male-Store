<?php
require_once __DIR__ . '/../services/CategoryService.php';

$categoryService = new CategoryService();

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        echo json_encode($categoryService->getAllCategories());
        break;

    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        echo json_encode($categoryService->createCategory($data));
        break;

    case 'PUT':
        parse_str($_SERVER['QUERY_STRING'], $params);
        $id = $params['id'] ?? null;
        $data = json_decode(file_get_contents('php://input'), true);
        echo json_encode($categoryService->updateCategory($id, $data));
        break;

    case 'DELETE':
        parse_str($_SERVER['QUERY_STRING'], $params);
        $id = $params['id'] ?? null;
        echo json_encode($categoryService->deleteCategory($id));
        break;

    default:
        http_response_code(405);
        echo json_encode(["message" => "Method not allowed"]);
}