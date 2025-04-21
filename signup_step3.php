<?php
session_start();
include('cnn.php');



// Retrieve values from session
$email = $_SESSION['email'];
$first_name = $_SESSION['first_name'];
$middle_name = isset($_SESSION['middle_name']) ? $_SESSION['middle_name'] : ''; // Handle if middle name is not set
$last_name = $_SESSION['last_name'];
$password = $_SESSION['password'];
$suffix = $_SESSION['suffix'];
$birthday = $_SESSION['birthday'];
$sex = $_SESSION['sex'];
$sex_other = isset($_SESSION['sex_other']) ? $_SESSION['sex_other'] : ''; // Handle if sex_other is not set
$nationality = $_SESSION['nationality'];
$phone_number = $_SESSION['phone_number'];
// Now you can proceed to your form processing code

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Store the address fields in session
    $_SESSION['unit_no'] = $_POST['unit_no'];
    $_SESSION['street'] = $_POST['street'];
    $_SESSION['village'] = $_POST['village'];
    $_SESSION['province'] = $_POST['province'];
    $_SESSION['city'] = $_POST['city'];
    $_SESSION['barangay'] = $_POST['barangay'];

    // Redirect to the next step (signup_step4.php)
    header("Location: signup_step4.php");
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


        <!-- Right Side - Address Form -->
        <div class="form-container">
            <h2><strong>Sign Up</strong></h2>
            <p>Where do you live?</p>

            <form action="signup_step3.php" method="POST">
                <input type="text" name="unit_no" placeholder="Unit no/House No/Building Number *" class="input-field"
                    required>
                <input type="text" name="street" placeholder="Street *" class="input-field" required>
                <input type="text" name="village" placeholder="Village/Subdivision" class="input-field">

                <div class="input-row">
                    <input type="text" name="city" placeholder="City *" class="input-field" required>
                    <input type="text" name="province" placeholder="Province *" class="input-field" required>
                    <input type="text" name="barangay" placeholder="Barangay *" class="input-field" required>
                </div>


                <button type="submit" class="btn1">Next</button>
            </form>

        </div>

    </div>

</body>

</html>