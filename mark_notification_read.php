<?php
session_start();
include('cnn.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['notification_id'])) {
    if (isset($_SESSION['customer_id'])) {
        $customer_id = $_SESSION['customer_id'];
        $notification_id = intval($_POST['notification_id']);

        // Update the notification to mark it as read
        $sql = "UPDATE notification SET is_read = 1 WHERE notification_id = ? AND customer_id = ?";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("ii", $notification_id, $customer_id);
            $stmt->execute();
            $stmt->close();
        }
    }
}

// Redirect back to the notifications page
header("Location: notification.php");
exit;
