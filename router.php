<?php
require 'vendor/autoload.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

use flight\Engine;

$app = new Engine();

// Map helper functions to FlightPHP engine
$app->map('isApiRequest', function() {
    $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
    return strpos($accept, 'application/json') !== false || 
           strpos($contentType, 'application/json') !== false;
});

$app->map('getBearerToken', function() {
    error_log("Debug: Starting token extraction");
    
    // Get all headers
    $headers = getallheaders();
    error_log("Debug: All headers: " . print_r($headers, true));
    
    // Check Authorization header
    if (isset($headers['Authorization'])) {
        error_log("Debug: Found Authorization header: " . $headers['Authorization']);
        if (preg_match('/Bearer\s(\S+)/', $headers['Authorization'], $matches)) {
            $token = $matches[1];
            error_log("Debug: Successfully extracted token from Authorization header");
            return $token;
        }
    }
    
    // If no Authorization header, try to get from Cookie
    if (isset($_SERVER['HTTP_COOKIE'])) {
        error_log("Debug: Found Cookie header: " . $_SERVER['HTTP_COOKIE']);
        $cookies = explode(';', $_SERVER['HTTP_COOKIE']);
        foreach ($cookies as $cookie) {
            $parts = explode('=', trim($cookie));
            if (count($parts) === 2 && $parts[0] === 'PHPSESSID') {
                $token = urldecode($parts[1]);
                error_log("Debug: Raw token from cookie: " . $token);
                if (preg_match('/Bearer\s(\S+)/', $token, $matches)) {
                    $token = $matches[1];
                    error_log("Debug: Successfully extracted token from cookie");
                    return $token;
                }
            }
        }
    }
    
    error_log("Debug: No valid token found in request");
    return null;
});

$app->map('isAdmin', function() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
});

$app->map('isLoggedIn', function() {
    return isset($_SESSION['user_id']);
});

$app->map('requireAdmin', function() use ($app) {
    error_log("Debug: Starting admin access check");
    
    $token = $app->getBearerToken();
    if (!$token) {
        error_log("Debug: No token found in request");
        $app->json(['error' => 'No token provided'], 401);
        return;
    }

    error_log("Debug: Found token, validating...");
    $payload = validateJWT($token);
    if (!$payload) {
        error_log("Debug: Token validation failed");
        $app->json(['error' => 'Invalid token'], 401);
        return;
    }

    error_log("Debug: Token payload: " . print_r($payload, true));
    if (!isset($payload['role']) || $payload['role'] !== 'admin') {
        error_log("Debug: User is not admin. Role: " . ($payload['role'] ?? 'not set'));
        $app->json(['error' => 'Unauthorized access'], 401);
        return;
    }

    error_log("Debug: Admin access granted");
    return $payload;
});

$app->map('db', function() {
    try {
        $pdo = new PDO(
            "mysql:host=localhost;dbname=ecommerce",
            "root",
            "",
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        return $pdo;
    } catch (PDOException $e) {
        error_log("Database connection error: " . $e->getMessage());
        throw $e;
    }
});

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define JWT secret key as a constant
define('JWT_SECRET', 'your-secret-key-123');

// Define base URL without trailing slash
define('BASE_URL', 'http://localhost:8080');

// Define environment
define('ENVIRONMENT', 'development');

// Load middleware classes
require_once 'middleware/ValidationMiddleware.php';
require_once 'middleware/ErrorHandlingMiddleware.php';
require_once 'middleware/LoggingMiddleware.php';

// Initialize middleware
$validationMiddleware = new ValidationMiddleware();
$errorHandlingMiddleware = new ErrorHandlingMiddleware();
$loggingMiddleware = new LoggingMiddleware();

// Apply global middleware
$app->before('start', function() use ($app, $loggingMiddleware) {
    $loggingMiddleware->before([]);
});

$app->after('start', function() use ($app, $loggingMiddleware, $errorHandlingMiddleware) {
    $loggingMiddleware->after([]);
    $errorHandlingMiddleware->after([]);
});

// Add a route for the base path
$app->route('/', function() {
    debug_log("Matched base path route");
    serveHtml('index.html', false);  // index.html is in frontend directory
});

// Add a route for the base path without trailing slash
$app->route('/', function() {
    debug_log("Matched base path route without trailing slash");
    serveHtml('index.html', false);  // index.html is in frontend directory
});

// Set the base directory for views
$app->set('flight.views.path', __DIR__ . '/frontend/views');

// Make baseUrl available to all views
$app->set('baseUrl', BASE_URL);

// Debug function
function debug_log($message) {
    error_log("[DEBUG] " . $message);
}

// Log the initial request
debug_log("Request URI: " . $_SERVER['REQUEST_URI']);
debug_log("Script Filename: " . $_SERVER['SCRIPT_FILENAME']);
debug_log("Document Root: " . $_SERVER['DOCUMENT_ROOT']);

// Test route to verify FlightPHP is working
$app->route('/test', function() use ($app) {
    echo json_encode([
        'status' => 'success',
        'message' => 'FlightPHP is working correctly!',
        'timestamp' => date('Y-m-d H:i:s')
    ]);
});

// Route to serve static assets (CSS, JS, images)
$app->route('/frontend/assets/*', function() {
    $requestUri = $_SERVER['REQUEST_URI'];
    $basePath = __DIR__ . '/frontend/assets/';
    
    // Extract the path after /frontend/assets/
    $assetPath = substr($requestUri, strlen('/frontend/assets/'));
    $filepath = $basePath . $assetPath;
    
    debug_log("Asset request: " . $requestUri);
    debug_log("Asset path: " . $assetPath);
    debug_log("Full filepath: " . $filepath);
    
    if (file_exists($filepath)) {
        $extension = strtolower(pathinfo($filepath, PATHINFO_EXTENSION));
        $mimeTypes = [
            'css' => 'text/css',
            'js' => 'application/javascript',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp'
        ];
        
        if (isset($mimeTypes[$extension])) {
            header('Content-Type: ' . $mimeTypes[$extension]);
        }
        
        readfile($filepath);
    } else {
        header("HTTP/1.0 404 Not Found");
        echo "Asset not found: " . $filepath;
    }
});

// Helper function to serve HTML files
function serveHtml($filename, $isInPages = true) {
    $baseUrl = Flight::get('baseUrl');
    $basePath = __DIR__ . '/frontend/';
    $filepath = $isInPages ? $basePath . 'pages/' . $filename : $basePath . $filename;
    
    debug_log("Serving HTML file: " . $filename);
    debug_log("Base path: " . $basePath);
    debug_log("Full filepath: " . $filepath);
    debug_log("File exists: " . (file_exists($filepath) ? 'Yes' : 'No'));
    debug_log("Current directory: " . __DIR__);
    
    if (file_exists($filepath)) {
        header('Content-Type: text/html');
        $content = file_get_contents($filepath);
        
        // Replace all types of relative paths with absolute paths
        $replacements = [
            // Asset paths
            'href="../assets/' => 'href="' . $baseUrl . '/frontend/assets/',
            'href="./assets/' => 'href="' . $baseUrl . '/frontend/assets/',
            'href="assets/' => 'href="' . $baseUrl . '/frontend/assets/',
            
            // Image paths
            'src="../assets/' => 'src="' . $baseUrl . '/frontend/assets/',
            'src="./assets/' => 'src="' . $baseUrl . '/frontend/assets/',
            'src="assets/' => 'src="' . $baseUrl . '/frontend/assets/',
            
            // Navigation links - handle both old and new formats
            'href="../index.html"' => 'href="' . $baseUrl . '/"',
            'href="./index.html"' => 'href="' . $baseUrl . '/"',
            'href="index.html"' => 'href="' . $baseUrl . '/"',
            
            // Category links - handle both old and new formats
            'href="./pages/shirts.html"' => 'href="' . $baseUrl . '/shirts"',
            'href="./pages/jackets.html"' => 'href="' . $baseUrl . '/jackets"',
            'href="./pages/perfumes.html"' => 'href="' . $baseUrl . '/perfumes"',
            'href="./pages/sneakers.html"' => 'href="' . $baseUrl . '/sneakers"',
            'href="./pages/tracksuits.html"' => 'href="' . $baseUrl . '/tracksuits"',
            'href="shirts.html"' => 'href="' . $baseUrl . '/shirts"',
            'href="jackets.html"' => 'href="' . $baseUrl . '/jackets"',
            'href="perfumes.html"' => 'href="' . $baseUrl . '/perfumes"',
            'href="sneakers.html"' => 'href="' . $baseUrl . '/sneakers"',
            'href="tracksuits.html"' => 'href="' . $baseUrl . '/tracksuits"',
            
            // JavaScript redirects in featured categories
            "onclick=\"window.location.href='./pages/shirts.html'\"" => "onclick=\"window.location.href='" . $baseUrl . "/shirts'\"",
            "onclick=\"window.location.href='./pages/jackets.html'\"" => "onclick=\"window.location.href='" . $baseUrl . "/jackets'\"",
            "onclick=\"window.location.href='./pages/perfumes.html'\"" => "onclick=\"window.location.href='" . $baseUrl . "/perfumes'\"",
            "onclick=\"window.location.href='./pages/sneakers.html'\"" => "onclick=\"window.location.href='" . $baseUrl . "/sneakers'\"",
            "onclick=\"window.location.href='./pages/tracksuits.html'\"" => "onclick=\"window.location.href='" . $baseUrl . "/tracksuits'\"",
            
            // Other page links
            'href="./pages/cart.html"' => 'href="' . $baseUrl . '/cart"',
            'href="./pages/user-profile.html"' => 'href="' . $baseUrl . '/user-profile"',
            'href="./pages/admin-dashboard.html"' => 'href="' . $baseUrl . '/admin-dashboard"',
            'href="./pages/login.html"' => 'href="' . $baseUrl . '/login"',
            'href="./pages/register.html"' => 'href="' . $baseUrl . '/register"',
            'href="./pages/checkout.html"' => 'href="' . $baseUrl . '/checkout"',
            'href="./pages/categories.html"' => 'href="' . $baseUrl . '/categories"',
            'href="./pages/manage-products.html"' => 'href="' . $baseUrl . '/admin/manage-products"',
            'href="./pages/manage-orders.html"' => 'href="' . $baseUrl . '/admin/manage-orders"',
            'href="./pages/manage-users.html"' => 'href="' . $baseUrl . '/admin/manage-users"',
            
            // Also handle links without ./pages/
            'href="cart.html"' => 'href="' . $baseUrl . '/cart"',
            'href="user-profile.html"' => 'href="' . $baseUrl . '/user-profile"',
            'href="admin-dashboard.html"' => 'href="' . $baseUrl . '/admin-dashboard"',
            'href="login.html"' => 'href="' . $baseUrl . '/login"',
            'href="register.html"' => 'href="' . $baseUrl . '/register"',
            'href="checkout.html"' => 'href="' . $baseUrl . '/checkout"',
            'href="categories.html"' => 'href="' . $baseUrl . '/categories"',
            'href="manage-products.html"' => 'href="' . $baseUrl . '/admin/manage-products"',
            'href="manage-orders.html"' => 'href="' . $baseUrl . '/admin/manage-orders"',
            'href="manage-users.html"' => 'href="' . $baseUrl . '/admin/manage-users"'
        ];
        
        $content = str_replace(array_keys($replacements), array_values($replacements), $content);
        
        // Remove any double slashes in the URLs (except for http:// and https://)
        $content = preg_replace('#([^:])//+#', '$1/', $content);
        
        echo $content;
    } else {
        // If the file doesn't exist in the pages directory, try the views directory
        $viewPath = __DIR__ . '/frontend/views/' . $filename;
        if (file_exists($viewPath)) {
            // Include the view file which will have access to the variables
            include $viewPath;
        } else {
            header("HTTP/1.0 404 Not Found");
            echo "Page not found: " . $filepath;
            debug_log("404 Error - File not found: " . $filepath);
        }
    }
}

// Helper function to check if request is for API
function isApiRequest() {
    $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
    return strpos($accept, 'application/json') !== false || 
           strpos($contentType, 'application/json') !== false;
}

// Helper function to send JSON response
function sendJsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

// Helper function to check admin role
function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

// Helper function to generate JWT token
function generateJWT($user) {
    $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
    $payload = json_encode([
        'user_id' => $user['ID'],
        'email' => $user['Email'],
        'role' => $user['Role'],
        'exp' => time() + (60 * 60) // Token expires in 1 hour
    ]);

    $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
    $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));
    $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, JWT_SECRET, true);
    $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

    return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
}

