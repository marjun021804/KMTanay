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

    // Delete the specific address
    $delete_sql = "DELETE FROM customer_address WHERE customer_id = ? AND address_id = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("ii", $customer_id, $address_id);

    if ($delete_stmt->execute()) {
        echo "Address deleted successfully.";
    } else {
        echo "Error: " . $delete_stmt->error;
    }

    $delete_stmt->close();
    $conn->close();
} else {
    echo "Invalid request method.";
}
?>
