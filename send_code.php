<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'PHPMailer/src/Exception.php';

session_start();
include('cnn.php');
$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['email']) && isset($data['code'])) {
    $email = filter_var($data['email'], FILTER_SANITIZE_EMAIL);
    $verification_code = $data['code'];
    $_SESSION['verification_code'] = $verification_code;
    $_SESSION['email'] = $email;

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'marjun.manalo12@gmail.com';
        $mail->Password = 'idlw krkn mjse qwnf'; // App Password
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('your_email@gmail.com', 'KM Tanay');
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = 'Your Verification Code';
        $mail->Body    = "<p>Your verification code is: <strong>$verification_code</strong></p>";

        $mail->send();
        echo 'success';
    } catch (Exception $e) {
        echo "Mailer Error: {$mail->ErrorInfo}";
    }
} else {
    echo 'Invalid data';
}
?>
