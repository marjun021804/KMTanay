<?php
session_start();
include('cnn.php');
include('link.php');

if (isset($_SESSION['customer_id'])) {
    include('menu.php'); // Include the menu for logged-in users
} else {
    include('indexMenu.php'); // Include the menu for guests
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation - KM Tanay</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #fff;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 90%;
            max-width: 800px;
            margin: 50px auto;
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .container h1 {
            color: #4CAF50;
            font-size: 24px;
            margin-bottom: 20px;
        }

        .container p {
            font-size: 16px;
            color: #555;
            margin-bottom: 20px;
        }

        .container a {
            display: inline-block;
            padding: 10px 20px;
            background-color: #f79dbc;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }

        .container a:hover {
            background-color: #f56a8e;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Order Placed Successfully!</h1>
        <p>Thank you for your order. Your order has been placed successfully and is being processed.</p>
        <a href="homepage.php">Return to Homepage</a>
    </div>

    <?php include('footer.php'); ?>
</body>

</html>
