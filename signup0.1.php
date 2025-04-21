<?php
session_start();
include('cnn.php');
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $entered_code = $_POST['code'];

    if ($entered_code == $_SESSION['verification_code']) {
        // Redirect to next signup step
        header('Location: signup_step2.php');
        exit();
    } else {
        $error = "❌ Invalid verification code.";
    }
}


// Check if email and verification code are stored in the session
if (isset($_SESSION['email']) && isset($_SESSION['verification_code'])) {
    $email = $_SESSION['email'];
    $password = $_SESSION['password'];
} else {
    // Redirect if the session variables are not set
    header("Location: signup.php");
    exit();
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
            <form method="POST">
                <h2><strong>Enter Verification Code</strong></h2>
                <input type="text" name="code" placeholder="6-digit code" class="input-field" required>
                <br>
                <button type="submit" class="btn1">Verify</button>
                <?php if (isset($error))
                    echo "<div class='error'>$error</div>"; ?>
            </form>
        </div>


    </div>

</body>

</html>