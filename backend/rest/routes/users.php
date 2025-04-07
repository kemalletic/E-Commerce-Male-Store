<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../services/UserService.php';

$userService = new UserService();

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        if (isset($_GET['id'])) {
            $user = $userService->getUserById($_GET['id']);
            if ($user) {
                echo json_encode($user);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'User not found']);
            }
        } else {
            echo json_encode($userService->getAllUsers());
        }
        break;

    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        if ($data) {
            $result = $userService->createUser($data);
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
                $result = $userService->updateUser($_GET['id'], $data);
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
            echo json_encode(['error' => 'User ID is required']);
        }
        break;

    case 'DELETE':
        if (isset($_GET['id'])) {
            $result = $userService->deleteUser($_GET['id']);
            if (isset($result['success']) && $result['success']) {
                echo json_encode($result);
            } else {
                http_response_code(400);
                echo json_encode($result);
            }
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'User ID is required']);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        break;
}
?> 