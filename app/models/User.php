<?php
require_once __DIR__ . "/../config/database.php";

class User {
    private $conn;
    
    public function __construct($db)
    {
       $this->conn = $db;
    }

    public function register($firstname, $lastname, $phone, $address, $username, $email, $password, $verification_code)
    {
        try {
            // Check username
            $stmt = $this->conn->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                return ['success' => false, 'error' => 'Username already taken'];
            }

            // Check email
            $stmt = $this->conn->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                return ['success' => false, 'error' => 'Email already registered'];
            }

            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $is_verified = 0;

            // Insert user
            $stmt = $this->conn->prepare("INSERT INTO users (firstname, lastname, phone, address, username, email, password, is_verified, verification_code) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssssis", $firstname, $lastname, $phone, $address, $username, $email, $hashed_password, $is_verified, $verification_code);

            if ($stmt->execute()) {
                return ['success' => true];
            } else {
                return ['success' => false, 'error' => 'Failed to insert user: ' . $stmt->error];
            }

        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function verifyEmail($verification_code) {
        try {
            $stmt = $this->conn->prepare("UPDATE users SET is_verified = 1, verification_code = NULL WHERE verification_code = ?");
            $stmt->bind_param("s", $verification_code);
            
            if ($stmt->execute() && $stmt->affected_rows > 0) {
                return ['success' => true];
            } else {
                return ['success' => false, 'error' => 'Invalid or expired verification code'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    public function getByEmail($email) {
        $sql = "SELECT * FROM users WHERE email = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
}