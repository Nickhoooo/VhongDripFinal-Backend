<?php
// ✅ Session FIRST


// ✅ Then headers

header("Content-Type: application/json");


// Your CartController class...
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
class CartController{

    public function Cart(){

    
        error_log("Session ID: " . session_id());
        error_log("Session user_id: " . ($_SESSION['user_id'] ?? 'NOT SET'));
        error_log("All session data: " . print_r($_SESSION, true));

        if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'User not logged in']);
        exit;
    }

        if (!isset($_POST['total']) || !is_numeric($_POST['total'])) {
            http_response_code(401);
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
    $mail->SMTPSecure = 'tls';
    $mail->Port       = 587;

    $mail->setFrom('xenonchan29@gmail.com', 'Vhong Website');
    $mail->addAddress($user_email);
    

    $mail->isHTML(true);
    $mail->Subject = 'Checkout Successful';
    $mail->Body    = 
    "<h2>Thank you for your order!</h2> 
    <b>your checkout of ₱" . number_format($total, 2) . " is successful!</b> 
    <p>Status: <b>Confirmed</b> </p>
    <p>Estimated Shipping Time: <b>5 business days</b> </p>
    <br>Order ID: $order_id
    <br>Thank you for your purchase.";

    $mail->send();
    http_response_code(200);
    echo json_encode(['success' => true, 'message' => 'Checkout successful! Confirmation email sent.', 'order_id' => $order_id]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => "Email could not be sent. Error: {$mail->ErrorInfo}"]);
    }
    }
}