// Helper function to get token from request
function getBearerToken() {
    error_log("Debug: Starting token extraction");
    
    // Get all headers
    $headers = getallheaders();
    error_log("Debug: All headers: " . print_r($headers, true));
    
    // Check Authorization header
    if (isset($headers['Authorization'])) {
        error_log("Debug: Found Authorization header: " . $headers['Authorization']);
        if (preg_match('/Bearer\s(\S+)/', $headers['Authorization'], $matches)) {
            $token = $matches[1];
            error_log("Debug: Successfully extracted token from Authorization header");
            return $token;
        }
    }
    
    // If no Authorization header, try to get from Cookie
    if (isset($_SERVER['HTTP_COOKIE'])) {
        error_log("Debug: Found Cookie header: " . $_SERVER['HTTP_COOKIE']);
        $cookies = explode(';', $_SERVER['HTTP_COOKIE']);
        foreach ($cookies as $cookie) {
            $parts = explode('=', trim($cookie));
            if (count($parts) === 2 && $parts[0] === 'PHPSESSID') {
                $token = urldecode($parts[1]);
                error_log("Debug: Raw token from cookie: " . $token);
                if (preg_match('/Bearer\s(\S+)/', $token, $matches)) {
                    $token = $matches[1];
                    error_log("Debug: Successfully extracted token from cookie");
                    return $token;
                }
            }
        }
    }
    
    error_log("Debug: No valid token found in request");
    return null;
}

// Helper function to validate JWT token
function validateJWT($token) {
    if (empty($token)) {
        error_log("Debug: Empty token provided");
        return false;
    }

    error_log("Debug: Validating token: " . $token);
    
    // Split the token
    $tokenParts = explode('.', $token);
    if (count($tokenParts) != 3) {
        error_log("Debug: Invalid token format - wrong number of parts");
        return false;
    }

    try {
        // Decode the payload
        $payload = json_decode(base64_decode(str_replace(['-', '_'], ['+', '/'], $tokenParts[1])), true);
        error_log("Debug: Token payload: " . print_r($payload, true));
        
        if (!$payload) {
            error_log("Debug: Failed to decode payload");
            return false;
        }

        // Verify expiration
        if (!isset($payload['exp'])) {
            error_log("Debug: No expiration time in token");
            return false;
        }

        if ($payload['exp'] < time()) {
            error_log("Debug: Token expired at " . date('Y-m-d H:i:s', $payload['exp']));
            return false;
        }

        // Verify signature
        $signature = hash_hmac('sha256', $tokenParts[0] . "." . $tokenParts[1], JWT_SECRET, true);
        $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

        if ($base64UrlSignature !== $tokenParts[2]) {
            error_log("Debug: Invalid signature");
            error_log("Debug: Expected: " . $base64UrlSignature);
            error_log("Debug: Got: " . $tokenParts[2]);
            return false;
        }

        error_log("Debug: Token validation successful");
        return $payload;
    } catch (Exception $e) {
        error_log("Debug: Error validating token: " . $e->getMessage());
        return false;
    }
}

// Helper function to require admin access
function requireAdmin() {
    error_log("Debug: Starting admin access check");
    
    $token = getBearerToken();
    if (!$token) {
        error_log("Debug: No token found in request");
        sendJsonResponse(['error' => 'No token provided'], 401);
    }

    error_log("Debug: Found token, validating...");
    $payload = validateJWT($token);
    if (!$payload) {
        error_log("Debug: Token validation failed");
        sendJsonResponse(['error' => 'Invalid token'], 401);
    }

    error_log("Debug: Token payload: " . print_r($payload, true));
    if (!isset($payload['role']) || $payload['role'] !== 'admin') {
        error_log("Debug: User is not admin. Role: " . ($payload['role'] ?? 'not set'));
        sendJsonResponse(['error' => 'Unauthorized access'], 401);
    }

    error_log("Debug: Admin access granted");
    return $payload;
}

// Define routes
$app->route('/', function() {
    debug_log("Matched root route");
    serveHtml('index.html', false);  // index.html is in frontend directory
});

// Product category routes
$app->route('GET /categories', function() use ($app) {
    $db = $app->db();
    $stmt = $db->query('SELECT * FROM categories');
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($app->isApiRequest()) {
        $app->json($categories);
    } else {
        $app->render('categories/index', ['categories' => $categories]);
    }
});

$app->route('GET /categories/@id', function($id) use ($app) {
    try {
        $db = $app->db();
        $stmt = $db->prepare('SELECT * FROM categories WHERE id = ?');
        $stmt->execute([$id]);
        $category = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$category) {
            $app->json(['error' => 'Category not found'], 404);
            return;
        }
        
        if ($app->isApiRequest()) {
            $app->json($category);
        } else {
            $app->render('categories/show', ['category' => $category]);
        }
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        $app->json(['error' => 'Failed to retrieve category'], 500);
    }
});

$app->route('PUT /categories/@id', function($id) use ($app) {
    $app->requireAdmin();
    
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) {
        $app->json(['error' => 'Invalid JSON data'], 400);
        return;
    }

    if (empty($data['name'])) {
        $app->json(['error' => 'Category name is required'], 400);
        return;
    }

    try {
        $db = $app->db();
        
        // Check if category exists
        $stmt = $db->prepare('SELECT * FROM categories WHERE id = ?');
        $stmt->execute([$id]);
        $category = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$category) {
            $app->json(['error' => 'Category not found'], 404);
            return;
        }
        
        // Update category
        $stmt = $db->prepare('UPDATE categories SET name = ?, description = ? WHERE id = ?');
        $stmt->execute([$data['name'], $data['description'] ?? null, $id]);
        
        // Get updated category
        $stmt = $db->prepare('SELECT * FROM categories WHERE id = ?');
        $stmt->execute([$id]);
        $updatedCategory = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $app->json($updatedCategory);
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        $app->json(['error' => 'Failed to update category'], 500);
    }
});

$app->route('DELETE /categories/@id', function($id) use ($app) {
    $app->requireAdmin();
    
    try {
        $db = $app->db();
        
        // Check if category exists
        $stmt = $db->prepare('SELECT * FROM categories WHERE id = ?');
        $stmt->execute([$id]);
        $category = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$category) {
            $app->json(['error' => 'Category not found'], 404);
            return;
        }
        
        // Delete category
        $stmt = $db->prepare('DELETE FROM categories WHERE id = ?');
        $stmt->execute([$id]);
        
        $app->json(['message' => 'Category deleted successfully']);
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        $app->json(['error' => 'Failed to delete category'], 500);
    }
});

