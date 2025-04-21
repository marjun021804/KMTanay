<?php
session_start();
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Include PHPMailer files
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    $new_email = htmlspecialchars(trim($_POST['email']));

    if (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo "Invalid email address.";
        exit;
    }

    $verification_code = rand(100000, 999999); // Generate a 6-digit verification code
    $_SESSION['verification_code'] = $verification_code; // Store the code in the session
    $_SESSION['pending_email'] = $new_email; // Store the new email in the session

    // Send the verification code using PHPMailer
    $mail = new PHPMailer(true);
    try {
        // SMTP configuration
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; // Replace with your SMTP server
        $mail->SMTPAuth = true;
        $mail->Username = 'marjun.manalo12@gmail.com'; // Replace with your email address
        $mail->Password = 'idlw krkn mjse qwnf'; // Replace with your app password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587; // Common SMTP port for STARTTLS

        // Email settings
        $mail->setFrom('marjun.manalo12@gmail.com', 'KM Tanay Admin'); // Replace with your email address
        $mail->addAddress($new_email);
        $mail->Subject = 'Email Verification Code';
        $mail->Body = "Your verification code is: $verification_code";

        $mail->send();
        echo "Verification code sent successfully.";
    } catch (Exception $e) {
        http_response_code(500);
        echo "Failed to send verification code. Error: {$mail->ErrorInfo}";
    }
} else {
    http_response_code(400);
    echo "Invalid request.";
}
?>
