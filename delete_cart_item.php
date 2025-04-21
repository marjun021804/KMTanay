<?php
session_start();
include('cnn.php');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    $cart_id = $input['cart_id'] ?? null;

    if ($cart_id) {
        $delete_sql = "DELETE FROM cart_tb WHERE cart_id = ?";
        $stmt = $conn->prepare($delete_sql);
        if ($stmt) {
            $stmt->bind_param("i", $cart_id);
            if ($stmt->execute()) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to delete item.']);
            }
            $stmt->close();
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to prepare delete statement.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid cart ID.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}

$conn->close();
?>