$app->route('POST /categories', function() use ($app) {
    $app->requireAdmin();
    
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) {
        $app->json(['error' => 'Invalid JSON data'], 400);
        return;
    }

    if (empty($data['name'])) {
        $app->json(['error' => 'Category name is required'], 400);
        return;
    }

    try {
        $db = $app->db();
        $stmt = $db->prepare('INSERT INTO categories (name, description) VALUES (?, ?)');
        $stmt->execute([$data['name'], $data['description'] ?? null]);
        
        $categoryId = $db->lastInsertId();
        $stmt = $db->prepare('SELECT * FROM categories WHERE id = ?');
        $stmt->execute([$categoryId]);
        $category = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $app->json($category, 201);
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        $app->json(['error' => 'Failed to create category'], 500);
    }
});

// Shirts route
$app->route('GET /shirts', function() use ($app) {
    error_log("Debug: Shirts route accessed");
    
    // TODO: Get products from database
    $products = [
        [
            'id' => 1,
            'name' => 'Classic White Shirt',
            'price' => '29.99',
            'description' => 'A timeless white shirt perfect for any occasion.',
            'image' => 'shirt1.jpg'
        ],
        [
            'id' => 2,
            'name' => 'Blue Denim Shirt',
            'price' => '39.99',
            'description' => 'Casual blue denim shirt for a relaxed look.',
            'image' => 'shirt2.jpg'
        ],
        [
            'id' => 3,
            'name' => 'Black Formal Shirt',
            'price' => '49.99',
            'description' => 'Elegant black shirt for formal events.',
            'image' => 'shirt3.jpg'
        ]
    ];
    
    // Check if the client accepts JSON
    $acceptHeader = $_SERVER['HTTP_ACCEPT'] ?? '';
    if (strpos($acceptHeader, 'application/json') !== false) {
        // Return JSON response
        header('Content-Type: application/json');
        echo json_encode($products);
    } else {
        // Return HTML response
        $app->render('shirts', [
            'products' => $products,
            'baseUrl' => BASE_URL
        ]);
    }
})->addMiddleware(new ValidationMiddleware([
    'category_id' => 'numeric'
]));

$app->route('POST /shirts/add-to-cart', function() use ($app) {
    error_log("Debug: Add to cart request received");
    
    $productId = $_POST['productId'] ?? null;
    $quantity = $_POST['quantity'] ?? 1;
    
    if (!$productId || $quantity < 1) {
        $app->json([
            'error' => 'Invalid request'
        ]);
        return;
    }
    
    // TODO: Add product to cart in session/database
    $app->json([
        'success' => true,
        'message' => 'Product added to cart successfully'
    ]);
})->addMiddleware(new ValidationMiddleware([
    'productId' => 'required|numeric',
    'quantity' => 'required|numeric|min:1'
]));

// Jackets route
$app->route('GET /jackets', function() use ($app) {
    error_log("Accessing jackets route");
    
    // Prepare products data
    $products = [
        [
            'id' => 1,
            'name' => 'Classic Denim Jacket',
            'price' => 89.99,
            'description' => 'A timeless denim jacket perfect for any casual occasion.',
            'image' => 'jacket1.jpg'
        ],
        [
            'id' => 2,
            'name' => 'Leather Biker Jacket',
            'price' => 199.99,
            'description' => 'Premium leather jacket with classic biker style.',
            'image' => 'jacket2.jpg'
        ],
        [
            'id' => 3,
            'name' => 'Lightweight Windbreaker',
            'price' => 59.99,
            'description' => 'Perfect for spring and fall weather.',
            'image' => 'jacket3.jpg'
        ]
    ];
    
    // Render the view
    $app->render('jackets', [
        'baseUrl' => BASE_URL,
        'products' => $products
    ]);
});

// Add to cart from jackets page
$app->route('POST /jackets/add-to-cart', function() use ($app) {
    error_log("Processing add to cart request from jackets page");
    
    $productId = $_POST['productId'] ?? null;
    $quantity = $_POST['quantity'] ?? 1;
    
    if (!$productId) {
        $app->json(['error' => 'Invalid product ID'], 400);
        return;
    }
    
    // Here you would typically add the item to the cart in the database
    // For now, we'll just return a success message
    $app->json(['message' => 'Product added to cart successfully']);
});

// Sneakers route
$app->route('GET /sneakers', function() use ($app) {
    error_log("Accessing sneakers route");
    
    // Prepare products data
    $products = [
        [
            'id' => 1,
            'name' => 'Classic White Sneakers',
            'price' => 89.99,
            'description' => 'Timeless white sneakers perfect for everyday wear.',
            'image' => 'sneakers1.jpg'
        ],
        [
            'id' => 2,
            'name' => 'Black Running Shoes',
            'price' => 119.99,
            'description' => 'Comfortable running shoes with advanced cushioning.',
            'image' => 'sneakers2.jpg'
        ],
        [
            'id' => 3,
            'name' => 'Casual Canvas Sneakers',
            'price' => 49.99,
            'description' => 'Lightweight canvas sneakers for a casual look.',
            'image' => 'sneakers3.jpg'
        ]
    ];
    
    // Render the view
    $app->render('sneakers', [
        'baseUrl' => BASE_URL,
        'products' => $products
    ]);
});

// Add to cart from sneakers page
$app->route('POST /sneakers/add-to-cart', function() use ($app) {
    error_log("Processing add to cart request from sneakers page");
    
    $productId = $_POST['productId'] ?? null;
    $quantity = $_POST['quantity'] ?? 1;
    
    if (!$productId) {
        $app->json(['error' => 'Invalid product ID'], 400);
        return;
    }
    
    // Here you would typically add the item to the cart in the database
    // For now, we'll just return a success message
    $app->json(['message' => 'Product added to cart successfully']);
});

// Tracksuits route
$app->route('GET /tracksuits', function() use ($app) {
    error_log("Accessing tracksuits route");
    
    // Prepare products data
    $products = [
        [
            'id' => 1,
            'name' => 'Classic Black Tracksuit',
            'price' => 79.99,
            'description' => 'Comfortable black tracksuit perfect for workouts and casual wear.',
            'image' => 'tracksuit1.jpg'
        ],
        [
            'id' => 2,
            'name' => 'Grey Athletic Set',
            'price' => 89.99,
            'description' => 'Premium grey tracksuit with moisture-wicking fabric.',
            'image' => 'tracksuit2.jpg'
        ],
        [
            'id' => 3,
            'name' => 'Navy Blue Training Suit',
            'price' => 69.99,
            'description' => 'Stylish navy blue tracksuit for training and leisure.',
            'image' => 'tracksuit3.jpg'
        ]
    ];
    
    // Render the view
    $app->render('tracksuits', [
        'baseUrl' => BASE_URL,
        'products' => $products
    ]);
});

// Add to cart from tracksuits page
$app->route('POST /tracksuits/add-to-cart', function() use ($app) {
    error_log("Processing add to cart request from tracksuits page");
    
    $productId = $_POST['productId'] ?? null;
    $quantity = $_POST['quantity'] ?? 1;
    
    if (!$productId) {
        $app->json(['error' => 'Invalid product ID'], 400);
        return;
    }
    
    // Here you would typically add the item to the cart in the database
    // For now, we'll just return a success message
    $app->json(['message' => 'Product added to cart successfully']);
});

// Perfumes route
$app->route('GET /perfumes', function() use ($app) {
    error_log("Accessing perfumes route");
    
    // Prepare products data
    $products = [
        [
            'id' => 1,
            'name' => 'Classic Eau de Parfum',
            'price' => 79.99,
            'description' => 'A timeless fragrance with notes of bergamot and sandalwood.',
            'image' => 'perfume1.jpg'
        ],
        [
            'id' => 2,
            'name' => 'Fresh Citrus Cologne',
            'price' => 59.99,
            'description' => 'Light and refreshing with citrus and mint notes.',
            'image' => 'perfume2.jpg'
        ],
        [
            'id' => 3,
            'name' => 'Woody Spice Eau de Toilette',
            'price' => 89.99,
            'description' => 'Rich and warm with notes of cedar and spices.',
            'image' => 'perfume3.jpg'
        ]
    ];
    
    // Render the view
    $app->render('perfumes', [
        'baseUrl' => BASE_URL,
        'products' => $products
    ]);
});

// Add to cart from perfumes page
$app->route('POST /perfumes/add-to-cart', function() use ($app) {
    error_log("Processing add to cart request from perfumes page");
    
    $productId = $_POST['productId'] ?? null;
    $quantity = $_POST['quantity'] ?? 1;
    
    if (!$productId) {
        $app->json(['error' => 'Invalid product ID'], 400);
        return;
    }
    
    // Here you would typically add the item to the cart in the database
    // For now, we'll just return a success message
    $app->json(['message' => 'Product added to cart successfully']);
});

// Login route that handles both API and form submissions
$app->route('GET /login', function() use ($app) {
    error_log("Debug: Accessing login page");
    
    // If there's an existing session, destroy it
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_unset();
        session_destroy();
    }
    
    // Start a fresh session
    session_start();
    
    // Serve the login page
    serveHtml('login.php', false);
});

