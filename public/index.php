<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
ini_set('session.cookie_httponly', '1');
ini_set('session.use_only_cookies', '1');
ini_set('session.cookie_lifetime', '0');
ini_set('session.cookie_path', '/');
ini_set('session.cookie_domain', '');

session_start();


header('Access-Control-Allow-Origin: https://vhongsdrip.great-site.net');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');


if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}


error_log("=== INDEX.PHP ===");
error_log("Session ID: " . session_id());
error_log("Session Data: " . print_r($_SESSION, true));


$method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// capture anything printed by the router/controllers
ob_start();
require_once __DIR__ . "/../routes/api.php";
$response = ob_get_clean();

// log the raw response (trim to 2000 chars to avoid huge logs)
error_log("[INDEX] raw response: " . substr($response, 0, 2000));

// send to client
echo $response;
