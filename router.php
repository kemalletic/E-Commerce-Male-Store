<?php
require 'vendor/autoload.php';

use flight\Engine;

$app = new Engine();

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define JWT secret key as a constant
define('JWT_SECRET', 'your-secret-key-123');

// Database connection function
function getDbConnection() {
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
        sendJsonResponse(['error' => 'Database connection failed'], 500);
    }
}

// Set the base directory for views
$app->set('flight.views.path', __DIR__ . '/frontend/views');

// Define base URL - remove trailing slash if present
$baseUrl = rtrim('/E-Commerce-Male-Store-main', '/');

// Make baseUrl available to all views
$app->set('baseUrl', $baseUrl);

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
    global $baseUrl;
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
            
            // Page links - handle both old and new formats
            'href="./pages/shirts.html"' => 'href="' . $baseUrl . '/shirts"',
            'href="./pages/jackets.html"' => 'href="' . $baseUrl . '/jackets"',
            'href="./pages/perfumes.html"' => 'href="' . $baseUrl . '/perfumes"',
            'href="./pages/sneakers.html"' => 'href="' . $baseUrl . '/sneakers"',
            'href="./pages/tracksuits.html"' => 'href="' . $baseUrl . '/tracksuits"',
            'href="./pages/cart.html"' => 'href="' . $baseUrl . '/cart"',
            'href="./pages/user-profile.html"' => 'href="' . $baseUrl . '/user-profile"',
            'href="./pages/admin-dashboard.html"' => 'href="' . $baseUrl . '/admin/dashboard"',
            'href="./pages/login.html"' => 'href="' . $baseUrl . '/login"',
            'href="./pages/register.html"' => 'href="' . $baseUrl . '/register"',
            'href="./pages/checkout.html"' => 'href="' . $baseUrl . '/checkout"',
            'href="./pages/categories.html"' => 'href="' . $baseUrl . '/categories"',
            'href="./pages/manage-products.html"' => 'href="' . $baseUrl . '/admin/manage-products"',
            'href="./pages/manage-orders.html"' => 'href="' . $baseUrl . '/admin/manage-orders"',
            'href="./pages/manage-users.html"' => 'href="' . $baseUrl . '/admin/manage-users"',
            
            // Also handle links without ./pages/
            'href="shirts.html"' => 'href="' . $baseUrl . '/shirts"',
            'href="jackets.html"' => 'href="' . $baseUrl . '/jackets"',
            'href="perfumes.html"' => 'href="' . $baseUrl . '/perfumes"',
            'href="sneakers.html"' => 'href="' . $baseUrl . '/sneakers"',
            'href="tracksuits.html"' => 'href="' . $baseUrl . '/tracksuits"',
            'href="cart.html"' => 'href="' . $baseUrl . '/cart"',
            'href="user-profile.html"' => 'href="' . $baseUrl . '/user-profile"',
            'href="admin-dashboard.html"' => 'href="' . $baseUrl . '/admin/dashboard"',
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
        header("HTTP/1.0 404 Not Found");
        echo "Page not found: " . $filepath;
        debug_log("404 Error - File not found: " . $filepath);
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
$app->route('GET /categories', function() {
    $db = getDbConnection();
    $stmt = $db->query('SELECT * FROM categories');
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (isApiRequest()) {
        sendJsonResponse($categories);
    } else {
        Flight::render('categories/index', ['categories' => $categories]);
    }
});

$app->route('GET /categories/@id', function($id) {
    try {
        $db = getDbConnection();
        $stmt = $db->prepare('SELECT * FROM categories WHERE id = ?');
        $stmt->execute([$id]);
        $category = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$category) {
            sendJsonResponse(['error' => 'Category not found'], 404);
            return;
        }
        
        if (isApiRequest()) {
            sendJsonResponse($category);
        } else {
            Flight::render('categories/show', ['category' => $category]);
        }
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        sendJsonResponse(['error' => 'Failed to retrieve category'], 500);
    }
});

$app->route('PUT /categories/@id', function($id) {
    requireAdmin();
    
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) {
        sendJsonResponse(['error' => 'Invalid JSON data'], 400);
        return;
    }

    if (empty($data['name'])) {
        sendJsonResponse(['error' => 'Category name is required'], 400);
        return;
    }

    try {
        $db = getDbConnection();
        
        // Check if category exists
        $stmt = $db->prepare('SELECT * FROM categories WHERE id = ?');
        $stmt->execute([$id]);
        $category = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$category) {
            sendJsonResponse(['error' => 'Category not found'], 404);
            return;
        }
        
        // Update category
        $stmt = $db->prepare('UPDATE categories SET name = ?, description = ? WHERE id = ?');
        $stmt->execute([$data['name'], $data['description'] ?? null, $id]);
        
        // Get updated category
        $stmt = $db->prepare('SELECT * FROM categories WHERE id = ?');
        $stmt->execute([$id]);
        $updatedCategory = $stmt->fetch(PDO::FETCH_ASSOC);
        
        sendJsonResponse($updatedCategory);
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        sendJsonResponse(['error' => 'Failed to update category'], 500);
    }
});

