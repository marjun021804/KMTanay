<?php
session_start();
include('cnn.php');

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email']);

    // Check if the email exists in the customer_tb table
    $stmt = $conn->prepare("SELECT * FROM customer_tb WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // Check if the account is validated
        if (isset($user['Validation']) && $user['Validation'] == 1) {
            // Set session values
            $_SESSION['customer_id'] = $user['customer_id'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['full_name'] = $user['customer_first_name'] . ' ' . $user['customer_last_name'];

            echo json_encode(["success" => true]);
        } else {
            echo json_encode(["success" => false, "message" => "Your account is not validated."]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "Email not registered. Please sign up first."]);
    }

    $stmt->close();
    $conn->close();
}
?>
