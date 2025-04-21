<?php
session_start();
include('cnn.php');

if (!isset($_SESSION['customer_id'])) {
    echo "Error: User not logged in.";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_id = $_SESSION['customer_id'];
    $address_id = intval($_POST['address_id']);

    // Set all other addresses for this customer to Default_address = 0
    $reset_sql = "UPDATE customer_address SET Default_address = 0 WHERE customer_id = ?";
    $reset_stmt = $conn->prepare($reset_sql);
    $reset_stmt->bind_param("i", $customer_id);
    $reset_stmt->execute();
    $reset_stmt->close();

    // Set the selected address to Default_address = 1
    $set_default_sql = "UPDATE customer_address SET Default_address = 1 WHERE customer_id = ? AND address_id = ?";
    $set_default_stmt = $conn->prepare($set_default_sql);
    $set_default_stmt->bind_param("ii", $customer_id, $address_id);

    if ($set_default_stmt->execute()) {
        echo "Default address updated successfully.";
    } else {
        echo "Error: " . $set_default_stmt->error;
    }

    $set_default_stmt->close();
    $conn->close();
} else {
    echo "Invalid request method.";
}
?>
