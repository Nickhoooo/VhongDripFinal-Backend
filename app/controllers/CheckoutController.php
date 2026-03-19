<?php
require_once __DIR__ . "/../config/database.php";

require_once __DIR__ . "/../../PHPMailer/Exception.php";
require_once __DIR__ . "/../../PHPMailer/PHPMailer.php";
require_once __DIR__ . "/../../PHPMailer/SMTP.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class CheckoutController {

    private function sendConfirmationEmail($email, $total, $order_id) {
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'xenonchan29@gmail.com';   
            $mail->Password   = 'wegd vevh vein wwzl';        
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->setFrom('xenonchan29@gmail.com', 'Ardor Shop');
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = 'Order Confirmed - Ardor Shop';
            $mail->Body = "
                <h2>Thank you for your order!</h2>
                <p>Your order has been <b>confirmed</b>.</p>
                <p>Order ID: <b>$order_id</b></p>
                <p>Total: <b>₱" . number_format($total, 2) . "</b></p>
                <p>Estimated Shipping: <b>5 business days</b></p>
                <br>
                <p>Thank you for shopping at Ardor!</p>
            ";
            $mail->send();
        } catch (Exception $e) {
            error_log("Email failed: " . $mail->ErrorInfo);
        }
    }

    public function checkout() {
        header('Content-Type: application/json');

        // 1. Check if logged in
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Not logged in']);
            exit;
        }

        // 2. Validate total
        if (!isset($_POST['total']) || !is_numeric($_POST['total'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid total']);
            exit;
        }

        $conn     = (new Database())->connect();
        $user_id  = $_SESSION['user_id'];
        $total    = floatval($_POST['total']);

        // 3. Get user payment mode and email
        $stmt = $conn->prepare("SELECT payment_mode, email FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();

        if (!$user) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'User not found']);
            exit;
        }

        $payment_mode = $user['payment_mode'];

        // 4. Handle GCash — save proof, status = pending
        if ($payment_mode === 'gcash') {

            if (!isset($_FILES['payment_proof']) || $_FILES['payment_proof']['error'] !== 0) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Payment proof is required for GCash']);
                exit;
            }

            $upload_dir = __DIR__ . "/../../payments/";
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            $filename  = uniqid('proof_') . "_" . basename($_FILES['payment_proof']['name']);
            $dest      = $upload_dir . $filename;

            if (!move_uploaded_file($_FILES['payment_proof']['tmp_name'], $dest)) {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to upload proof']);
                exit;
            }

            $status = 'pending';
            $stmt = $conn->prepare("INSERT INTO orders (user_id, total, payment_mode, payment_proof, payment_status) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("idsss", $user_id, $total, $payment_mode, $filename, $status);
            $stmt->execute();

            echo json_encode([
                'success' => true,
                'message' => 'Payment submitted! Waiting for admin verification.'
            ]);

        // 5. Handle Cash on Delivery — status = completed, send email agad
        } else {

            $status   = 'completed';
            $order_id = uniqid('ORDER_');

            $stmt = $conn->prepare("INSERT INTO orders (user_id, total, payment_mode, payment_status) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("idss", $user_id, $total, $payment_mode, $status);
            $stmt->execute();

            $this->sendConfirmationEmail($user['email'], $total, $order_id);

            echo json_encode([
                'success' => true,
                'message' => 'Order placed! Confirmation email sent.'
            ]);
        }
    }
}