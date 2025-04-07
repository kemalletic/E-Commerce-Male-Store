<?php
// Set proper character encoding and headers
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Error handling
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Get the request URI
$request_uri = $_SERVER['REQUEST_URI'];
$path = parse_url($request_uri, PHP_URL_PATH);
$path = trim($path, '/');

// Extract the resource from the path
$parts = explode('/', $path);
$resource = end($parts);

// Map resources to their respective route files
$routes = [
    'users' => 'users.php',
    'products' => 'products_final.php',
    'categories' => 'categories.php',
    'orders' => 'orders.php',
    'orderitem' => 'OrderItem.php',
    'cart' => 'cart.php',
    'test' => 'test.php'
];

// Check if the resource exists in our routes
if (isset($routes[$resource])) {
    $route_file = __DIR__ . '/routes/' . $routes[$resource];
    if (file_exists($route_file)) {
        require_once $route_file;
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Route not found']);
    }
} else {
    http_response_code(404);
    echo json_encode(['error' => 'Resource not found']);
}