$app->route('DELETE /categories/@id', function($id) {
    requireAdmin();
    
    try {
        $db = getDbConnection();
        
        // Check if category exists
        $stmt = $db->prepare('SELECT * FROM categories WHERE id = ?');
        $stmt->execute([$id]);
        $category = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$category) {
            sendJsonResponse(['error' => 'Category not found'], 404);
            return;
        }
        
        // Delete category
        $stmt = $db->prepare('DELETE FROM categories WHERE id = ?');
        $stmt->execute([$id]);
        
        sendJsonResponse(['message' => 'Category deleted successfully']);
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        sendJsonResponse(['error' => 'Failed to delete category'], 500);
    }
});

$app->route('POST /categories', function() {
    requireAdmin();
    
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) {
        sendJsonResponse(['error' => 'Invalid JSON data'], 400);
        return;
    }

    if (empty($data['name'])) {
        sendJsonResponse(['error' => 'Category name is required'], 400);
        return;
    }

    try {
        $db = getDbConnection();
        $stmt = $db->prepare('INSERT INTO categories (name, description) VALUES (?, ?)');
        $stmt->execute([$data['name'], $data['description'] ?? null]);
        
        $categoryId = $db->lastInsertId();
        $stmt = $db->prepare('SELECT * FROM categories WHERE id = ?');
        $stmt->execute([$categoryId]);
        $category = $stmt->fetch(PDO::FETCH_ASSOC);
        
        sendJsonResponse($category, 201);
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        sendJsonResponse(['error' => 'Failed to create category'], 500);
    }
});

// Shirts route
$app->route('GET /shirts', function() use ($app, $baseUrl) {
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
            'baseUrl' => $baseUrl
        ]);
    }
});

$app->route('POST /shirts/add-to-cart', function() use ($app, $baseUrl) {
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
});

