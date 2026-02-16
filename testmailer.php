<?php
require "PHPMailer/PHPMailer.php";
require "PHPMailer/SMTP.php";
require "PHPMailer/Exception.php";

use PHPMailer\PHPMailer\PHPMailer;

$mail = new PHPMailer(true);

$mail->isSMTP();
$mail->Host = 'smtp.gmail.com';
$mail->SMTPAuth = true;
$mail->Username = 'xenonchan29@gmail.com';
$mail->Password = 'wegd vevh vein wwzl'; // app password
$mail->SMTPSecure = 'tls';
$mail->Port = 587;

$mail->setFrom('xenonchan29@gmail.com', 'Test');
$mail->addAddress('xenonchan29@gmail.com');

$mail->Subject = 'TEST EMAIL';
$mail->Body = 'Gumagana ba?';

if($mail->send()){
    echo "SUCCESS SEND";
}else{
    echo $mail->ErrorInfo;
}
