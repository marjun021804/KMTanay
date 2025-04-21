<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'PHPMailer/src/Exception.php';

session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require_once 'cnn.php'; // Database connection

    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    // Basic sanitation

    // Check if email already exists
    $stmt = $conn->prepare("SELECT * FROM customer_tb WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    // If email exists, stop signup
    if ($result->num_rows > 0) {
        echo "<script>alert('This email is already registered. Please use a different one or log in.');</script>";
    } else {
        // Proceed to send verification code
        $verification_code = rand(100000, 999999);
        $_SESSION['email'] = $email;
        $_SESSION['verification_code'] = $verification_code;
        $_SESSION['password'] = password_hash($password, PASSWORD_DEFAULT); // <-- this line!

        $mail = new PHPMailer(true);

        try {
            // SMTP Configuration
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'marjun.manalo12@gmail.com';
            $mail->Password = 'idlw krkn mjse qwnf';
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom('your_email@gmail.com', 'KM Tanay');
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = 'Your Verification Code';
            $mail->Body = "<p>Your verification code is: <strong>$verification_code</strong></p>";

            $mail->send();

            header("Location: signup0.1.php");
            exit();
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    }
}

?>





<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
    <link rel="stylesheet" href="style.css">
    <?php include('link.php'); ?>
    <!-- Facebook SDK -->
    <script async defer crossorigin="anonymous" src="https://connect.facebook.net/en_US/sdk.js"></script>

    <!-- Google Platform API -->
    <script src="https://accounts.google.com/gsi/client" async defer></script>
    <style>
        /* Global Styles */
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
        }

        /* Header */
        header {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            background-color: #f48fb1;
            padding: 15px 20px;
            text-align: left;
            display: flex;
            align-items: center;
            box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.1);
        }

        .header-container {
            width: 100%;
            text-align: center;
        }

        header h2 {
            margin: 0;
            font-size: 40px;
            color: white;
            font-weight: bold;
        }

        /* Main Container */
        .signup-container {
            display: flex;
            align-items: center;
            justify-content: center;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
            max-width: 800px;
            width: 100%;
            margin: 130px auto 0;
            /* Increased margin to push the form down */
        }

        /* Left Side - Logo */
        .logo-container {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .logo-container img {
            width: 250px;
        }

        /* Right Side - Form */
        .form-container {
            flex: 1;
            padding: 20px;
            text-align: center;
        }

        .form-container h2 {
            font-size: 24px;
            margin-bottom: 20px;
        }

        .input-field {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
        }



        /* Social Buttons */
        .social-buttons {
            display: flex;
            justify-content: center;
            gap: 10px;
            /* Space between buttons */
            margin-top: 10px;
        }

        .social-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 8px;
            width: 40%;
            border: 1px solid #ccc;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            background: white;
        }

        .social-btn i {
            margin-right: 5px;
        }

        .social-btn.facebook {
            color: #1877F2;
            border-color: #1877F2;
        }

        .social-btn.google {
            color: #DB4437;
            border-color: #DB4437;
        }

        .social-btn:hover {
            background: #f1f1f1;
        }

        /* Login Link */
        .login-link {
            margin-top: 15px;
            font-size: 14px;
            color: #555;
        }

        .login-link a {
            color: #007BFF;
            text-decoration: none;
        }

        .login-link a:hover {
            text-decoration: underline;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .signup-container {
                flex-direction: column;
                text-align: center;
            }

            .logo-container img {
                width: 180px;
            }

            .social-buttons {
                flex-direction: column;
            }

            .social-btn {
                width: 100%;
                margin-bottom: 10px;
            }
        }
    </style>
</head>

<body>

    <!-- Fixed Header -->
    <header>
        <div class="header-container">
            <h2>Sign Up</h2>
        </div>
    </header>

    <!-- Signup Form Container -->
    <div class="signup-container">
        <!-- Left Side - Logo -->
        <div class="logo-container">
            <img src="/KMTanayAdmin/image/kmtanaylogo.png" alt="KM Tanay Logo">
        </div>

        <!-- Right Side - Sign Up Form -->
        <div class="form-container">

            <form action="" method="POST">
                <h2><strong>Sign Up</strong></h2>
                <input type="email" name="email" placeholder="Email" class="input-field" required>
                <input type="password" name="password" placeholder="Password" class="input-field" required>
                <button type="submit" class="btn1">Next</button>
            </form>

            <div class="divider">
                <hr><span>OR</span>
                <hr>
            </div>

            <!-- Centered & Smaller Social Buttons -->
            <div class="social-buttons">
                <!-- Facebook Login Button -->
                <button class="social-btn facebook" onclick="loginWithFacebook()">
                    <i class="fa-brands fa-facebook"></i> Facebook
                </button>
                <div id="g_id_onload"
                    data-client_id="706544271646-2q54pek1897bqaabst0q47jhf6mnpme8.apps.googleusercontent.com"
                    data-callback="handleGoogleLogin" data-auto_prompt="false">
                </div>
                <div class="g_id_signin" data-type="standard"></div>
            </div>

            <p class="login-link">
                Have an account? <a href="login.php">Log In</a>
            </p>
        </div>
    </div>





    <script>
        // Send verification code via AJAX
        function sendVerificationCode(email) {
            const code = Math.floor(100000 + Math.random() * 900000);
            sessionStorage.setItem('verification_code', code); // Optionally store on client

            fetch('send_code.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ email, code })
            })
                .then(response => response.text())
                .then(data => {
                    if (data === 'success') {
                        alert("Verification code sent to " + email);
                        window.location.href = "signup0.1.php"; // Redirect
                    } else {
                        alert("Failed to send code: " + data);
                    }
                })
                .catch(error => {
                    console.error("Error sending code:", error);
                    alert("Error sending code.");
                });
        }


        function loginWithFacebook() {
            FB.init({
                appId: '1598790274155456',
                cookie: true,
                xfbml: true,
                version: 'v17.0'
            });

            FB.getLoginStatus(function (response) {
                if (response.status === 'connected') {
                    // Force logout first to clear cached session
                    FB.logout(function () {
                        // Then re-initiate login
                        initiateFBLogin();
                    });
                } else {
                    initiateFBLogin();
                }
            });

            function initiateFBLogin() {
                FB.login(function (response) {
                    if (response.authResponse) {
                        FB.api('/me', { fields: 'name,email' }, function (response) {
                            console.log('FB Login success:', response);
                            sendVerificationCode(response.email);
                        });
                    } else {
                        alert('Facebook login failed.');
                    }
                }, {
                    scope: 'email',
                    auth_type: 'reauthenticate' // Forces Facebook to ask for login again
                });
            }
        }




        // Google Login Function
        function handleGoogleLogin(response) {
            try {
                const payload = jwt_decode(response.credential);
                console.log("Google Login Success:", payload);
                sendVerificationCode(payload.email);
            } catch (err) {
                console.error("Google login error:", err);
                alert("Google Login failed.");
            }
        }



    </script>
    <!-- Google Platform API -->
    <script src="https://accounts.google.com/gsi/client" async defer></script>
    <script src="https://cdn.jsdelivr.net/npm/jwt-decode/build/jwt-decode.min.js"></script>


    </script>
</body>

</html>