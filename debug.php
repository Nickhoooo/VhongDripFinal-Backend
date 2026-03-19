<?php
require_once __DIR__ . "/app/config/database.php";

$db = (new Database())->connect();

// 1. Check which DB we're connected to
$result = $db->query("SELECT DATABASE()");
$row = $result->fetch_row();
echo "Connected to: " . $row[0] . "\n";

// 2. Count users
$result = $db->query("SELECT COUNT(*) FROM users");
$row = $result->fetch_row();
echo "Total users: " . $row[0] . "\n";

// 3. Raw query — bypass User model completely
$stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$email = 'admin@gmail.com';
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($user) {
    echo "Found: " . $user['email'] . "\n";
} else {
    echo "Still not found\n";
}