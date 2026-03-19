<?php
// CORS Headers
ini_set('display_errors', 0);
error_reporting(E_ALL);

$allowed_origins = [
    'http://localhost:5173',
    'http://localhost:3000',
    'http://127.0.0.1:5173',
];

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (in_array($origin, $allowed_origins)) {
    header('Access-Control-Allow-Origin: ' . $origin);
}

header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Debug logging mapupunta to sa notes for debug
error_log("=== API.PHP ===");
error_log("URI: " . $_SERVER['REQUEST_URI']);
error_log("Session ID: " . session_id());
error_log("Session Data: " . print_r($_SESSION, true));

require_once __DIR__ . "/../app/controllers/ProductController.php";
require_once __DIR__ . "/../app/controllers/NewDropController.php";
require_once __DIR__ . "/../app/controllers/AuthController.php";
require_once __DIR__ . "/../app/controllers/CartController.php";
require_once __DIR__ . "/../app/controllers/CheckoutController.php";
require_once __DIR__ . "/../app/controllers/DashboardController.php";
require_once __DIR__ . "/../app/controllers/UserController.php";
require_once __DIR__ . "/../app/controllers/PaymentController.php";


$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = rtrim($uri, '/');

if (strpos($uri, '/Backend') === 0) {
    $uri = substr($uri, 8);
}

if (empty($uri)) {
    $uri = '/';
}

switch ($uri) {
    case "/ping":
        echo json_encode(["ok" => true]);
        break;
    case "/dbtest":
        try {
            require_once __DIR__ . "/../app/config/database.php";
            $db = new Database();
            $conn = $db->connect();
            $ok = !$conn->connect_error;
            $tablesRes = $conn->query("SHOW TABLES LIKE 'products'");
            $hasProducts = $tablesRes && $tablesRes->num_rows > 0;
            $count = null;
            if ($hasProducts) {
                $c = $conn->query("SELECT COUNT(*) as c FROM products");
                if ($c) {
                    $row = $c->fetch_assoc();
                    $count = (int)$row['c'];
                }
            }
            echo json_encode(["connected" => $ok, "has_products_table" => $hasProducts, "products_count" => $count]);
        } catch (Exception $e) {
            error_log("[DBTEST] exception: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(["error" => $e->getMessage()]);
        }
        break;
    case "/products":
        (new ProductController())->index();
        break;
    case "/products/delete":
    (new ProductController())->delete();
        break;       
    case "/newdrop":
        (new NewDropController())->index();
        break;
    case "/newdrop/add":
        (new NewDropController())->add();
        break;
    case "/newdrop/delete":
        (new NewDropController())->delete();
        break;       
    case "/login":
        (new AuthController())->login();
        break;
    case "/register":
        (new AuthController())->register();
        break;
    case "/verify":
        (new AuthController())->verify();
        break;
    case "/cart":
        (new CartController())->cart();
        break;
    case "/checkout":
        (new CheckoutController())->checkout();
        break;
    case "/dashboard":
        (new DashboardController()) ->getStats();
        break; 
    case "/users":
        (new UserController()) ->getUsers();
        break;
    case "/users/block":
        (new UserController()) ->block();
        break;
    case "/users/delete":
        (new UserController()) ->delete();
        break;
    case "/payments/pending":
    (new PaymentController())->pending();
        break;
    case "/payments/approve":
    (new PaymentController())->approve();
        break;
    case "/payments/decline":
    (new PaymentController())->decline();
        break;     
    case "/profile":
    (new AuthController())->profile();
        break;            
    default:
        http_response_code(404);
        echo json_encode(["error" => "Route not found"]);
        break;
}