$app->route('POST /login', function() use ($app) {
    error_log("Debug: Processing login request");
    
    if ($app->isApiRequest()) {
        // Handle API request
        $data = json_decode(file_get_contents('php://input'), true);
        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';
        
        if (empty($email) || empty($password)) {
            $app->json(['error' => 'Email and password are required'], 400);
            return;
        }
        
        try {
            $pdo = $app->db();
            $stmt = $pdo->prepare("SELECT ID, Name, Email, Password, Role FROM Users WHERE Email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user || !password_verify($password, $user['Password'])) {
                $app->json(['error' => 'Invalid email or password'], 401);
                return;
            }
            
            // Start fresh session
            if (session_status() === PHP_SESSION_ACTIVE) {
                session_destroy();
            }
            session_start();
            
            // Set session data
            $_SESSION['user_id'] = $user['ID'];
            $_SESSION['user_role'] = $user['Role'];
            $_SESSION['user_name'] = $user['Name'];
            $_SESSION['user_email'] = $user['Email'];
            
            // Generate JWT token
            $token = generateJWT($user);
            
            $app->json([
                'message' => 'Login successful',
                'token' => $token,
                'user' => [
                    'id' => $user['ID'],
                    'name' => $user['Name'],
                    'email' => $user['Email'],
                    'role' => $user['Role']
                ]
            ]);
            
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            $app->json(['error' => 'An error occurred during login'], 500);
        }
    } else {
        // Handle form submission
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        
        if (empty($email) || empty($password)) {
            $app->render('login', [
                'error' => 'Email and password are required',
                'email' => $email,
                'baseUrl' => BASE_URL
            ]);
            return;
        }
        
        try {
            $pdo = $app->db();
            $stmt = $pdo->prepare("SELECT ID, Name, Email, Password, Role FROM Users WHERE Email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user || !password_verify($password, $user['Password'])) {
                $app->render('login', [
                    'error' => 'Invalid email or password',
                    'email' => $email,
                    'baseUrl' => BASE_URL
                ]);
                return;
            }
            
            // Start fresh session
            if (session_status() === PHP_SESSION_ACTIVE) {
                session_destroy();
            }
            session_start();
            
            // Set session data
            $_SESSION['user_id'] = $user['ID'];
            $_SESSION['user_role'] = $user['Role'];
            $_SESSION['user_name'] = $user['Name'];
            $_SESSION['user_email'] = $user['Email'];
            
            // Generate JWT token
            $token = generateJWT($user);
            
            // Store token in cookie
            setcookie('token', $token, time() + (86400 * 30), "/", "", false, true); // 30 days, secure cookie
            
            // Redirect based on role
            if ($user['Role'] === 'admin') {
                $app->redirect('/admin/dashboard');
            } else {
                $app->redirect('/');
            }
            
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            $app->render('login', [
                'error' => 'An error occurred during login',
                'email' => $email,
                'baseUrl' => BASE_URL
            ]);
        }
    }
});

$app->route('GET /logout', function() use ($app) {
    // Clear all session data
    session_unset();
    session_destroy();
    
    // Clear the auth token cookie
    setcookie('auth_token', '', time() - 3600, '/', '', true, true);
    
    // Clear any other cookies that might be set
    if (isset($_SERVER['HTTP_COOKIE'])) {
        $cookies = explode(';', $_SERVER['HTTP_COOKIE']);
        foreach($cookies as $cookie) {
            $parts = explode('=', $cookie);
            $name = trim($parts[0]);
            setcookie($name, '', time() - 3600, '/');
        }
    }
    
    // Start a new session
    session_start();
    
    // Redirect to login page
    $app->redirect('/login');
});

$app->route('POST /logout', function() use ($app) {
    // Clear session
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_destroy();
    }
    
    // Clear token cookie
    setcookie('token', '', time() - 3600, '/', '', false, true);
    
    if ($app->isApiRequest()) {
        $app->json(['message' => 'Logged out successfully']);
    } else {
        $app->redirect('/login');
    }
});

$app->route('POST /user-profile', function() use ($app) {
    error_log("Debug: User profile POST request received");
    
    // Check if user is logged in
    session_start();
    if (!isset($_SESSION['user_id'])) {
        $app->redirect('/login');
        return;
    }
    
    // Get form data
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $currentPassword = $_POST['currentPassword'] ?? '';
    $newPassword = $_POST['newPassword'] ?? '';
    $confirmPassword = $_POST['confirmPassword'] ?? '';
    
    // Basic validation
    $error = null;
    if (empty($username) || empty($email)) {
        $error = "Username and email are required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format";
    } elseif ($newPassword && strlen($newPassword) < 6) {
        $error = "New password must be at least 6 characters long";
    } elseif ($newPassword && $newPassword !== $confirmPassword) {
        $error = "New passwords do not match";
    }
    
    if ($error) {
        // If there's an error, render the form again with the error message
        $app->render('user-profile', [
            'error' => $error,
            'user' => [
                'username' => $username,
                'email' => $email
            ],
            'baseUrl' => BASE_URL
        ]);
        return;
    }
    
    try {
        $pdo = $app->db();
        
        // Verify current password if changing password
        if ($newPassword) {
            $stmt = $pdo->prepare("SELECT Password FROM Users WHERE ID = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user || !password_verify($currentPassword, $user['Password'])) {
                $app->render('user-profile', [
                    'error' => 'Current password is incorrect',
                    'user' => [
                        'username' => $username,
                        'email' => $email
                    ],
                    'baseUrl' => BASE_URL
                ]);
                return;
            }
            
            // Update user with new password
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE Users SET Name = ?, Email = ?, Password = ? WHERE ID = ?");
            $stmt->execute([$username, $email, $hashedPassword, $_SESSION['user_id']]);
        } else {
            // Update user without changing password
            $stmt = $pdo->prepare("UPDATE Users SET Name = ?, Email = ? WHERE ID = ?");
            $stmt->execute([$username, $email, $_SESSION['user_id']]);
        }
        
        $app->render('user-profile', [
            'success' => 'Profile updated successfully',
            'user' => [
                'username' => $username,
                'email' => $email
            ],
            'baseUrl' => BASE_URL
        ]);
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        $app->render('user-profile', [
            'error' => 'An error occurred while updating your profile',
            'user' => [
                'username' => $username,
                'email' => $email
            ],
            'baseUrl' => BASE_URL
        ]);
    }
});

// Function to ensure cart table exists
function ensureCartTable() {
    try {
        $db = getDbConnection();
        $db->exec("CREATE TABLE IF NOT EXISTS cart (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            product_id INT NOT NULL,
            quantity INT NOT NULL DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
            UNIQUE KEY unique_cart_item (user_id, product_id)
        )");
    } catch (PDOException $e) {
        error_log("Error creating cart table: " . $e->getMessage());
        throw $e;
    }
}

// Cart routes
$app->route('GET /cart', function() use ($app) {
    error_log("Debug: Accessing cart route");
    
    if ($app->isApiRequest()) {
        // API request handling
        $token = $app->getBearerToken();
        if (!$token) {
            $app->json(['error' => 'Unauthorized'], 401);
            return;
        }

        try {
            $payload = validateJWT($token);
            if (!$payload) {
                $app->json(['error' => 'Invalid token'], 401);
                return;
            }
            
            $userId = $payload['user_id'];
            error_log("Debug: Retrieving cart for user ID: " . $userId);

            // Get cart items with product details
            $db = $app->db();
            error_log("Debug: Database connection established");
            
            $query = "
                SELECT c.*, p.name, p.price, p.description, p.stock 
                FROM cart c 
                JOIN products p ON c.product_id = p.id 
                WHERE c.user_id = ?
            ";
            error_log("Debug: Executing query: " . $query);
            
            $stmt = $db->prepare($query);
            $stmt->execute([$userId]);
            $cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
            error_log("Debug: Found " . count($cartItems) . " cart items");

            // Calculate totals
            $subtotal = 0;
            foreach ($cartItems as $item) {
                $subtotal += $item['price'] * $item['quantity'];
            }
            $shipping = 10; // Fixed shipping cost
            $total = $subtotal + $shipping;

            $response = [
                'items' => $cartItems,
                'subtotal' => $subtotal,
                'shipping' => $shipping,
                'total' => $total
            ];
            error_log("Debug: Sending response: " . json_encode($response));
            
            $app->json($response);
        } catch (PDOException $e) {
            error_log("Database error in GET /cart: " . $e->getMessage());
            error_log("SQL State: " . $e->getCode());
            $app->json([
                'error' => 'Database error',
                'message' => $e->getMessage(),
                'code' => $e->getCode()
            ], 500);
        } catch (Exception $e) {
            error_log("General error in GET /cart: " . $e->getMessage());
            $app->json([
                'error' => 'Failed to retrieve cart',
                'message' => $e->getMessage()
            ], 500);
        }
    } else {
        // For regular page access, render the cart.php view
        error_log("Debug: Rendering cart view");
        $app->render('cart', [
            'baseUrl' => BASE_URL
        ]);
    }
});

