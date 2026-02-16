<?php
// ✅ ADD ob_start to prevent header errors
ob_start();

header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// ✅ START SESSION - DITO YUNG KULANG MO!
session_start();

// ✅ REQUIRE ALL CONTROLLERS SA TAAS (not inside switch cases)
require_once "../app/controllers/ProductController.php";
require_once "../app/controllers/NewDropController.php";
require_once "../app/controllers/AuthController.php";
require_once "../app/controllers/CartController.php";
require_once "../app/controllers/CheckoutController.php";

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

switch ($uri) {
    case "/backend/public/products":
        (new ProductController())->index();
        break;
        
    case "/backend/public/newdrop":
        (new NewDropController())->index();
        break;
        
    case "/backend/public/login":
        (new AuthController())->login();
        break;
        
    case "/backend/public/register":
        (new AuthController())->register();
        break;
        
    case "/backend/public/cart":
        (new CartController())->cart();
        break;
        
    case "/backend/public/checkout":
        (new CheckoutController())->checkout();
        break;
        
    case "/backend/public/verify":
        (new AuthController())->verifyEmail();
        break;
        
    default:
        echo json_encode(["error" => "Route not found"]);
        break;
}