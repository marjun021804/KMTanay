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
    <title>About Us - KM Tanay</title>
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

        .section {
            background-color: #ffe4ec;
            padding: 30px;
            margin: 40px 0 20px;
            border-radius: 12px;
        }

        .section h3 {
            margin-bottom: 20px;
            color: #333;
            font-weight: bold;
        }

        .about-section {
            text-align: justify;
            line-height: 1.6;
            color: #333;
        }

        .image-section {
            text-align: center;
            margin-top: 20px;
        }

        .image-section img {
            width: 100%;
            max-width: 800px;
            border-radius: 5px;
        }

        .quote {
            font-style: italic;
            background: white;
            padding: 10px;
            border-left: 5px solid #f89db2;
            margin: 20px 0;
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
        <div id="history-section" class="section">
            <h3>About Us</h3>
            <div class="about-section">
                <p>Welcome to KM Tanay, a business based in Tanay, Rizal, and proudly established in 2021. Our commitment to excellence drives us to deliver high-quality and cost-effective materials that meet the needs of our valued customers. Every product we offer is carefully crafted to ensure durability and functionality, reflecting our dedication to producing outputs that align with customer satisfaction. At KM Tanay, we believe that quality and affordability should go hand in hand, and we strive to embody this belief in every product and service we provide.</p>

                <p>To cater to modern customer demands, we have introduced a convenient web-based ordering system that makes purchasing from us easier than ever. From the initial browsing of products to placing and checking out orders, we prioritize a seamless and secure online transaction process. Our platform ensures that personal information is safeguarded at all times, giving our clients the peace of mind they deserve. By integrating user-friendly features and ensuring a smooth flow of operations, we aim to make online transactions effortless and accessible for everyone.</p>

                <p>Our dedication to excellence extends beyond the ordering process. At KM Tanay, we guarantee that every delivery is handled with the utmost care and arrives on time. Customers can trust that their orders will be accurate, free of damage, and meet their expectations. We understand the importance of reliability in every step of the transaction, and we go above and beyond to maintain the trust of our clients. Whether it is a small order or a bulk purchase, we treat each one with equal importance, ensuring that quality and punctuality remain consistent.</p>

                <p>To ensure a trouble-free delivery experience, we pay close attention to the packaging of each order. Our team carefully prepares every item, ensuring that it is secure and protected during transit. This attention to detail guarantees that products arrive in perfect condition, ready to meet the needs of our customers. At KM Tanay, we are not just about selling materials; we are about building trust and fostering long-lasting relationships with our clients through reliable service, quality products, and a genuine commitment to customer satisfaction.</p>
            </div>
        </div>

        <div id="location-section" class="section">
            <h3>Location</h3>
            <div class="image-section">
                <img src="uploads/location.jpg" alt="KM Tanay Store">
            </div>
            <div class="quote">We Print While You Wait</div>
        </div>
    </div>

    <?php include('footer.php'); ?>
</body>

</html>
