<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

include __DIR__ . "/../config/db.php";

class VerifyController {

    private $conn;

    public function __construct($db){
        $this->conn = $db;
    }

    public function verify(){

        if (isset($_GET['code'])) {

            $code = $_GET['code'];

            $query = "SELECT id FROM users WHERE verification_code = ? AND verified = 0";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("s", $code);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows == 1) {

                $update = "UPDATE users SET verified = 1, verification_code = NULL WHERE verification_code = ?";
                $update_stmt = $this->conn->prepare($update);
                $update_stmt->bind_param("s", $code);
                $update_stmt->execute();

                echo "Your email has been verified! You can now <a href='https://vhong-drip.vercel.app/login'>login</a>.";

            } else {
                echo "Invalid or expired verification link.";
            }

        } else {
            echo "No verification code provided.";
        }
    }
}
