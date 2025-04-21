<?php
session_start();
include('cnn.php');
if (isset($_SESSION['verified'])) {
    echo "<p style='color:green;'>✅ Verification successful!</p>";
    unset($_SESSION['verified']);
}


// Check if email is stored in the session
if (isset($_SESSION['email'])) {
    $email = $_SESSION['email'];
    $password = $_SESSION['password'];
} else {
    // Redirect if the email is not in the session
    header("Location: signup.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Store the form values in session variables
    $_SESSION['first_name'] = $_POST['first_name'];
    $_SESSION['middle_name'] = $_POST['middle_name'];
    $_SESSION['last_name'] = $_POST['last_name'];
    $_SESSION['suffix'] = $_POST['suffix'];
    $_SESSION['birthday'] = $_POST['birthday'];
    $_SESSION['sex'] = $_POST['sex'];
    $_SESSION['sex_other'] = isset($_POST['sex_other']) ? $_POST['sex_other'] : ''; // Only if 'Other' is selected
    $_SESSION['nationality'] = $_POST['nationality'];
    $_SESSION['phone_number'] = $_POST['phone_number']; // Store phone number

    // Handle profile picture upload
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/profile_pictures/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true); // Create directory if it doesn't exist
        }
        $fileName = basename($_FILES['profile_picture']['name']);
        $targetFilePath = $uploadDir . uniqid() . '_' . $fileName;

        if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $targetFilePath)) {
            $_SESSION['profile_picture'] = $targetFilePath; // Store file path in session
        } else {
            $_SESSION['profile_picture'] = ''; // Set to empty if upload fails
        }
    } else {
        $_SESSION['profile_picture'] = ''; // Set to empty if no file is uploaded
    }

    // Redirect to the next step
    header("Location: signup_step3.php");
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

        <!-- Right Side - Detailed Form -->
        <div class="form-container">
            <h2><strong>Sign Up</strong></h2>

            <form action="signup_step2.php" method="POST" enctype="multipart/form-data">
                <div class="form-row">
                    <input type="text" name="first_name" placeholder="First Name *" class="input-field" required>
                    <input type="text" name="middle_name" placeholder="Middle Name" class="input-field">
                </div>

                <div class="form-row">
                    <input type="text" name="last_name" placeholder="Last Name *" class="input-field" required>
                    <input type="text" name="suffix" placeholder="Suffix" class="input-field">
                </div>

                <div class="form-row">
                    <label for="birthday">Birthday:</label>
                    <input type="date" id="birthday" name="birthday" class="input-field" required>
                </div>

                <div class="form-row" style="text-align: left;">
                    <label><strong>Sex: </strong></label><br>
                    <label><input type="radio" name="sex" value="Male" required> Male</label><br>
                    <label><input type="radio" name="sex" value="Female"> Female</label><br>
                    <label>
                        <input type="radio" name="sex" value="Other" id="sexOtherRadio"> Other
                        <input type="text" name="sex_other" id="sexOtherText"
                            style="display:none; margin-left: 10px;">
                    </label>
                </div>


                <div class="form-row">
                    <input type="text" name="nationality" placeholder="Nationality *" class="input-field" required>
                </div>

                <div class="form-row">
                    <input type="text" name="phone_number" placeholder="Phone Number *" class="input-field" required>
                </div>

                <div class="form-row">
                    <label for="profile_picture">Profile Picture (Optional):</label>
                    <input type="file" id="profile_picture" name="profile_picture" class="input-field" accept="image/*">
                </div>

                <button type="submit" class="btn1">Next</button>
            </form>
        </div>

    </div>

</body>


<script>
    const otherRadio = document.getElementById('sexOtherRadio');
    const otherText = document.getElementById('sexOtherText');

    document.querySelectorAll('input[name="sex"]').forEach(radio => {
        radio.addEventListener('change', () => {
            if (otherRadio.checked) {
                otherText.style.display = 'inline-block';
                otherText.required = true;
            } else {
                otherText.style.display = 'none';
                otherText.required = false;
                otherText.value = '';
            }
        });
    });
</script>


</html>