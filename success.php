<?php
session_start();

// Optional: You can clear session data after successful registration
session_unset();
session_destroy();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Registration Successful</title>
    <link rel="stylesheet" href="style.css">
    <?php include('link.php'); ?>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            text-align: center;
        }

        .success-container {
            max-width: 500px;
            margin: 150px auto;
            background: #ffffff;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .success-container img {
            width: 80px;
            margin-bottom: 20px;
        }

        .success-container h2 {
            color: #4caf50;
            margin-bottom: 10px;
        }

        .success-container p {
            color: #555;
            font-size: 16px;
        }

        .success-container a {
            display: inline-block;
            margin-top: 25px;
            padding: 10px 20px;
            background-color: #f48fb1;
            color: #fff;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
        }

        .success-container a:hover {
            background-color: #e56b92;
        }
    </style>
</head>

<body>
    <div class="success-container">
        <img src="https://cdn-icons-png.flaticon.com/512/845/845646.png" alt="Success Icon">
        <h2>Registration Successful!</h2>
        <p>Thank you for submitting your information. We will review your ID and notify you via email once your account is verified.</p>
        <a href="login.php">Go to Login</a>
    </div>
</body>

</html>
