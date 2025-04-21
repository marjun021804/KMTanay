<?php
session_start();
include('cnn.php');

if (!isset($_SESSION['customer_id'])) {
    echo "Error: User not logged in.";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_id = $_SESSION['customer_id'];
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Fetch the hashed password from the database
    $sql = "SELECT password FROM customer_tb WHERE customer_id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("i", $customer_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $current_password_hashed = $row['password'];

            // Verify the current password
            if (!password_verify($current_password, $current_password_hashed)) {
                echo "Error: Current password is incorrect.";
                exit();
            }

            // Check if the new password is the same as the current password
            if (password_verify($new_password, $current_password_hashed)) {
                echo "Error: New password cannot be the same as the current password.";
                exit();
            }

            // Check if new password matches confirm password
            if ($new_password !== $confirm_password) {
                echo "Error: New password and confirm password do not match.";
                exit();
            }

            // Hash the new password
            $new_password_hashed = password_hash($new_password, PASSWORD_DEFAULT);

            // Update the password in the database
            $update_sql = "UPDATE customer_tb SET password = ? WHERE customer_id = ?";
            $update_stmt = $conn->prepare($update_sql);
            if ($update_stmt) {
                $update_stmt->bind_param("si", $new_password_hashed, $customer_id);
                if ($update_stmt->execute()) {
                    echo "Password updated successfully.";
                } else {
                    echo "Error: Could not update password.";
                }
                $update_stmt->close();
            } else {
                echo "Error: Failed to prepare the update statement.";
            }
        } else {
            echo "Error: User not found.";
        }
        $stmt->close();
    } else {
        echo "Error: Failed to prepare the select statement.";
    }
    $conn->close();
} else {
    echo "Invalid request method.";
}
?>