// Jackets route
$app->route('GET /jackets', function() use ($app, $baseUrl) {
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
        'baseUrl' => $baseUrl,
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
$app->route('GET /sneakers', function() use ($app, $baseUrl) {
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
        'baseUrl' => $baseUrl,
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
$app->route('GET /tracksuits', function() use ($app, $baseUrl) {
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
        'baseUrl' => $baseUrl,
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
$app->route('GET /perfumes', function() use ($app, $baseUrl) {
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
        'baseUrl' => $baseUrl,
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
$app->route('POST /login', function() use ($app, $baseUrl) {
    error_log("Debug: Login route accessed");
    
    // Check if this is an API request
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
    $acceptHeader = $_SERVER['HTTP_ACCEPT'] ?? '';
    
    if (strpos($contentType, 'application/json') !== false || strpos($acceptHeader, 'application/json') !== false) {
        // Handle as API request
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        
        if (!$data) {
            header('Content-Type: application/json');
            http_response_code(400);
            echo json_encode(['error' => 'Invalid JSON data']);
            return;
        }
        
        // Validate required fields
        if (empty($data['email']) || empty($data['password'])) {
            header('Content-Type: application/json');
            http_response_code(400);
            echo json_encode(['error' => 'Email and password are required']);
            return;
        }
        
        try {
            // Connect to database
            $pdo = new PDO(
                "mysql:host=localhost;dbname=ecommerce",
                "root",
                "",
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
            
            // Get user by email
            $stmt = $pdo->prepare("SELECT ID, Name, Email, Password, Role FROM Users WHERE Email = ?");
            $stmt->execute([$data['email']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user || !password_verify($data['password'], $user['Password'])) {
                header('Content-Type: application/json');
                http_response_code(401);
                echo json_encode(['error' => 'Invalid email or password']);
                return;
            }
            
            // Generate JWT token
            $token = generateJWT($user);
            
            // Return success response with token
            header('Content-Type: application/json');
            echo json_encode([
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
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode(['error' => 'Internal server error']);
        }
    } else {
        // Handle as form submission
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        
        if (empty($email) || empty($password)) {
            $app->render('login', [
                'error' => 'Please fill in all fields',
                'email' => $email,
                'baseUrl' => $baseUrl
            ]);
            return;
        }
        
        try {
            // Connect to database
            $pdo = new PDO(
                "mysql:host=localhost;dbname=ecommerce",
                "root",
                "",
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
            
            // Get user by email
            $stmt = $pdo->prepare("SELECT ID, Name, Email, Password, Role FROM Users WHERE Email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user || !password_verify($password, $user['Password'])) {
                $app->render('login', [
                    'error' => 'Invalid email or password',
                    'email' => $email,
                    'baseUrl' => $baseUrl
                ]);
                return;
            }
            
            // Start session and set user data
            session_start();
            $_SESSION['user_id'] = $user['ID'];
            $_SESSION['role'] = $user['Role'];
            
            // Redirect to home page
            header('Location: ' . $baseUrl . '/');
            exit;
            
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            $app->render('login', [
                'error' => 'An error occurred. Please try again.',
                'email' => $email,
                'baseUrl' => $baseUrl
            ]);
        }
    }
});

// Register route
$app->route('GET /register', function() use ($app, $baseUrl) {
    error_log("Debug: Register route accessed");
    $app->render('register', [
        'baseUrl' => $baseUrl
    ]);
});

$app->route('POST /register', function() use ($app) {
    error_log("Debug: Register route accessed");
    
    // Get JSON input
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    
    if (!$data) {
        header('Content-Type: application/json');
        http_response_code(400);
        echo json_encode(['error' => 'Invalid JSON data']);
        return;
    }
    
    // Validate required fields
    if (empty($data['name']) || empty($data['email']) || empty($data['password'])) {
        header('Content-Type: application/json');
        http_response_code(400);
        echo json_encode(['error' => 'Name, email and password are required']);
        return;
    }
    
    // Validate role if provided
    $role = $data['role'] ?? 'user';
    if (!in_array($role, ['user', 'admin'])) {
        header('Content-Type: application/json');
        http_response_code(400);
        echo json_encode(['error' => 'Invalid role. Must be either "user" or "admin"']);
        return;
    }
    
    try {
        // Connect to database
        $pdo = new PDO(
            "mysql:host=localhost;dbname=ecommerce",
            "root",
            "",
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT ID FROM Users WHERE Email = ?");
        $stmt->execute([$data['email']]);
        if ($stmt->fetch()) {
            header('Content-Type: application/json');
            http_response_code(400);
            echo json_encode(['error' => 'Email already registered']);
            return;
        }
        
        // Hash password
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
        
        // Insert new user with role
        $stmt = $pdo->prepare("INSERT INTO Users (Name, Email, Password, Role) VALUES (?, ?, ?, ?)");
        $stmt->execute([$data['name'], $data['email'], $hashedPassword, $role]);
        
        // Get the new user's ID
        $userId = $pdo->lastInsertId();
        
        // Start session and set user data
        session_start();
        $_SESSION['user_id'] = $userId;
        $_SESSION['role'] = $role;
        
        // Return success response
        header('Content-Type: application/json');
        echo json_encode([
            'message' => 'Registration successful',
            'user' => [
                'id' => $userId,
                'name' => $data['name'],
                'email' => $data['email'],
                'role' => $role
            ]
        ]);
        
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode(['error' => 'Internal server error']);
    }
});

$app->route('POST /logout', function() use ($app) {
    session_start();
    session_destroy();
    
    header('Content-Type: application/json');
    echo json_encode(['message' => 'Logged out successfully']);
});

// User profile route
$app->route('GET /user-profile', function() use ($app, $baseUrl) {
    error_log("Debug: User profile route accessed");
    
    // TODO: Get user data from session/database
    $user = [
        'username' => 'test_user',
        'email' => 'test@example.com'
    ];
    
    // TODO: Get user's order history
    $orders = [
        [
            'id' => '1',
            'date' => '2024-03-15',
            'total' => '99.99',
            'status' => 'Delivered'
        ]
    ];
    
    $app->render('user-profile', [
        'user' => $user,
        'orders' => $orders,
        'baseUrl' => $baseUrl
    ]);
});

$app->route('POST /user-profile', function() use ($app, $baseUrl) {
    error_log("Debug: User profile POST request received");
    
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
            'baseUrl' => $baseUrl
        ]);
        return;
    }
    
    // TODO: Add user profile update logic here
    // For now, just show a success message
    $app->render('user-profile', [
        'success' => 'Profile updated successfully',
        'user' => [
            'username' => $username,
            'email' => $email
        ],
        'baseUrl' => $baseUrl
    ]);
});

// Function to ensure cart_items table exists
function ensureCartItemsTable() {
    try {
        $db = getDbConnection();
        $db->exec("CREATE TABLE IF NOT EXISTS cart_items (
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
        error_log("Error creating cart_items table: " . $e->getMessage());
        throw $e;
    }
}

// Cart routes
$app->route('POST /cart', function() {
    try {
        // Ensure cart_items table exists
        ensureCartItemsTable();
        
        // Get and validate JWT token
        $token = getBearerToken();
        if (!$token) {
            sendJsonResponse(['error' => 'No token provided'], 401);
            return;
        }

        $payload = validateJWT($token);
        if (!$payload) {
            sendJsonResponse(['error' => 'Invalid token'], 401);
            return;
        }

        $userId = $payload['user_id'];
        
        // Get and validate request data
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data) {
            sendJsonResponse(['error' => 'Invalid JSON data'], 400);
            return;
        }

        if (!isset($data['product_id']) || !is_numeric($data['product_id'])) {
            sendJsonResponse(['error' => 'Valid product ID is required'], 400);
            return;
        }

        if (!isset($data['quantity']) || !is_numeric($data['quantity']) || $data['quantity'] <= 0) {
            sendJsonResponse(['error' => 'Valid quantity is required'], 400);
            return;
        }

        $db = getDbConnection();
        
        // Check if product exists and has enough stock
        $stmt = $db->prepare('SELECT * FROM products WHERE id = ?');
        $stmt->execute([$data['product_id']]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$product) {
            sendJsonResponse(['error' => 'Product not found'], 404);
            return;
        }

        if ($product['stock'] < $data['quantity']) {
            sendJsonResponse(['error' => 'Not enough stock available'], 400);
            return;
        }

        // Check if item already exists in cart
        $stmt = $db->prepare('SELECT * FROM cart_items WHERE user_id = ? AND product_id = ?');
        $stmt->execute([$userId, $data['product_id']]);
        $existingItem = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existingItem) {
            // Update quantity if item exists
            $newQuantity = $existingItem['quantity'] + $data['quantity'];
            
            // Check if new total quantity exceeds stock
            if ($newQuantity > $product['stock']) {
                sendJsonResponse(['error' => 'Not enough stock available for requested quantity'], 400);
                return;
            }

            $stmt = $db->prepare('UPDATE cart_items SET quantity = ? WHERE user_id = ? AND product_id = ?');
            $stmt->execute([$newQuantity, $userId, $data['product_id']]);
        } else {
            // Add new item to cart
            $stmt = $db->prepare('INSERT INTO cart_items (user_id, product_id, quantity) VALUES (?, ?, ?)');
            $stmt->execute([$userId, $data['product_id'], $data['quantity']]);
        }

        // Get updated cart item with product details
        $stmt = $db->prepare('
            SELECT ci.*, p.name, p.price, p.stock 
            FROM cart_items ci 
            JOIN products p ON ci.product_id = p.id 
            WHERE ci.user_id = ? AND ci.product_id = ?
        ');
        $stmt->execute([$userId, $data['product_id']]);
        $cartItem = $stmt->fetch(PDO::FETCH_ASSOC);

        sendJsonResponse([
            'message' => 'Item added to cart successfully',
            'cart_item' => $cartItem
        ]);
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        sendJsonResponse(['error' => 'Failed to add item to cart: ' . $e->getMessage()], 500);
    }
});

// Cart route
$app->route('GET /cart', function() use ($app, $baseUrl) {
    try {
        // Get and validate JWT token
        $token = getBearerToken();
        if (!$token) {
            if (isApiRequest()) {
                sendJsonResponse(['error' => 'No token provided'], 401);
                return;
            }
            // For HTML requests, redirect to login
            header('Location: ' . $baseUrl . '/login');
            return;
        }

        $payload = validateJWT($token);
        if (!$payload) {
            if (isApiRequest()) {
                sendJsonResponse(['error' => 'Invalid token'], 401);
                return;
            }
            // For HTML requests, redirect to login
            header('Location: ' . $baseUrl . '/login');
            return;
        }

        $userId = $payload['user_id'];
        $db = getDbConnection();

        // Get cart items with product details
        $stmt = $db->prepare('
            SELECT ci.*, p.name, p.price, p.stock 
            FROM cart_items ci 
            JOIN products p ON ci.product_id = p.id 
            WHERE ci.user_id = ?
        ');
        $stmt->execute([$userId]);
        $cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Calculate totals
        $subtotal = 0;
        foreach ($cartItems as $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }
        $shipping = 10.00; // Fixed shipping cost
        $total = $subtotal + $shipping;

        if (isApiRequest()) {
            // Return JSON response for API requests
            sendJsonResponse([
                'items' => $cartItems,
                'subtotal' => number_format($subtotal, 2),
                'shipping' => number_format($shipping, 2),
                'total' => number_format($total, 2)
            ]);
        } else {
            // Return HTML response for browser requests
            $app->render('cart', [
                'cart' => [
                    'items' => $cartItems,
                    'subtotal' => number_format($subtotal, 2),
                    'shipping' => number_format($shipping, 2),
                    'total' => number_format($total, 2)
                ],
                'baseUrl' => $baseUrl
            ]);
        }
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        if (isApiRequest()) {
            sendJsonResponse(['error' => 'Failed to retrieve cart: ' . $e->getMessage()], 500);
        } else {
            $app->render('error', [
                'error' => 'Failed to retrieve cart',
                'baseUrl' => $baseUrl
            ]);
        }
    }
});

$app->route('POST /cart/update', function() {
    try {
        // Get and validate JWT token
        $token = getBearerToken();
        if (!$token) {
            sendJsonResponse(['error' => 'No token provided'], 401);
            return;
        }

        $payload = validateJWT($token);
        if (!$payload) {
            sendJsonResponse(['error' => 'Invalid token'], 401);
            return;
        }

        $userId = $payload['user_id'];
        
        // Get and validate request data
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data) {
            sendJsonResponse(['error' => 'Invalid JSON data'], 400);
            return;
        }

        if (!isset($data['product_id']) || !is_numeric($data['product_id'])) {
            sendJsonResponse(['error' => 'Valid product ID is required'], 400);
            return;
        }

        if (!isset($data['quantity']) || !is_numeric($data['quantity']) || $data['quantity'] < 0) {
            sendJsonResponse(['error' => 'Valid quantity is required'], 400);
            return;
        }

        $db = getDbConnection();
        
        // Check if product exists and has enough stock
        $stmt = $db->prepare('SELECT * FROM products WHERE id = ?');
        $stmt->execute([$data['product_id']]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$product) {
            sendJsonResponse(['error' => 'Product not found'], 404);
            return;
        }

        // Check if item exists in cart
        $stmt = $db->prepare('SELECT * FROM cart_items WHERE user_id = ? AND product_id = ?');
        $stmt->execute([$userId, $data['product_id']]);
        $existingItem = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$existingItem) {
            sendJsonResponse(['error' => 'Item not found in cart'], 404);
            return;
        }

        // If quantity is 0, remove item from cart
        if ($data['quantity'] === 0) {
            $stmt = $db->prepare('DELETE FROM cart_items WHERE user_id = ? AND product_id = ?');
            $stmt->execute([$userId, $data['product_id']]);
            sendJsonResponse([
                'message' => 'Item removed from cart',
                'cart_item' => null
            ]);
            return;
        }

        // Check if new quantity exceeds stock
        if ($data['quantity'] > $product['stock']) {
            sendJsonResponse(['error' => 'Not enough stock available for requested quantity'], 400);
            return;
        }

        // Update quantity
        $stmt = $db->prepare('UPDATE cart_items SET quantity = ? WHERE user_id = ? AND product_id = ?');
        $stmt->execute([$data['quantity'], $userId, $data['product_id']]);

        // Get updated cart item with product details
        $stmt = $db->prepare('
            SELECT ci.*, p.name, p.price, p.stock 
            FROM cart_items ci 
            JOIN products p ON ci.product_id = p.id 
            WHERE ci.user_id = ? AND ci.product_id = ?
        ');
        $stmt->execute([$userId, $data['product_id']]);
        $cartItem = $stmt->fetch(PDO::FETCH_ASSOC);

        sendJsonResponse([
            'message' => 'Cart updated successfully',
            'cart_item' => $cartItem
        ]);
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        sendJsonResponse(['error' => 'Failed to update cart: ' . $e->getMessage()], 500);
    }
});

$app->route('DELETE /cart/@id', function($id) {
    try {
        // Get and validate JWT token
        $token = getBearerToken();
        if (!$token) {
            sendJsonResponse(['error' => 'No token provided'], 401);
            return;
        }

        $payload = validateJWT($token);
        if (!$payload) {
            sendJsonResponse(['error' => 'Invalid token'], 401);
            return;
        }

        $userId = $payload['user_id'];
        
        $db = getDbConnection();
        
        // Check if the item exists in the user's cart
        $stmt = $db->prepare('SELECT * FROM cart_items WHERE user_id = ? AND product_id = ?');
        $stmt->execute([$userId, $id]);
        $cartItem = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$cartItem) {
            sendJsonResponse(['error' => 'Item not found in cart'], 404);
            return;
        }
        
        // Delete the item from cart
        $stmt = $db->prepare('DELETE FROM cart_items WHERE user_id = ? AND product_id = ?');
        $stmt->execute([$userId, $id]);
        
        sendJsonResponse([
            'message' => 'Item removed from cart successfully',
            'removed_item' => [
                'product_id' => $id,
                'quantity' => $cartItem['quantity']
            ]
        ]);
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        sendJsonResponse(['error' => 'Failed to remove item from cart: ' . $e->getMessage()], 500);
    }
});

// Checkout route
$app->route('GET /checkout', function() use ($app, $baseUrl) {
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
        'baseUrl' => $baseUrl
    ]);
});

$app->route('POST /checkout', function() use ($app, $baseUrl) {
    error_log("Debug: Checkout POST request received");
    
    // Get form data
    $shipping = [
        'fullName' => $_POST['fullName'] ?? '',
        'address' => $_POST['address'] ?? '',
        'city' => $_POST['city'] ?? '',
        'state' => $_POST['state'] ?? '',
        'zipCode' => $_POST['zipCode'] ?? ''
    ];
    
    $payment = [
        'cardNumber' => $_POST['cardNumber'] ?? '',
        'expiryDate' => $_POST['expiryDate'] ?? '',
        'cvv' => $_POST['cvv'] ?? ''
    ];
    
    // Basic validation
    $error = null;
    if (empty($shipping['fullName']) || empty($shipping['address']) || 
        empty($shipping['city']) || empty($shipping['state']) || 
        empty($shipping['zipCode'])) {
        $error = "All shipping fields are required";
    } elseif (empty($payment['cardNumber']) || empty($payment['expiryDate']) || 
              empty($payment['cvv'])) {
        $error = "All payment fields are required";
    } elseif (!preg_match('/^\d{16}$/', str_replace(' ', '', $payment['cardNumber']))) {
        $error = "Invalid card number";
    } elseif (!preg_match('/^\d{2}\/\d{2}$/', $payment['expiryDate'])) {
        $error = "Invalid expiry date format (MM/YY)";
    } elseif (!preg_match('/^\d{3,4}$/', $payment['cvv'])) {
        $error = "Invalid CVV";
    }
    
    if ($error) {
        // If there's an error, render the form again with the error message
        $app->render('checkout', [
            'error' => $error,
            'shipping' => $shipping,
            'baseUrl' => $baseUrl
        ]);
        return;
    }
    
    // TODO: Process payment and create order
    // For now, just redirect to success page
    $app->redirect($baseUrl . '/order-success');
});

// Admin routes
$app->route('/admin/dashboard', function() {
    debug_log("Matched admin dashboard route");
    serveHtml('admin-dashboard.html');
});

$app->route('/admin/manage-products', function() {
    debug_log("Matched manage products route");
    serveHtml('manage-products.html');
});

$app->route('/admin/manage-orders', function() {
    debug_log("Matched manage orders route");
    serveHtml('manage-orders.html');
});

$app->route('/admin/manage-users', function() {
    debug_log("Matched manage users route");
    serveHtml('manage-users.html');
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
$app->route('GET /users', function() {
    if (!isApiRequest()) {
        render('users');
        return;
    }

    requireAdmin();
    
    $conn = getDbConnection();
    $stmt = $conn->prepare("SELECT id, name, email, role, created_at FROM users");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    sendJsonResponse($users);
});

$app->route('POST /users', function() {
    if (!isApiRequest()) {
        render('users');
        return;
    }

    requireAdmin();
    
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data || !isset($data['name']) || !isset($data['email']) || !isset($data['password'])) {
        sendJsonResponse(['error' => 'Name, email, and password are required'], 400);
    }

    $conn = getDbConnection();
    
    // Check if email already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$data['email']]);
    if ($stmt->fetch()) {
        sendJsonResponse(['error' => 'Email already exists'], 400);
    }

    $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
    $role = $data['role'] ?? 'user';
    
    $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt->execute([$data['name'], $data['email'], $hashedPassword, $role]);
    
    $userId = $conn->lastInsertId();
    $user = [
        'id' => $userId,
        'name' => $data['name'],
        'email' => $data['email'],
        'role' => $role
    ];
    
    sendJsonResponse($user, 201);
});

$app->route('PUT /users/@id', function($id) {
    requireAdmin();
    
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) {
        sendJsonResponse(['error' => 'Invalid JSON data'], 400);
        return;
    }

    if (empty($data['name']) || empty($data['email'])) {
        sendJsonResponse(['error' => 'Name and email are required'], 400);
        return;
    }

    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        sendJsonResponse(['error' => 'Invalid email format'], 400);
        return;
    }

    if (isset($data['role']) && !in_array($data['role'], ['user', 'admin'])) {
        sendJsonResponse(['error' => 'Invalid role. Must be either "user" or "admin"'], 400);
        return;
    }

    try {
        $db = getDbConnection();
        
        // Check if user exists
        $stmt = $db->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([$id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            sendJsonResponse(['error' => 'User not found'], 404);
            return;
        }
        
        // Check if email is already taken by another user
        $stmt = $db->prepare('SELECT id FROM users WHERE email = ? AND id != ?');
        $stmt->execute([$data['email'], $id]);
        if ($stmt->fetch()) {
            sendJsonResponse(['error' => 'Email already taken'], 400);
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
        
        sendJsonResponse($updatedUser);
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        sendJsonResponse(['error' => 'Failed to update user'], 500);
    }
});

$app->route('DELETE /users/@id', function($id) {
    requireAdmin();
    
    try {
        $db = getDbConnection();
        
        // Check if user exists
        $stmt = $db->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([$id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            sendJsonResponse(['error' => 'User not found'], 404);
            return;
        }
        
        // Check if user has any associated orders
        $stmt = $db->prepare('SELECT COUNT(*) FROM orders WHERE user_id = ?');
        $stmt->execute([$id]);
        $orderCount = $stmt->fetchColumn();
        
        if ($orderCount > 0) {
            sendJsonResponse(['error' => 'Cannot delete user with associated orders'], 400);
            return;
        }
        
        // Delete user
        $stmt = $db->prepare('DELETE FROM users WHERE id = ?');
        $stmt->execute([$id]);
        
        sendJsonResponse(['message' => 'User deleted successfully']);
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        sendJsonResponse(['error' => 'Failed to delete user'], 500);
    }
});

// Product routes
$app->route('GET /products', function() {
    if (isApiRequest()) {
        $conn = getDbConnection();
        $categoryId = $_GET['category_id'] ?? null;
        
        if ($categoryId) {
            $stmt = $conn->prepare("SELECT * FROM products WHERE category_id = ?");
            $stmt->execute([$categoryId]);
        } else {
            $stmt = $conn->prepare("SELECT * FROM products");
            $stmt->execute();
        }
        
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        sendJsonResponse($products);
    } else {
        render('products');
    }
});

$app->route('GET /products/@id', function($id) {
    try {
        $db = getDbConnection();
        
        // Get product details
        $stmt = $db->prepare('SELECT p.*, c.name as category_name 
                            FROM products p 
                            LEFT JOIN categories c ON p.category_id = c.id 
                            WHERE p.id = ?');
        $stmt->execute([$id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$product) {
            sendJsonResponse(['error' => 'Product not found'], 404);
            return;
        }
        
        if (isApiRequest()) {
            sendJsonResponse($product);
        } else {
            Flight::render('products/show', ['product' => $product]);
        }
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        sendJsonResponse(['error' => 'Failed to retrieve product'], 500);
    }
});

$app->route('POST /products', function() {
    if (!isApiRequest()) {
        render('products');
        return;
    }

    requireAdmin();
    
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data || !isset($data['name']) || !isset($data['price']) || !isset($data['category_id'])) {
        sendJsonResponse(['error' => 'Name, price, and category_id are required'], 400);
    }

    $conn = getDbConnection();
    $stmt = $conn->prepare("INSERT INTO products (name, description, price, stock, category_id) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([
        $data['name'],
        $data['description'] ?? null,
        $data['price'],
        $data['stock'] ?? 0,
        $data['category_id']
    ]);
    
    $productId = $conn->lastInsertId();
    $product = [
        'id' => $productId,
        'name' => $data['name'],
        'description' => $data['description'] ?? null,
        'price' => $data['price'],
        'stock' => $data['stock'] ?? 0,
        'category_id' => $data['category_id']
    ];
    
    sendJsonResponse($product, 201);
});

$app->route('PUT /products/@id', function($id) {
    requireAdmin();
    
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) {
        sendJsonResponse(['error' => 'Invalid JSON data'], 400);
        return;
    }

    // Validate required fields
    if (empty($data['name'])) {
        sendJsonResponse(['error' => 'Product name is required'], 400);
        return;
    }

    if (!isset($data['price']) || !is_numeric($data['price']) || $data['price'] < 0) {
        sendJsonResponse(['error' => 'Valid price is required'], 400);
        return;
    }

    if (!isset($data['category_id']) || !is_numeric($data['category_id'])) {
        sendJsonResponse(['error' => 'Valid category ID is required'], 400);
        return;
    }

    if (isset($data['stock']) && (!is_numeric($data['stock']) || $data['stock'] < 0)) {
        sendJsonResponse(['error' => 'Stock must be a non-negative number'], 400);
        return;
    }

    try {
        $db = getDbConnection();
        
        // Check if product exists
        $stmt = $db->prepare('SELECT * FROM products WHERE id = ?');
        $stmt->execute([$id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$product) {
            sendJsonResponse(['error' => 'Product not found'], 404);
            return;
        }
        
        // Check if category exists
        $stmt = $db->prepare('SELECT id FROM categories WHERE id = ?');
        $stmt->execute([$data['category_id']]);
        if (!$stmt->fetch()) {
            sendJsonResponse(['error' => 'Category not found'], 400);
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
        
        sendJsonResponse($updatedProduct);
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        sendJsonResponse(['error' => 'Failed to update product'], 500);
    }
});

$app->route('DELETE /products/@id', function($id) {
    requireAdmin();
    
    try {
        $db = getDbConnection();
        
        // Check if product exists
        $stmt = $db->prepare('SELECT * FROM products WHERE id = ?');
        $stmt->execute([$id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$product) {
            sendJsonResponse(['error' => 'Product not found'], 404);
            return;
        }
        
        // Check if product is in any orders
        $stmt = $db->prepare('SELECT COUNT(*) FROM order_items WHERE product_id = ?');
        $stmt->execute([$id]);
        $orderCount = $stmt->fetchColumn();
        
        if ($orderCount > 0) {
            sendJsonResponse(['error' => 'Cannot delete product that is associated with orders'], 400);
            return;
        }
        
        // Delete product
        $stmt = $db->prepare('DELETE FROM products WHERE id = ?');
        $stmt->execute([$id]);
        
        sendJsonResponse(['message' => 'Product deleted successfully']);
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        sendJsonResponse(['error' => 'Failed to delete product'], 500);
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
$app->route('POST /orders', function() {
    try {
        // Get and validate JWT token
        $token = getBearerToken();
        if (!$token) {
            sendJsonResponse(['error' => 'No token provided'], 401);
            return;
        }

        $payload = validateJWT($token);
        if (!$payload) {
            sendJsonResponse(['error' => 'Invalid token'], 401);
            return;
        }

        $userId = $payload['user_id'];
        
        // Get and validate request data
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data) {
            sendJsonResponse(['error' => 'Invalid JSON data'], 400);
            return;
        }

        if (empty($data['shipping_address'])) {
            sendJsonResponse(['error' => 'Shipping address is required'], 400);
            return;
        }

        $db = getDbConnection();
        
        // Start transaction
        $db->beginTransaction();
        
        try {
            // Get cart items
            $stmt = $db->prepare('
                SELECT ci.*, p.name, p.price, p.stock 
                FROM cart_items ci 
                JOIN products p ON ci.product_id = p.id 
                WHERE ci.user_id = ?
            ');
            $stmt->execute([$userId]);
            $cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (empty($cartItems)) {
                throw new Exception('Cart is empty');
            }
            
            // Calculate total amount
            $totalAmount = 0;
            foreach ($cartItems as $item) {
                $totalAmount += $item['price'] * $item['quantity'];
                
                // Check stock
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
            $stmt = $db->prepare('DELETE FROM cart_items WHERE user_id = ?');
            $stmt->execute([$userId]);
            
            // Commit transaction
            $db->commit();
            
            // Get complete order details
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
            
            // Parse items JSON
            $order['items'] = json_decode($order['items'], true);
            
            sendJsonResponse([
                'message' => 'Order created successfully',
                'order' => $order
            ], 201);
            
        } catch (Exception $e) {
            // Rollback transaction on error
            $db->rollBack();
            throw $e;
        }
    } catch (Exception $e) {
        error_log("Error creating order: " . $e->getMessage());
        sendJsonResponse(['error' => 'Failed to create order: ' . $e->getMessage()], 500);
    }
});

// Add a catch-all route for debugging
$app->route('GET /users/@id', function($id) {
    try {
        // Validate JWT token
        $token = getBearerToken();
        if (!$token) {
            sendJsonResponse(['error' => 'No token provided'], 401);
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
        sendJsonResponse(['error' => 'Failed to retrieve user'], 500);
    }
});

// Add a catch-all route for debugging
$app->route('GET /orders/@id', function($id) {
    try {
        // Get and validate JWT token
        $token = getBearerToken();
        if (!$token) {
            sendJsonResponse(['error' => 'No token provided'], 401);
            return;
        }

        $payload = validateJWT($token);
        if (!$payload) {
            sendJsonResponse(['error' => 'Invalid token'], 401);
            return;
        }

        $userId = $payload['user_id'];
        $isAdmin = $payload['role'] === 'admin';
        
        $db = getDbConnection();
        
        // Get order details
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
            sendJsonResponse(['error' => 'Order not found'], 404);
            return;
        }
        
        // Check if user has permission to view this order
        if (!$isAdmin && $order['user_id'] != $userId) {
            sendJsonResponse(['error' => 'Unauthorized access'], 403);
            return;
        }
        
        // Parse items JSON
        $order['items'] = json_decode($order['items'], true);
        
        sendJsonResponse($order);
    } catch (Exception $e) {
        error_log("Error retrieving order: " . $e->getMessage());
        sendJsonResponse(['error' => 'Failed to retrieve order: ' . $e->getMessage()], 500);
    }
});

// Add a catch-all route for debugging
$app->route('GET /orders', function() {
    try {
        // Get and validate JWT token
        $token = getBearerToken();
        if (!$token) {
            sendJsonResponse(['error' => 'No token provided'], 401);
            return;
        }

        $payload = validateJWT($token);
        if (!$payload) {
            sendJsonResponse(['error' => 'Invalid token'], 401);
            return;
        }

        $userId = $payload['user_id'];
        $isAdmin = $payload['role'] === 'admin';
        
        $db = getDbConnection();
        
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
        
        sendJsonResponse($orders);
    } catch (Exception $e) {
        error_log("Error retrieving orders: " . $e->getMessage());
        sendJsonResponse(['error' => 'Failed to retrieve orders: ' . $e->getMessage()], 500);
    }
});

$app->route('*', function() {
    debug_log("No route matched for: " . $_SERVER['REQUEST_URI']);
    header("HTTP/1.0 404 Not Found");
    echo "No route matched for: " . $_SERVER['REQUEST_URI'];
});

// Start the application
$app->start(); 