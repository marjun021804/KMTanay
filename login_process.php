<?php
session_start();
include('cnn.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = htmlspecialchars(trim($_POST['email']));
    $password = trim($_POST['password']);

    // Check if the email exists in the database
    $sql = "SELECT * FROM customer_tb WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Verify the password
        if (password_verify($password, $user['password'])) {
            if (isset($user['Validation']) && $user['Validation'] == 1) { // Check if the account is validated
                $_SESSION['customer_id'] = $user['customer_id'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['full_name'] = $user['customer_first_name'] . ' ' . $user['customer_last_name'];
                header("Location: homepage.php"); // Redirect to homepage
                exit();
            } else {
                echo "<script>
                    alert('Your account is not validated.');
                    window.location.href = 'login.php'; // Redirect back to login page
                </script>";
                exit();
            }
        } else {
            echo "<script>
                alert('Invalid email or password.');
                window.location.href = 'login.php'; // Redirect back to login page
            </script>";
            exit();
        }
    } else {
        echo "<script>
            alert('Invalid email or password.');
            window.location.href = 'login.php'; // Redirect back to login page
        </script>";
        exit();
    }

  
}

$conn->close();
?>
