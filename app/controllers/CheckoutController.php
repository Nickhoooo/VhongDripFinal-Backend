<?php

require_once __DIR__ . "/../models/User.php";
require_once __DIR__ . "/../config/database.php";
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class CheckoutController {

    public function checkout(){
        header('Content-Type: application/json');
        
        require __DIR__ . "/../../PHPMailer/Exception.php";
        require __DIR__ . "/../../PHPMailer/PHPMailer.php";
        require __DIR__ . "/../../PHPMailer/SMTP.php";

        // ✅ Debug logging
        error_log("=== CHECKOUT DEBUG ===");
        error_log("Session ID: " . session_id());
        error_log("Session data: " . print_r($_SESSION, true));
        error_log("User ID set: " . (isset($_SESSION['user_id']) ? 'YES' : 'NO'));

        if (!isset($_SESSION['user_id'], $_SESSION['email'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'User not logged in']);
            exit;
        }

        if (!isset($_POST['total']) || !is_numeric($_POST['total'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid total amount']);
            exit;
        }

        $total = floatval($_POST['total']);
        $user_email = $_SESSION['email'];
        $order_id = uniqid('ORDER_');

        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'xenonchan29@gmail.com';
            $mail->Password   = 'wegd vevh vein wwzl';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->setFrom('xenonchan29@gmail.com', 'Vhong Website');
            $mail->addAddress($user_email);

            $mail->isHTML(true);
            $mail->Subject = 'Checkout Successful';
            $mail->Body    = "
                <h2>Thank you for your order!</h2> 
                <b>Your checkout of ₱" . number_format($total, 2) . " is successful!</b> 
                <p>Status: <b>Confirmed</b></p>
                <p>Estimated Shipping Time: <b>5 business days</b></p>
                <br>Order ID: $order_id
                <br>Thank you for your purchase.
            ";

            $mail->send();

            echo json_encode([
                'success' => true,
                'message' => 'Checkout successful! Confirmation email sent.',
                'order_id' => $order_id
            ]);

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => "Email could not be sent: {$mail->ErrorInfo}"
            ]);
        }
    }
}