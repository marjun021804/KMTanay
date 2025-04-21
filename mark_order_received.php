<?php
session_start();
include('cnn.php'); // Include the database connection

if (!isset($_SESSION['customer_id'])) {
    echo "<script>alert('Please log in to update your order status.'); window.location.href = 'login.php';</script>";
    exit();
}

if (isset($_GET['product_id'])) {
    $product_id = intval($_GET['product_id']);
    $user_id = $_SESSION['customer_id'];

    // Update the order status to "completed"
    $sql = "UPDATE order_tb SET order_status = 'completed' WHERE product_id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("ii", $product_id, $user_id);
        if ($stmt->execute()) {
            echo "<script>alert('Order marked as received.'); window.location.href = 'my_orders.php';</script>";
        } else {
            echo "<script>alert('Failed to update order status. Please try again.'); window.location.href = 'my_orders.php';</script>";
        }
        $stmt->close();
    } else {
        echo "<script>alert('Error preparing the query.'); window.location.href = 'my_orders.php';</script>";
    }
} else {
    echo "<script>alert('Invalid request.'); window.location.href = 'my_orders.php';</script>";
}

$conn->close();
?>
