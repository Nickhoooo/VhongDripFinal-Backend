<?php
// Headers for CORS
ini_set('session.cookie_samesite', 'None');
ini_set('session.cookie_secure', '0');  // Set to '1' if using HTTPS
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_domain', '');  // Empty for localhost
ini_set('session.cookie_path', '/');

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");


// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Declare method and URI
$method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Include routes file
require_once "../routes/api.php";