$app->route('POST /cart', function() use ($app) {
    try {
        // Get and validate JWT token
        $token = $app->getBearerToken();
        if (!$token) {
            $app->json(['error' => 'No token provided'], 401);
            return;
        }

        $payload = validateJWT($token);
        if (!$payload) {
            $app->json(['error' => 'Invalid token'], 401);
            return;
        }

        $userId = $payload['user_id'];
        
        // Get and validate request data
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data) {
            $app->json(['error' => 'Invalid JSON data'], 400);
            return;
        }

        if (!isset($data['product_id']) || !is_numeric($data['product_id'])) {
            $app->json(['error' => 'Valid product ID is required'], 400);
            return;
        }

        if (!isset($data['quantity']) || !is_numeric($data['quantity']) || $data['quantity'] < 1) {
            $app->json(['error' => 'Valid quantity is required'], 400);
            return;
        }

        $db = $app->db();
        
        // Check if product exists and has enough stock
        $stmt = $db->prepare('SELECT * FROM products WHERE id = ?');
        $stmt->execute([$data['product_id']]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$product) {
            $app->json(['error' => 'Product not found'], 404);
            return;
        }

        if ($product['stock'] < $data['quantity']) {
            $app->json(['error' => 'Not enough stock available'], 400);
            return;
        }

        // Check if item already exists in cart
        $stmt = $db->prepare('SELECT * FROM cart WHERE user_id = ? AND product_id = ?');
        $stmt->execute([$userId, $data['product_id']]);
        $existingItem = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existingItem) {
            // Update quantity if item exists
            $newQuantity = $existingItem['quantity'] + $data['quantity'];
            if ($newQuantity > $product['stock']) {
                $app->json(['error' => 'Not enough stock available for requested quantity'], 400);
                return;
            }
            
            $stmt = $db->prepare('UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?');
            $stmt->execute([$newQuantity, $userId, $data['product_id']]);
        } else {
            // Insert new item if it doesn't exist
            $stmt = $db->prepare('INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)');
            $stmt->execute([$userId, $data['product_id'], $data['quantity']]);
        }

        // Get updated cart item with product details
        $stmt = $db->prepare('
            SELECT c.*, p.name, p.price, p.stock 
            FROM cart c 
            JOIN products p ON c.product_id = p.id 
            WHERE c.user_id = ? AND c.product_id = ?
        ');
        $stmt->execute([$userId, $data['product_id']]);
        $cartItem = $stmt->fetch(PDO::FETCH_ASSOC);

        $app->json([
            'message' => 'Item added to cart successfully',
            'cart_item' => $cartItem
        ]);
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        $app->json(['error' => 'Failed to add item to cart: ' . $e->getMessage()], 500);
    }
})->addMiddleware(new ValidationMiddleware([
    'product_id' => 'required|numeric',
    'quantity' => 'required|numeric|min:1'
]));

$app->route('POST /cart/update', function() use ($app) {
    try {
        // Get and validate JWT token
        $token = $app->getBearerToken();
        if (!$token) {
            $app->json(['error' => 'No token provided'], 401);
            return;
        }

        $payload = validateJWT($token);
        if (!$payload) {
            $app->json(['error' => 'Invalid token'], 401);
            return;
        }

        $userId = $payload['user_id'];
        
        // Get and validate request data
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data) {
            $app->json(['error' => 'Invalid JSON data'], 400);
            return;
        }

        if (!isset($data['product_id']) || !is_numeric($data['product_id'])) {
            $app->json(['error' => 'Valid product ID is required'], 400);
            return;
        }

        if (!isset($data['quantity']) || !is_numeric($data['quantity']) || $data['quantity'] < 0) {
            $app->json(['error' => 'Valid quantity is required'], 400);
            return;
        }

        $db = $app->db();
        
        // Check if product exists and has enough stock
        $stmt = $db->prepare('SELECT * FROM products WHERE id = ?');
        $stmt->execute([$data['product_id']]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$product) {
            $app->json(['error' => 'Product not found'], 404);
            return;
        }

        // Check if item exists in cart
        $stmt = $db->prepare('SELECT * FROM cart WHERE user_id = ? AND product_id = ?');
        $stmt->execute([$userId, $data['product_id']]);
        $existingItem = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$existingItem) {
            $app->json(['error' => 'Item not found in cart'], 404);
            return;
        }

        // If quantity is 0, remove item from cart
        if ($data['quantity'] === 0) {
            $stmt = $db->prepare('DELETE FROM cart WHERE user_id = ? AND product_id = ?');
            $stmt->execute([$userId, $data['product_id']]);
            $app->json([
                'message' => 'Item removed from cart',
                'cart_item' => null
            ]);
            return;
        }

        // Check if new quantity exceeds stock
        if ($data['quantity'] > $product['stock']) {
            $app->json(['error' => 'Not enough stock available for requested quantity'], 400);
            return;
        }

        $stmt = $db->prepare('UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?');
        $stmt->execute([$data['quantity'], $userId, $data['product_id']]);

        // Get updated cart item with product details
        $stmt = $db->prepare('
            SELECT c.*, p.name, p.price, p.stock 
            FROM cart c 
            JOIN products p ON c.product_id = p.id 
            WHERE c.user_id = ? AND c.product_id = ?
        ');
        $stmt->execute([$userId, $data['product_id']]);
        $cartItem = $stmt->fetch(PDO::FETCH_ASSOC);

        $app->json([
            'message' => 'Cart updated successfully',
            'cart_item' => $cartItem
        ]);
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        $app->json(['error' => 'Failed to update cart: ' . $e->getMessage()], 500);
    }
});

$app->route('DELETE /cart/@id', function($id) use ($app) {
    try {
        // Get and validate JWT token
        $token = $app->getBearerToken();
        if (!$token) {
            $app->json(['error' => 'No token provided'], 401);
            return;
        }

        $payload = validateJWT($token);
        if (!$payload) {
            $app->json(['error' => 'Invalid token'], 401);
            return;
        }

        $userId = $payload['user_id'];
        
        $db = $app->db();
        
        // Check if the item exists in the user's cart
        $stmt = $db->prepare('SELECT * FROM cart WHERE user_id = ? AND product_id = ?');
        $stmt->execute([$userId, $id]);
        $cartItem = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$cartItem) {
            $app->json(['error' => 'Item not found in cart'], 404);
            return;
        }
        
        // Delete the item from cart
        $stmt = $db->prepare('DELETE FROM cart WHERE user_id = ? AND product_id = ?');
        $stmt->execute([$userId, $id]);
        
        $app->json([
            'message' => 'Item removed from cart successfully',
            'removed_item' => [
                'product_id' => $id,
                'quantity' => $cartItem['quantity']
            ]
        ]);
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        $app->json(['error' => 'Failed to remove item from cart: ' . $e->getMessage()], 500);
    }
});

// Checkout route
$app->route('GET /checkout', function() use ($app) {
    error_log("Debug: Checkout route accessed");
    
    // TODO: Get cart data from session/database
    $cart = [
        'items' => [
            [
                'id' => 1,
                'name' => 'Classic White Shirt',
                'price' => '29.99',
                'quantity' => 2,
                'image' => 'shirt1.jpg'
            ],
            [
                'id' => 2,
                'name' => 'Black Leather Jacket',
                'price' => '99.99',
                'quantity' => 1,
                'image' => 'jacket1.jpg'
            ]
        ],
        'subtotal' => '159.97',
        'shipping' => '10.00',
        'total' => '169.97'
    ];
    
    // TODO: Get saved shipping info from user profile
    $shipping = [
        'fullName' => 'John Doe',
        'address' => '123 Main St',
        'city' => 'New York',
        'state' => 'NY',
        'zipCode' => '10001'
    ];
    
    $app->render('checkout', [
        'cart' => $cart,
        'shipping' => $shipping,
        'baseUrl' => BASE_URL
    ]);
})->addMiddleware(new ValidationMiddleware([
    'token' => 'required'
]));

$app->route('POST /checkout', function() use ($app) {
    $token = $app->getBearerToken();
    if (!$token) {
        $app->json(['error' => 'No token provided'], 401);
        return;
    }
    $payload = validateJWT($token);
    if (!$payload) {
        $app->json(['error' => 'Invalid token'], 401);
        return;
    }
    $userId = $payload['user_id'];
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data || empty($data['shipping_address'])) {
        $app->json(['error' => 'Shipping address is required'], 400);
        return;
    }
    $db = $app->db();
    $stmt = $db->prepare('SELECT c.*, p.price FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = ?');
    $stmt->execute([$userId]);
    $cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (empty($cartItems)) {
        $app->json(['error' => 'Cart is empty'], 400);
        return;
    }
    $total = 0;
    foreach ($cartItems as $item) {
        $total += $item['price'] * $item['quantity'];
    }
    $stmt = $db->prepare('INSERT INTO orders (user_id, total, shipping_address) VALUES (?, ?, ?)');
    $stmt->execute([$userId, $total, $data['shipping_address']]);
    $orderId = $db->lastInsertId();
    foreach ($cartItems as $item) {
        $stmt = $db->prepare('INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)');
        $stmt->execute([$orderId, $item['product_id'], $item['quantity'], $item['price']]);
    }
    $stmt = $db->prepare('DELETE FROM cart WHERE user_id = ?');
    $stmt->execute([$userId]);
    $app->json(['message' => 'Order placed successfully', 'order_id' => $orderId]);
})->addMiddleware(new ValidationMiddleware([
    'token' => 'required',
    'shipping_address' => 'required|min:10',
    'payment_method' => 'required'
]));

