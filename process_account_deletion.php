<?php
session_start();
include('cnn.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_id = $_POST['customer_id'];
    $customer_name = $_POST['customer_name'];
    $reason = $_POST['reason'];

    // Validate required fields
    if (empty($customer_id) || empty($customer_name) || empty($reason)) {
        echo "Error: All fields are required.";
        exit();
    }

    // Fetch the email from customer_tb
    $email = "";
    $emailQuery = "SELECT email FROM customer_tb WHERE customer_id = ?";
    $emailStmt = $conn->prepare($emailQuery);
    if ($emailStmt) {
        $emailStmt->bind_param("i", $customer_id);
        $emailStmt->execute();
        $emailResult = $emailStmt->get_result();
        if ($emailResult && $emailRow = $emailResult->fetch_assoc()) {
            $email = $emailRow['email'];
        }
        $emailStmt->close();
    }

    if (empty($email)) {
        echo "Error: Email not found for the specified customer.";
        exit();
    }

    // Insert the request into the request_for_deletion table
    $sql = "INSERT INTO request_for_deletion (customer_id, customer_name, reason, email) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("isss", $customer_id, $customer_name, $reason, $email);
        if ($stmt->execute()) {
            // Delete the account from customer_tb
            $deleteQuery = "DELETE FROM customer_tb WHERE customer_id = ?";
            $deleteStmt = $conn->prepare($deleteQuery);
            if ($deleteStmt) {
                $deleteStmt->bind_param("i", $customer_id);
                if ($deleteStmt->execute()) {
                    echo "The deletion of your account has been processed successfully.";
                } else {
                    echo "Error: Could not delete the account. " . $deleteStmt->error;
                }
                $deleteStmt->close();
            } else {
                echo "Error: Failed to prepare the delete statement. " . $conn->error;
            }
        } else {
            // Display the error for troubleshooting
            echo "Error: Could not submit your request. " . $stmt->error;
        }
        $stmt->close();
    } else {
        // Display the error for troubleshooting
        echo "Error: Failed to prepare the statement. " . $conn->error;
    }

    $conn->close();
} else {
    echo "Invalid request method.";
}
?>
