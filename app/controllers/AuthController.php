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

        // Validation
        if (!$firstname || !$lastname || !$phone || !$address || !$email || !$username || !$password){
            echo json_encode([
                "status" => "error",
                "message" => "All fields are required"
            ]);
            return;
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)){
            echo json_encode([
                "status" => "error",
                "message" => "Invalid Email"
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
                "message" => "You must agree to the terms and conditions"
            ]);
            return;
        }

        $user = new User((new Database())->connect());
        
        // Generate verification code
        $verification_code = bin2hex(random_bytes(16));
        
        $result = $user->register($firstname, $lastname, $phone, $address, $username, $email, $password, $verification_code);

        if (!$result['success']) {
            echo json_encode([
                "status" => "error",
                "message" => $result['error']
            ]);
            return;
        }

        //  SEND VERIFICATION EMAIL
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
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = 'Verify Your Email';
            $verification_link = "https://vhongsdrip.great-site.net/verify?code=$verification_code";
            $mail->Body    = "
                <h2>Welcome to Vhong Website!</h2>
                <p>Please verify your email by clicking the link below:</p>
                <a href='$verification_link'>Verify Email</a>
                <p>Or copy this link: $verification_link</p>
            ";

            $mail->send();

            echo json_encode([
                "status" => "success",
                "message" => "Registration successful! Please check your email to verify your account."
            ]);

        } catch (Exception $e) {
            echo json_encode([
                "status" => "error",
                "message" => "Registration successful but email failed to send: {$mail->ErrorInfo}"
            ]);
        }
    }
}


     public function login() {

    header('Content-Type: application/json');

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

   
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $_SESSION['user_id'] = $result['id'];
    $_SESSION['username'] = $result['username'];
    $_SESSION['email'] = $result['email'];

    error_log("LOGIN SESSION SAVED:");
    error_log(print_r($_SESSION, true));


    echo json_encode([
        "status" => "success",
        "message" => "Login successful",
        "user" => [
            "id" => $result['id'],
            "username" => $result['username'],
            "email" => $result['email']
        ]
    ]);
}

public function verify() {

    header('Content-Type: text/html; charset=UTF-8');
    
    if (!isset($_GET['code'])) {
        echo "<h2>Verification Failed</h2><p>Verification code is missing.</p>";
        return;
    }

    $verification_code = $_GET['code'];
    
    $user = new User((new Database())->connect());
    $result = $user->verifyEmail($verification_code);
    
    if ($result['success']) {
        echo '
        <!DOCTYPE html>
        <html>
        <head>
            <title>Email Verified</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    height: 100vh;
                    margin: 0;
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                }
                .container {
                    background: white;
                    padding: 40px;
                    border-radius: 10px;
                    box-shadow: 0 10px 25px rgba(0,0,0,0.2);
                    text-align: center;
                    max-width: 400px;
                }
                h2 {
                    color: #4CAF50;
                    margin-bottom: 20px;
                }
                p {
                    color: #666;
                    line-height: 1.6;
                }
                .success-icon {
                    font-size: 60px;
                    color: #4CAF50;
                    margin-bottom: 20px;
                }
                .btn {
                    display: inline-block;
                    margin-top: 20px;
                    padding: 12px 30px;
                    background: #667eea;
                    color: white;
                    text-decoration: none;
                    border-radius: 5px;
                    transition: background 0.3s;
                }
                .btn:hover {
                    background: #5568d3;
                }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="success-icon">✓</div>
                <h2>Email Verified Successfully!</h2>
                <p>Your account has been activated. You can now login to your account.</p>
                <a href="https://vhongsdrip.great-site.net/login
                " class="btn">Go to Login</a>
            </div>
        </body>
        </html>
        ';
    } else {
        echo '
        <!DOCTYPE html>
        <html>
        <head>
            <title>Verification Failed</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    height: 100vh;
                    margin: 0;
                    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
                }
                .container {
                    background: white;
                    padding: 40px;
                    border-radius: 10px;
                    box-shadow: 0 10px 25px rgba(0,0,0,0.2);
                    text-align: center;
                    max-width: 400px;
                }
                h2 {
                    color: #f5576c;
                    margin-bottom: 20px;
                }
                p {
                    color: #666;
                    line-height: 1.6;
                }
                .error-icon {
                    font-size: 60px;
                    color: #f5576c;
                    margin-bottom: 20px;
                }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="error-icon">✗</div>
                <h2>Verification Failed</h2>
                <p>' . htmlspecialchars($result['error']) . '</p>
            </div>
        </body>
        </html>
        ';
    }
}


}