// Admin Routes Group
$app->group('/admin', function($router) use ($app) {
    // Admin Login Route
    $router->post('/login', function() use ($app) {
        error_log("Debug: Processing admin login request");
        
        $data = json_decode(file_get_contents('php://input'), true);
        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';
        
        if (empty($email) || empty($password)) {
            $app->json(['error' => 'Email and password are required'], 400);
            return;
        }
        
        try {
            $pdo = $app->db();
            $stmt = $pdo->prepare("SELECT ID, Name, Email, Password, Role FROM Users WHERE Email = ? AND Role = 'admin'");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user || !password_verify($password, $user['Password'])) {
                $app->json(['error' => 'Invalid email or password'], 401);
                return;
            }
            
            // Generate JWT token
            $token = generateJWT($user);
            
            // Start session and set session data
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION['user_id'] = $user['ID'];
            $_SESSION['user_role'] = $user['Role'];
            $_SESSION['user_name'] = $user['Name'];
            $_SESSION['user_email'] = $user['Email'];
            
            $app->json([
                'message' => 'Login successful',
                'token' => $token,
                'user' => [
                    'id' => $user['ID'],
                    'name' => $user['Name'],
                    'email' => $user['Email'],
                    'role' => $user['Role']
                ]
            ]);
            
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            $app->json(['error' => 'An error occurred during login'], 500);
        }
    });

    // Admin Dashboard Route
    $router->get('/dashboard', function() use ($app) {
        error_log("Debug: Accessing admin dashboard");
        
        // Check JWT token first
        $token = $app->getBearerToken();
        $isAdmin = false;
        $userData = null;
        
        if ($token) {
            try {
                $decoded = JWT::decode($token, new Key(JWT_SECRET, 'HS256'));
                if ($decoded->role === 'admin') {
                    $isAdmin = true;
                    $userData = [
                        'id' => $decoded->user_id,
                        'name' => $decoded->name,
                        'email' => $decoded->email,
                        'role' => $decoded->role
                    ];
                }
            } catch (Exception $e) {
                error_log("JWT validation failed: " . $e->getMessage());
            }
        }
        
        // If JWT check failed, try session
        if (!$isAdmin && session_status() === PHP_SESSION_ACTIVE) {
            error_log("Debug: Checking session data: " . print_r($_SESSION, true));
            if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
                $isAdmin = true;
                $userData = [
                    'id' => $_SESSION['user_id'],
                    'name' => $_SESSION['user_name'],
                    'email' => $_SESSION['user_email'],
                    'role' => $_SESSION['user_role']
                ];
            }
        }
        
        if ($isAdmin && $userData) {
            error_log("Debug: Admin access granted");
            $app->render('admin-dashboard', [
                'user' => $userData,
                'baseUrl' => BASE_URL
            ]);
        } else {
            error_log("Debug: Admin access denied, redirecting to login");
            $app->redirect('/login');
        }
    });

    // Admin Products Route
    $router->get('/products', function() use ($app) {
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            $app->redirect('/login');
            return;
        }
        $app->render('admin-products', ['baseUrl' => BASE_URL]);
    });

    // Admin Orders Route
    $router->get('/orders', function() use ($app) {
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            $app->redirect('/login');
            return;
        }
        $app->render('admin-orders', ['baseUrl' => BASE_URL]);
    });

    // Admin Users Route
    $router->get('/users', function() use ($app) {
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            $app->redirect('/login');
            return;
        }
        $app->render('admin-users', ['baseUrl' => BASE_URL]);
    });
});

// User Routes Group
$app->group('/user', function($router) use ($app) {
    // User Profile Route
    $router->get('/profile', function() use ($app) {
        error_log("Debug: Accessing user profile route");
        
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            error_log("Debug: User not logged in, redirecting to login");
            $app->redirect('/login');
            return;
        }
        
        try {
            $pdo = $app->db();
            // Get user details
            $stmt = $pdo->prepare("SELECT ID, Name, Email, Role FROM Users WHERE ID = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user) {
                error_log("Debug: User not found in database");
                $app->redirect('/login');
                return;
            }
            
            // Get user's order history
            $stmt = $pdo->prepare("SELECT * FROM Orders WHERE User_ID = ? ORDER BY Created_at DESC");
            $stmt->execute([$_SESSION['user_id']]);
            $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            error_log("Debug: Rendering user profile with data");
            $app->render('user-profile', [
                'user' => [
                    'username' => $user['Name'],
                    'email' => $user['Email'],
                    'role' => $user['Role']
                ],
                'orders' => $orders,
                'baseUrl' => BASE_URL
            ]);
            
        } catch (PDOException $e) {
            error_log("Database error in user profile: " . $e->getMessage());
            $app->render('user-profile', [
                'error' => 'An error occurred while fetching your profile. Please try again later.',
                'baseUrl' => BASE_URL
            ]);
        }
    });

    // Update User Profile Route
    $router->post('/profile', function() use ($app) {
        // ... existing user profile update code ...
    });
});

// Legacy route redirects for backward compatibility
$app->route('GET /admin-dashboard.html', function() use ($app) {
    $app->redirect('/admin/dashboard');
});

$app->route('GET /admin-dashboard', function() use ($app) {
    $app->redirect('/admin/dashboard');
});

$app->route('GET /user-profile', function() use ($app) {
    $app->redirect('/user/profile');
});

// API Documentation routes
$app->route('GET /api-docs', function() {
    include __DIR__ . '/api-docs.php';
});

$app->route('GET /openapi.yaml', function() {
    header('Content-Type: text/yaml');
    readfile(__DIR__ . '/openapi.yaml');
});

// User management routes
$app->route('GET /users', function() use ($app) {
    if (!$app->isApiRequest()) {
        $app->render('users');
        return;
    }

    $app->requireAdmin();
    
    $db = $app->db();
    $stmt = $db->prepare("SELECT id, name, email, role, created_at FROM users");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $app->json($users);
});

$app->route('POST /users', function() use ($app) {
    if (!$app->isApiRequest()) {
        $app->render('users');
        return;
    }

    $app->requireAdmin();
    
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data || !isset($data['name']) || !isset($data['email']) || !isset($data['password'])) {
        $app->json(['error' => 'Name, email, and password are required'], 400);
        return;
    }

    $db = $app->db();
    
    // Check if email already exists
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$data['email']]);
    if ($stmt->fetch()) {
        $app->json(['error' => 'Email already exists'], 400);
        return;
    }

    $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
    $role = $data['role'] ?? 'user';
    
    $stmt = $db->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt->execute([$data['name'], $data['email'], $hashedPassword, $role]);
    
    $userId = $db->lastInsertId();
    $user = [
        'id' => $userId,
        'name' => $data['name'],
        'email' => $data['email'],
        'role' => $role
    ];
    
    $app->json($user, 201);
});

$app->route('PUT /users/@id', function($id) use ($app) {
    $app->requireAdmin();
    
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) {
        $app->json(['error' => 'Invalid JSON data'], 400);
        return;
    }

    if (empty($data['name']) || empty($data['email'])) {
        $app->json(['error' => 'Name and email are required'], 400);
        return;
    }

    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $app->json(['error' => 'Invalid email format'], 400);
        return;
    }

    if (isset($data['role']) && !in_array($data['role'], ['user', 'admin'])) {
        $app->json(['error' => 'Invalid role. Must be either "user" or "admin"'], 400);
        return;
    }

    try {
        $db = $app->db();
        
        // Check if user exists
        $stmt = $db->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([$id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            $app->json(['error' => 'User not found'], 404);
            return;
        }
        
        // Check if email is already taken by another user
        $stmt = $db->prepare('SELECT id FROM users WHERE email = ? AND id != ?');
        $stmt->execute([$data['email'], $id]);
        if ($stmt->fetch()) {
            $app->json(['error' => 'Email already taken'], 400);
            return;
        }
        
        // Update user
        $stmt = $db->prepare('UPDATE users SET name = ?, email = ?, role = ? WHERE id = ?');
        $stmt->execute([
            $data['name'],
            $data['email'],
            $data['role'] ?? $user['role'],
            $id
        ]);
        
        // Get updated user
        $stmt = $db->prepare('SELECT id, name, email, role, created_at FROM users WHERE id = ?');
        $stmt->execute([$id]);
        $updatedUser = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $app->json($updatedUser);
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        $app->json(['error' => 'Failed to update user'], 500);
    }
});

