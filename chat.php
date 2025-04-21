<?php
session_start();
include('cnn.php');

// Check if the user is logged in
$isLoggedIn = isset($_SESSION['customer_id']);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat with KM Tanay</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #fff;
        }

        .main-container {
            max-width: 1200px;
            margin: auto;
            padding: 20px;
        }

        .chat-container {
            width: 100%;
            max-width: 800px;
            margin: 40px auto;
            background-color: white;
            border: 1px solid #ccc;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .chat-header {
            padding: 15px;
            background-color: #ffe4ec;
            border-bottom: 1px solid #ccc;
            display: flex;
            align-items: center;
        }

        .chat-header img {
            border-radius: 50%;
            width: 40px;
            height: 40px;
            margin-right: 10px;
        }

        .chat-body {
            background-color: #f8b7c7;
            padding: 20px;
            max-height: 400px;
            overflow-y: auto;
        }

        .message {
            margin-bottom: 15px;
            max-width: 70%;
            padding: 10px 15px;
            border-radius: 20px;
            background-color: #ffffff;
            display: inline-block;
        }

        .message.user {
            background-color: #fff;
            align-self: flex-start;
        }

        .message.bot {
            background-color: #f7d3dd;
            align-self: flex-end;
            float: right;
            clear: both;
        }

        .chat-footer {
            border-top: 1px solid #ccc;
            padding: 10px;
            background-color: #eeeeee;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .chat-footer input {
            flex-grow: 1;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .chat-footer button {
            background-color: #f79ecf;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
        }

        .chat-footer button:hover {
            background-color: #e75480;
        }
    </style>
</head>

<body>
    <?php
    if ($isLoggedIn) {
        include('menu.php'); // Include menu for logged-in users
    } else {
        include('indexMenu.php'); // Include menu for guests
    }
    ?>

    <div class="main-container">
        <div class="chat-container">
            <div class="chat-header">
                <img src="uploads/user-icon.png" alt="User Icon">
                <strong>Chat with KM Tanay</strong>
            </div>
            <div class="chat-body">
                <div class="message user">
                    Hi! I’m looking to get some things printed. What services do you offer?
                </div>
                <div class="message bot">
                    Hello! We offer a variety of printing services, including:
                    <ul>
                        <li>PVC ID & Souvenir</li>
                        <li>Pull-up Banners and Panoflex</li>
                        <li>Custom Refrigerator Magnet</li>
                        <li>Stickers, Decals & Signage</li>
                        <li>Personalized Items like mugs and T-shirts</li>
                    </ul>
                    Is there something specific you’re interested in?
                </div>
                <div class="message user">
                    That’s great! I need some business cards and a few posters printed. Can you help with that?
                </div>
                <div class="message bot">
                    Absolutely! For business cards, we have several designs, paper types, and finishes like matte or glossy. For posters, we offer different sizes and paper quality options. Would you like us to send samples or pricing details?
                </div>
                <div class="message user">
                    Yes, please! Could you send me options for business card designs and poster sizes?
                </div>
                <div class="message bot">
                    Sure! I’ll email you our catalog with the designs and pricing. Let us know your preferences, and we can get started right away!
                </div>
            </div>
            <div class="chat-footer">
                <input type="text" placeholder="Type a message here">
                <button>Send</button>
            </div>
        </div>
    </div>

    <?php include('footer.php'); ?>
</body>

</html>
