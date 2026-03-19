<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . "/../../PHPMailer/Exception.php";
require_once __DIR__ . "/../../PHPMailer/PHPMailer.php";
require_once __DIR__ . "/../../PHPMailer/SMTP.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class PaymentController {

    private function sendMail($email, $subject, $body) {
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'xenonchan29@gmail.com';     // ← palitan
            $mail->Password   = 'wegd vevh vein wwzl';       // ← palitan
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->setFrom('xenonchan29@gmail.com', 'Vhong Shop');
            $mail->addAddress($email);
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $body;
            $mail->send();
        } catch (Exception $e) {
            error_log("Email error: " . $mail->ErrorInfo);
        }
    }

    // GET all pending payments
    public function pending() {
        header('Content-Type: application/json');
        $conn = (new Database())->connect();

        $sql = "SELECT o.id, u.username, u.email, o.payment_mode, o.payment_proof, o.total, o.payment_status
                FROM orders o
                JOIN users u ON o.user_id = u.id
                WHERE o.payment_status = 'pending'
                ORDER BY o.created_at DESC";

        $result   = $conn->query($sql);
        $payments = [];

        while ($row = $result->fetch_assoc()) {
            $payments[] = $row;
        }

        echo json_encode($payments);
    }

    // POST approve payment
    public function approve() {
        header('Content-Type: application/json');
        $data = json_decode(file_get_contents("php://input"), true);
        $id   = intval($data['id']);

        $conn = (new Database())->connect();

        // Update status
        $stmt = $conn->prepare("UPDATE orders SET payment_status = 'completed' WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        // Get user email and total for the email
        $stmt = $conn->prepare("SELECT u.email, o.total FROM orders o JOIN users u ON o.user_id = u.id WHERE o.id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        // Send approval email
        $this->sendMail(
            $row['email'],
            '✅ Payment Approved - Vhong Shop',
            "
                <h2>Your payment has been approved! ✅</h2>
                <p>Order Total: <b>₱" . number_format($row['total'], 2) . "</b></p>
                <p>Status: <b>Completed</b></p>
                <p>Estimated Shipping: <b>5 business days</b></p>
                <br>
                <p>Thank you for shopping at Ardor!</p>
            "
        );

        echo json_encode(['success' => true, 'message' => 'Payment approved and email sent']);
    }

    // POST decline payment
    public function decline() {
        header('Content-Type: application/json');
        $data = json_decode(file_get_contents("php://input"), true);
        $id   = intval($data['id']);

        $conn = (new Database())->connect();

        $stmt = $conn->prepare("UPDATE orders SET payment_status = 'declined' WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        // Get user email
        $stmt = $conn->prepare("SELECT u.email, o.total FROM orders o JOIN users u ON o.user_id = u.id WHERE o.id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        // Send decline email
        $this->sendMail(
            $row['email'],
            '❌ Payment Declined - Vhong Shop',
            "
                <h2>Your payment was declined ❌</h2>
                <p>Order Total: <b>₱" . number_format($row['total'], 2) . "</b></p>
                <p>Please re-submit your payment proof or contact support.</p>
                <br>
                <p>Vhong Shop Support</p>
            "
        );

        echo json_encode(['success' => true, 'message' => 'Payment declined and email sent']);
    }
}