$app->route('DELETE /users/@id', function($id) use ($app) {
    $app->requireAdmin();
    
    try {
        $db = $app->db();
        
        // Check if user exists
        $stmt = $db->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([$id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            $app->json(['error' => 'User not found'], 404);
            return;
        }
        
        // Check if user has any associated orders
        $stmt = $db->prepare('SELECT COUNT(*) FROM orders WHERE user_id = ?');
        $stmt->execute([$id]);
        $orderCount = $stmt->fetchColumn();
        
        if ($orderCount > 0) {
            $app->json(['error' => 'Cannot delete user with associated orders'], 400);
            return;
        }
        
        // Delete user
        $stmt = $db->prepare('DELETE FROM users WHERE id = ?');
        $stmt->execute([$id]);
        
        $app->json(['message' => 'User deleted successfully']);
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        $app->json(['error' => 'Failed to delete user'], 500);
    }
});

// Product routes
$app->route('GET /products', function() use ($app) {
    if ($app->isApiRequest()) {
        $db = $app->db();
        $categoryId = $_GET['category_id'] ?? null;
        
        if ($categoryId) {
            $stmt = $db->prepare("SELECT * FROM products WHERE category_id = ?");
            $stmt->execute([$categoryId]);
        } else {
            $stmt = $db->prepare("SELECT * FROM products");
            $stmt->execute();
        }
        
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $app->json($products);
    } else {
        $app->render('products');
    }
});

$app->route('GET /products/@id', function($id) use ($app) {
    try {
        $db = $app->db();
        
        // Get product details
        $stmt = $db->prepare('SELECT p.*, c.name as category_name 
                            FROM products p 
                            LEFT JOIN categories c ON p.category_id = c.id 
                            WHERE p.id = ?');
        $stmt->execute([$id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$product) {
            $app->json(['error' => 'Product not found'], 404);
            return;
        }
        
        if ($app->isApiRequest()) {
            $app->json($product);
        } else {
            $app->render('products/show', ['product' => $product]);
        }
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        $app->json(['error' => 'Failed to retrieve product'], 500);
    }
});

$app->route('POST /products', function() use ($app) {
    if (!$app->isApiRequest()) {
        $app->render('products');
        return;
    }

    $app->requireAdmin();
    
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data || !isset($data['name']) || !isset($data['price']) || !isset($data['category_id'])) {
        $app->json(['error' => 'Name, price, and category_id are required'], 400);
        return;
    }

    $db = $app->db();
    $stmt = $db->prepare("INSERT INTO products (name, description, price, stock, category_id) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([
        $data['name'],
        $data['description'] ?? null,
        $data['price'],
        $data['stock'] ?? 0,
        $data['category_id']
    ]);
    
    $productId = $db->lastInsertId();
    $product = [
        'id' => $productId,
        'name' => $data['name'],
        'description' => $data['description'] ?? null,
        'price' => $data['price'],
        'stock' => $data['stock'] ?? 0,
        'category_id' => $data['category_id']
    ];
    
    $app->json($product, 201);
});

$app->route('PUT /products/@id', function($id) use ($app) {
    $app->requireAdmin();
    
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) {
        $app->json(['error' => 'Invalid JSON data'], 400);
        return;
    }

    // Validate required fields
    if (empty($data['name'])) {
        $app->json(['error' => 'Product name is required'], 400);
        return;
    }

    if (!isset($data['price']) || !is_numeric($data['price']) || $data['price'] < 0) {
        $app->json(['error' => 'Valid price is required'], 400);
        return;
    }

    if (!isset($data['category_id']) || !is_numeric($data['category_id'])) {
        $app->json(['error' => 'Valid category ID is required'], 400);
        return;
    }

    if (isset($data['stock']) && (!is_numeric($data['stock']) || $data['stock'] < 0)) {
        $app->json(['error' => 'Stock must be a non-negative number'], 400);
        return;
    }

    try {
        $db = $app->db();
        
        // Check if product exists
        $stmt = $db->prepare('SELECT * FROM products WHERE id = ?');
        $stmt->execute([$id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$product) {
            $app->json(['error' => 'Product not found'], 404);
            return;
        }
        
        // Check if category exists
        $stmt = $db->prepare('SELECT id FROM categories WHERE id = ?');
        $stmt->execute([$data['category_id']]);
        if (!$stmt->fetch()) {
            $app->json(['error' => 'Category not found'], 400);
            return;
        }
        
        // Update product
        $stmt = $db->prepare('UPDATE products SET 
            name = ?, 
            description = ?, 
            price = ?, 
            stock = ?, 
            category_id = ? 
            WHERE id = ?');
            
        $stmt->execute([
            $data['name'],
            $data['description'] ?? null,
            $data['price'],
            $data['stock'] ?? 0,
            $data['category_id'],
            $id
        ]);
        
        // Get updated product with category name
        $stmt = $db->prepare('SELECT p.*, c.name as category_name 
                            FROM products p 
                            LEFT JOIN categories c ON p.category_id = c.id 
                            WHERE p.id = ?');
        $stmt->execute([$id]);
        $updatedProduct = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $app->json($updatedProduct);
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        $app->json(['error' => 'Failed to update product'], 500);
    }
});

$app->route('DELETE /products/@id', function($id) use ($app) {
    $app->requireAdmin();
    
    try {
        $db = $app->db();
        
        // Check if product exists
        $stmt = $db->prepare('SELECT * FROM products WHERE id = ?');
        $stmt->execute([$id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$product) {
            $app->json(['error' => 'Product not found'], 404);
            return;
        }
        
        // Check if product is in any orders
        $stmt = $db->prepare('SELECT COUNT(*) FROM order_items WHERE product_id = ?');
        $stmt->execute([$id]);
        $orderCount = $stmt->fetchColumn();
        
        if ($orderCount > 0) {
            $app->json(['error' => 'Cannot delete product that is associated with orders'], 400);
            return;
        }
        
        // Delete product
        $stmt = $db->prepare('DELETE FROM products WHERE id = ?');
        $stmt->execute([$id]);
        
        $app->json(['message' => 'Product deleted successfully']);
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        $app->json(['error' => 'Failed to delete product'], 500);
    }
});

// Function to ensure orders and order_items tables exist
function ensureOrderTables() {
    try {
        $db = getDbConnection();
        
        // Create orders table if it doesn't exist
        $db->exec("CREATE TABLE IF NOT EXISTS orders (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT,
            total_amount DECIMAL(10, 2),
            status ENUM('pending', 'shipped', 'delivered') DEFAULT 'pending',
            shipping_address TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id)
        )");
        
        // Create order_items table if it doesn't exist
        $db->exec("CREATE TABLE IF NOT EXISTS order_items (
            id INT AUTO_INCREMENT PRIMARY KEY,
            order_id INT,
            product_id INT,
            quantity INT,
            price DECIMAL(10, 2),
            FOREIGN KEY (order_id) REFERENCES orders(id),
            FOREIGN KEY (product_id) REFERENCES products(id)
        )");
    } catch (PDOException $e) {
        error_log("Error creating order tables: " . $e->getMessage());
        throw $e;
    }
}

// Order routes
$app->route('POST /orders', function() use ($app) {
    try {
        $token = $app->getBearerToken();
        if (!$token) {
            $app->json(['error' => 'No token provided'], 401);
            return;
        }
        $payload = validateJWT($token);
        if (!$payload) {
            $app->json(['error' => 'Invalid token'], 401);
            return;
        }
        $userId = $payload['user_id'];
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data) {
            $app->json(['error' => 'Invalid JSON data'], 400);
            return;
        }
        if (empty($data['shipping_address'])) {
            $app->json(['error' => 'Shipping address is required'], 400);
            return;
        }
        $db = $app->db();
        $db->beginTransaction();
        try {
            // Get cart items with product details
            $stmt = $db->prepare('
                SELECT c.*, p.name, p.price, p.stock 
                FROM cart c 
                JOIN products p ON c.product_id = p.id 
                WHERE c.user_id = ?
            ');
            $stmt->execute([$userId]);
            $cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (empty($cartItems)) {
                throw new Exception('Cart is empty');
            }

            // Calculate total and check stock
            $totalAmount = 0;
            foreach ($cartItems as $item) {
                $totalAmount += $item['price'] * $item['quantity'];
                if ($item['stock'] < $item['quantity']) {
                    throw new Exception("Not enough stock for product: {$item['name']}");
                }
            }

            // Create order
            $stmt = $db->prepare('
                INSERT INTO orders (user_id, total_amount, status, shipping_address) 
                VALUES (?, ?, ?, ?)
            ');
            $stmt->execute([$userId, $totalAmount, 'pending', $data['shipping_address']]);
            $orderId = $db->lastInsertId();

            // Create order items and update stock
            foreach ($cartItems as $item) {
                // Add order item
                $stmt = $db->prepare('
                    INSERT INTO order_items (order_id, product_id, quantity, price) 
                    VALUES (?, ?, ?, ?)
                ');
                $stmt->execute([
                    $orderId,
                    $item['product_id'],
                    $item['quantity'],
                    $item['price']
                ]);

                // Update product stock
                $stmt = $db->prepare('
                    UPDATE products 
                    SET stock = stock - ? 
                    WHERE id = ?
                ');
                $stmt->execute([$item['quantity'], $item['product_id']]);
            }

            // Clear cart
            $stmt = $db->prepare('DELETE FROM cart WHERE user_id = ?');
            $stmt->execute([$userId]);

            $db->commit();

            // Get created order with items
            $stmt = $db->prepare('
                SELECT o.*, 
                       COALESCE(
                           GROUP_CONCAT(
                               JSON_OBJECT(
                                   "product_id", oi.product_id,
                                   "quantity", oi.quantity,
                                   "price", oi.price,
                                   "name", p.name
                               )
                           ),
                           "[]"
                       ) as items
                FROM orders o
                LEFT JOIN order_items oi ON o.id = oi.order_id
                LEFT JOIN products p ON oi.product_id = p.id
                WHERE o.id = ?
                GROUP BY o.id
            ');
            $stmt->execute([$orderId]);
            $order = $stmt->fetch(PDO::FETCH_ASSOC);
            $order['items'] = json_decode($order['items'], true);

            $app->json([
                'message' => 'Order created successfully',
                'order' => $order
            ], 201);

        } catch (Exception $e) {
            $db->rollBack();
            $app->json(['error' => 'Failed to create order: ' . $e->getMessage()], 500);
        }
    } catch (Exception $e) {
        error_log("Error creating order: " . $e->getMessage());
        $app->json(['error' => 'Failed to create order: ' . $e->getMessage()], 500);
    }
});

// Add a catch-all route for debugging
$app->route('GET /users/@id', function($id) use ($app) {
    try {
        // Validate JWT token
        $token = $app->getBearerToken();
        if (!$token) {
            $app->json(['error' => 'No token provided'], 401);
            return;
        }

        $payload = validateJWT($token);
        if (!$payload) {
            sendJsonResponse(['error' => 'Invalid token'], 401);
            return;
        }

        // Only allow users to access their own data or admins to access any user
        if ($payload['user_id'] != $id && $payload['role'] !== 'admin') {
            sendJsonResponse(['error' => 'Unauthorized access'], 403);
            return;
        }

        $db = getDbConnection();
        
        // Get user details
        $stmt = $db->prepare('SELECT id, name, email, role, created_at FROM users WHERE id = ?');
        $stmt->execute([$id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            sendJsonResponse(['error' => 'User not found'], 404);
            return;
        }
        
        sendJsonResponse($user);
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        $app->json(['error' => 'Failed to retrieve user'], 500);
    }
});

// Add a catch-all route for debugging
$app->route('GET /orders/@id', function($id) use ($app) {
    try {
        // Get and validate JWT token
        $token = $app->getBearerToken();
        if (!$token) {
            $app->json(['error' => 'No token provided'], 401);
            return;
        }

        $payload = validateJWT($token);
        if (!$payload) {
            $app->json(['error' => 'Invalid token'], 401);
            return;
        }

        $userId = $payload['user_id'];
        $isAdmin = $payload['role'] === 'admin';
        
        $db = $app->db();
        
        // Get order details with items
        $stmt = $db->prepare('
            SELECT o.*, 
                   COALESCE(
                       GROUP_CONCAT(
                           JSON_OBJECT(
                               "product_id", oi.product_id,
                               "quantity", oi.quantity,
                               "price", oi.price,
                               "name", p.name
                           )
                       ),
                       "[]"
                   ) as items
            FROM orders o
            LEFT JOIN order_items oi ON o.id = oi.order_id
            LEFT JOIN products p ON oi.product_id = p.id
            WHERE o.id = ?
            GROUP BY o.id
        ');
        $stmt->execute([$id]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$order) {
            $app->json(['error' => 'Order not found'], 404);
            return;
        }
        
        // Check if user has permission to view this order
        if (!$isAdmin && $order['user_id'] != $userId) {
            $app->json(['error' => 'Unauthorized access'], 403);
            return;
        }
        
        // Parse items JSON
        $order['items'] = json_decode($order['items'], true);
        
        $app->json($order);
    } catch (Exception $e) {
        error_log("Error retrieving order: " . $e->getMessage());
        $app->json(['error' => 'Failed to retrieve order: ' . $e->getMessage()], 500);
    }
});

// Add a catch-all route for debugging
$app->route('GET /orders', function() use ($app) {
    try {
        // Get and validate JWT token
        $token = $app->getBearerToken();
        if (!$token) {
            $app->json(['error' => 'No token provided'], 401);
            return;
        }

        $payload = validateJWT($token);
        if (!$payload) {
            $app->json(['error' => 'Invalid token'], 401);
            return;
        }

        $userId = $payload['user_id'];
        $isAdmin = $payload['role'] === 'admin';
        
        $db = $app->db();
        
        // Prepare the base query
        $query = '
            SELECT o.*, 
                   COALESCE(
                       GROUP_CONCAT(
                           JSON_OBJECT(
                               "product_id", oi.product_id,
                               "quantity", oi.quantity,
                               "price", oi.price,
                               "name", p.name
                           )
                       ),
                       "[]"
                   ) as items
            FROM orders o
            LEFT JOIN order_items oi ON o.id = oi.order_id
            LEFT JOIN products p ON oi.product_id = p.id
        ';
        
        // Add WHERE clause based on user role
        if (!$isAdmin) {
            $query .= ' WHERE o.user_id = ?';
        }
        
        $query .= ' GROUP BY o.id ORDER BY o.created_at DESC';
        
        $stmt = $db->prepare($query);
        
        // Execute with or without user_id parameter
        if (!$isAdmin) {
            $stmt->execute([$userId]);
        } else {
            $stmt->execute();
        }
        
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Parse items JSON for each order
        foreach ($orders as &$order) {
            $order['items'] = json_decode($order['items'], true);
        }
        
        $app->json($orders);
    } catch (Exception $e) {
        error_log("Error retrieving orders: " . $e->getMessage());
        $app->json(['error' => 'Failed to retrieve orders: ' . $e->getMessage()], 500);
    }
});

// Add GET /login route for browser
$app->route('GET /login', function() use ($app) {
    if ($app->isLoggedIn()) {
        $app->redirect('/');
        return;
    }
    $app->render('login', [
        'baseUrl' => BASE_URL,
        'error' => '',
        'email' => ''
    ]);
});

// Register routes
$app->route('GET /register', function() use ($app) {
    if ($app->isLoggedIn()) {
        $app->redirect('/');
        return;
    }
    $app->render('register', [
        'baseUrl' => BASE_URL,
        'error' => '',
        'username' => '',
        'email' => ''
    ]);
});

$app->route('POST /register', function() use ($app) {
    if ($app->isApiRequest()) {
        // Handle API request
        $data = json_decode(file_get_contents('php://input'), true);
    } else {
        // Handle form submission
        $data = [
            'name' => $_POST['username'] ?? '',
            'email' => $_POST['email'] ?? '',
            'password' => $_POST['password'] ?? '',
            'confirmPassword' => $_POST['confirmPassword'] ?? ''
        ];
    }
    
    if (!$data) {
        if ($app->isApiRequest()) {
            $app->json(['error' => 'Invalid JSON data'], 400);
        } else {
            $app->render('register', [
                'error' => 'Invalid form data',
                'username' => $data['name'] ?? '',
                'email' => $data['email'] ?? '',
                'baseUrl' => BASE_URL
            ]);
        }
        return;
    }
    
    // Validate required fields
    if (empty($data['name']) || empty($data['email']) || empty($data['password'])) {
        if ($app->isApiRequest()) {
            $app->json(['error' => 'Name, email and password are required'], 400);
        } else {
            $app->render('register', [
                'error' => 'Name, email and password are required',
                'username' => $data['name'] ?? '',
                'email' => $data['email'] ?? '',
                'baseUrl' => BASE_URL
            ]);
        }
        return;
    }
    
    // Validate password confirmation for form submission
    if (!$app->isApiRequest() && $data['password'] !== $data['confirmPassword']) {
        $app->render('register', [
            'error' => 'Passwords do not match',
            'username' => $data['name'],
            'email' => $data['email'],
            'baseUrl' => BASE_URL
        ]);
        return;
    }
    
    try {
        $pdo = $app->db();
        
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT ID FROM Users WHERE Email = ?");
        $stmt->execute([$data['email']]);
        if ($stmt->fetch()) {
            if ($app->isApiRequest()) {
                $app->json(['error' => 'Email already registered'], 400);
            } else {
                $app->render('register', [
                    'error' => 'Email already registered',
                    'username' => $data['name'],
                    'email' => $data['email'],
                    'baseUrl' => BASE_URL
                ]);
            }
            return;
        }
        
        // Hash password
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
        
        // Insert new user
        $stmt = $pdo->prepare("INSERT INTO Users (Name, Email, Password, Role) VALUES (?, ?, ?, 'user')");
        $stmt->execute([$data['name'], $data['email'], $hashedPassword]);
        
        // Get the new user's ID
        $userId = $pdo->lastInsertId();
        
        if ($app->isApiRequest()) {
            // Return success response for API
            $app->json([
                'message' => 'Registration successful',
                'user' => [
                    'id' => $userId,
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'role' => 'user'
                ]
            ]);
        } else {
            // Redirect to login page for form submission
            $app->redirect('/login');
        }
        
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        if ($app->isApiRequest()) {
            $app->json(['error' => 'Internal server error'], 500);
        } else {
            $app->render('register', [
                'error' => 'An error occurred during registration',
                'username' => $data['name'],
                'email' => $data['email'],
                'baseUrl' => BASE_URL
            ]);
        }
    }
});

// Middleware test page route
$app->route('GET /middleware-test', function() use ($app) {
    $app->render('middleware-test', [
        'baseUrl' => BASE_URL
    ]);
});

// Test route for middleware
$app->route('POST /test-middleware', function() use ($app) {
    $data = $app->request()->data;
    
    // Test validation
    if (isset($data['test_validation'])) {
        if (!isset($data['required_field'])) {
            $app->json(['error' => 'Required field is missing'], 400);
            return;
        }
        
        if (isset($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $app->json(['error' => 'Invalid email format'], 400);
            return;
        }
        
        if (isset($data['min_length']) && strlen($data['min_length']) < 3) {
            $app->json(['error' => 'Field must be at least 3 characters'], 400);
            return;
        }
        
        if (isset($data['max_length']) && strlen($data['max_length']) > 10) {
            $app->json(['error' => 'Field must not exceed 10 characters'], 400);
            return;
        }
        
        if (isset($data['numeric']) && !is_numeric($data['numeric'])) {
            $app->json(['error' => 'Field must be numeric'], 400);
            return;
        }
    }
    
    // Test error handling
    if (isset($data['test_error'])) {
        throw new Exception('Test error message');
    }
    
    // Test success response
    $app->json([
        'message' => 'Middleware test successful',
        'data' => $data
    ]);
})->addMiddleware(new ValidationMiddleware([
    'required_field' => 'required',
    'email' => 'email',
    'min_length' => 'min:3',
    'max_length' => 'max:10',
    'numeric' => 'numeric'
]));

// Catch-all route (should be last)
$app->route('*', function() {
    debug_log("No route matched for: " . $_SERVER['REQUEST_URI']);
    header("HTTP/1.0 404 Not Found");
    echo "No route matched for: " . $_SERVER['REQUEST_URI'];
});

// Start the application
$app->start();