<?php
session_start();
include('cnn.php');

if (!isset($_SESSION['customer_id'])) {
    header("Location: login.php");
    exit();
}

if (isset($_GET['product_id'])) {
    $product_id = intval($_GET['product_id']);
    $customer_id = $_SESSION['customer_id'];

    $sql = "UPDATE order_tb SET request = 'Request for Cancellation' WHERE product_id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("ii", $product_id, $customer_id);
        if ($stmt->execute()) {
            echo "<script>alert('Cancellation request submitted successfully.'); window.location.href='my_orders.php';</script>";
        } else {
            echo "<script>alert('Failed to submit cancellation request.'); window.location.href='my_orders.php';</script>";
        }
        $stmt->close();
    } else {
        echo "<script>alert('Error preparing the request.'); window.location.href='my_orders.php';</script>";
    }
} else {
    echo "<script>alert('Invalid request.'); window.location.href='my_orders.php';</script>";
}

$conn->close();
?>
