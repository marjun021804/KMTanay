<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $entered_code = $_POST['code'];

    if ($entered_code == $_SESSION['verification_code']) {
        // Redirect to the next signup step and keep email in session
        header('Location: signup_step2.php');
        exit();
    } else {
        $error = "❌ Invalid verification code.";
    }
}

// Check if email and verification code are stored in the session
if (isset($_SESSION['email']) && isset($_SESSION['verification_code'])) {
    $email = $_SESSION['email'];
} else {
    // Redirect if the session variables are not set
    header("Location: signup.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Verify Code</title>
    <style>
        body {
            font-family: Arial;
            background: #f9f9f9;
            text-align: center;
            padding-top: 100px;
        }
        form {
            background: white;
            display: inline-block;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        input {
            padding: 10px;
            margin: 10px;
            font-size: 16px;
        }
        .error {
            color: red;
            margin-top: 10px;
        }
    </style>
</head>
<body>

    <form method="POST">
        <h2>Enter Verification Code</h2>
        <input type="text" name="code" placeholder="6-digit code" required>
        <br>
        <button type="submit">Verify</button>
        <?php if (isset($error)) echo "<div class='error'>$error</div>"; ?>
    </form>

</body>
</html>
