<?php
require_once __DIR__ . "/../models/User.php";
require_once __DIR__ . "/../config/database.php";
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class AuthController{



    public function register()
    
    {
        require __DIR__ . "/../../PHPMailer/Exception.php";
        require __DIR__ . "/../../PHPMailer/PHPMailer.php";
        require __DIR__ . "/../../PHPMailer/SMTP.php";
        
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] === 'POST'){

           $firstname = $_POST['firstname'] ?? '';
            $lastname = $_POST['lastname'] ?? '';
            $phone = $_POST['phone'] ?? '';
            $address = $_POST['address'] ?? '';
            $email = $_POST['email'] ?? '';
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';
            $terms = $_POST['terms'] ?? false;

            if ( !$firstname || !$lastname || !$phone || !$address || !$email || !$username || !$password){
                echo json_encode([
                    "status" => "error",
                    "message" => "All field are required"
                ]);
                return;
            }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)){
                echo json_encode([
                    "status" => "error",
                    "message" => "invalid Email"
                ]);
                return;
            }
            if(strlen($password) < 6){
                echo json_encode([
                    "status" => "error",
                    "message" => "Your password is too short"
                ]);
                return;
            }
            if (!$terms){
                echo json_encode([
                    "status" => "error",
                    "message" => "You must agree to the terms and conditions"  // ✅ FIX 5: Fixed typo
                ]);
                return;
            }

        try {
            $verification_code = bin2hex(random_bytes(16)); // 32-character code
            error_log("Generated code: " . $verification_code);
            
            $is_verified = 0;
            $user = new User((new Database())->connect());
            $result = $user->register($firstname, $lastname, $phone, $address, $username, $email, $password, $is_verified, $verification_code);

            if (!$result['success']) {
                echo json_encode([
                    "status" => "error",
                    "message" => $result['error']
                ]);
                return;
            }

            $mail = new PHPMailer(true);

        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'xenonchan29@gmail.com';
        $mail->Password = 'wegd vevh vein wwzl';
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('xenonchan29@gmail.com', 'Vhong Website');
        
        // ✅ FIX 1: Define $name variable
        $name = $firstname . ' ' . $lastname;
        $mail->addAddress($email, $name);

        $mail->isHTML(true);
        $mail->Subject = 'Email Verification - Vhong Website';
        
        // ✅ FIX 4: Fixed verification link URL
        $verification_link = "http://localhost/backend/public/verify?code=" . $verification_code;

        
        $mail->Body = "<h2>Welcome to Vhong Website!</h2>
            <p>Hi $name,</p>
            <p>Thank you for signing up! Please verify your email to activate your account:</p>
            <p><a href='$verification_link' style='padding: 10px 20px; background-color: #007bff; color: white; text-decoration: none; border-radius: 5px; display: inline-block;'>Verify Email</a></p>
            <p>Or copy this link: $verification_link</p>
            <p>This link will expire in 24 hours.</p>
            <p>If you didn't sign up, please ignore this email.</p>";

        $mail->send();
        echo json_encode([
            "status" => "success",
            "message" => "Successful! Please check your Gmail to verify your email before logging in.",
            "redirect" => "/login"
        ]); 
        exit();
        } catch (Exception $e) {
            echo json_encode([
                "status" => "error",
                "message" => "Verification email could not be sent. Mailer Error: {$mail->ErrorInfo}"
            ]);
        }


    }
}


     public function login() {
    header('Content-Type: application/json');
    session_start();
    if($_SERVER['REQUEST_METHOD'] == 'POST'){
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $terms = $_POST['terms'] ?? false;
    }

    if(!$email || !$password){
        echo json_encode([
            "status" => "error",
            "message" => "All field are required"
        ]);
        return;
    }
    if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
        echo json_encode([
            "status" => "error",
            "message" => "Invalid Email"
        ]);
        return;
    } 
    if(!$terms){
        echo json_encode([
            "status" => "error",
            "message" => "You must agree to the terms and conditions"
        ]);
        return;
    }

    $user = new User((new Database())->connect());
    $result = $user->getByEmail($email);

    if ($result == null){
        echo json_encode([
            "status" => "error",
            "message" => "Invalid email or password"
        ]);
        return;
    }
    
    $isPasswordCorrect = password_verify($password, $result['password']);

    if (!$isPasswordCorrect) {
        echo json_encode([
            "status" => "error",
            "message" => "Invalid email or password"
        ]);
        return;
    }

    // Check if email is verified
    if ($result['is_verified'] == 0) {
        echo json_encode([
            "status" => "error",
            "message" => "Please verify your email before logging in"
        ]);
        return;
    }

    // ❌ TANGGALIN MO ITO!
    // session_start();

    // ✅ Session already started sa api.php, just use it!
    $_SESSION['user_id'] = $result['id'];
    $_SESSION['username'] = $result['username'];
    $_SESSION['email'] = $result['email'];

    // ✅ ADD DEBUG
    error_log("LOGIN SUCCESS - Session ID: " . session_id());
    error_log("LOGIN SUCCESS - User ID: " . $_SESSION['user_id']);

    echo json_encode([
        "status" => "success",
        "message" => "Login successful",
        "user" => [
            "id" => $result['id'],
            "username" => $result['username'],
            "email" => $result['email'],
            "firstname" => $result['firstname'],
            "lastname" => $result['lastname']
        ]
    ]);
}


   public function verifyEmail() {
    header('Content-Type: text/html');

    $db = new Database();
    $conn = $db->connect();

    if(isset($_GET['code'])) {
        $code = trim($_GET['code']); // ✅ ADD TRIM
        
        // ✅ LOG IT
        error_log("Received code: " . $code);

        // Hanapin sa users table
        $stmt = $conn->prepare("SELECT id, username, verification_code FROM users WHERE verification_code = ? AND is_verified = 0");
        $stmt->bind_param("s", $code);
        $stmt->execute();
        $result = $stmt->get_result();
        
        // ✅ LOG IT
        error_log("Found rows: " . $result->num_rows);
        
        if($result->num_rows == 1){
            $user_data = $result->fetch_assoc();
            error_log("DB code: " . $user_data['verification_code']);
            
            // Update verified
            $update = $conn->prepare("UPDATE users SET is_verified = 1, verification_code = NULL WHERE verification_code = ?");
            $update->bind_param("s", $code);
            $update->execute();

            echo "Your email has been verified! You can now <a href='http://localhost:5173/login'>login</a>.";
        } else {
            echo "Invalid or expired verification link.";
        }
    } else {
        echo "No verification code provided.";
    }
}


}