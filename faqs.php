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
    <title>FAQs - KM Tanay</title>
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

        .faq-box {
            background: #fff;
            padding: 15px;
            margin: 10px 0;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .faq-box:hover {
            background-color: #ffe4ec;
        }

        .faq-box .question {
            font-weight: bold;
            color: #333;
        }

        .faq-box .answer {
            display: none;
            margin-top: 10px;
            color: #555;
            text-align: left;
        }

        .faq-box.open .answer {
            display: block;
        }
    </style>
    <script>
        function toggleAnswer(element) {
            element.classList.toggle('open');
        }
    </script>
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
        <div class="section">
            <h3>Frequently Asked Questions</h3>
            <?php
            $faqs = [
                "What printing services do you offer?" => "KM Tanay offers various printing services such as printing documents, pictures, stickers, signage, pull-up banners, panels, decals, PVC IDs, mugs, scanning services, and t-shirt printing.",
                "What file formats do you accept for printing?" => "We accept PDF, PNG, and JPEG file formats.",
                "How can I place an order?" => "You can place an order through our website or by contacting us directly.",
                "What is your pricing structure?" => "Our pricing depends on the type and quantity of the order.",
                "Do you offer discounts for bulk printing?" => "Yes, we offer discounts for bulk printing orders.",
                "Do you offer large-format printing?" => "Yes, we provide large-format printing services.",
                "Can I get a quote before placing an order?" => "Yes, you can request a quote before placing an order.",
                "What is the turnaround time for printing orders?" => "Turnaround time depends on the size and complexity of the order.",
                "Do you accept same-day or rush printing services?" => "Yes, we offer same-day or rush printing services upon request.",
                "Do you require a deposit for large orders?" => "Yes, a deposit is required for large orders.",
                "Do you offer package installation for example panflex?" => "Yes, we provide package installation services.",
                "How can I track my order once it has shipped?" => "You can track your order through our website or by contacting us.",
                "Can I pick up my order in-store?" => "Yes, in-store pickup is available.",
                "Do you offer shipping services?" => "Yes, we provide shipping services."
            ];
            foreach ($faqs as $question => $answer) {
                echo '<div class="faq-box" onclick="toggleAnswer(this)">';
                echo '<div class="question">' . htmlspecialchars($question) . '</div>';
                echo '<div class="answer">' . htmlspecialchars($answer) . '</div>';
                echo '</div>';
            }
            ?>
        </div>
    </div>

    <?php include('footer.php'); ?>
</body>

</html>
