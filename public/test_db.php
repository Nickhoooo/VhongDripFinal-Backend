<?php
// Session config
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.cookie_secure', '0');
session_start();

// CORS
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");

// Debug output
echo json_encode([
    'session_id' => session_id(),
    'session_data' => $_SESSION,
    'cookies_received' => $_COOKIE,
    'has_user_id' => isset($_SESSION['user_id']),
    'user_id_value' => $_SESSION['user_id'] ?? 'NOT SET'
]);
