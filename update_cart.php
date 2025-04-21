<?php
session_start();
include('cnn.php');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    $cart_id = $input['cart_id'] ?? null;
    $quantity = $input['quantity'] ?? null;

    if ($cart_id && $quantity) {
        // Fetch the price of the product from cart_tb
        $sql = "SELECT price FROM cart_tb WHERE cart_id = ?";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("i", $cart_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $price = $row['price'] ?? 0;
            $stmt->close();

            // Calculate the new total price
            $new_total_price = $price * $quantity;

            // Update the quantity and total_price in cart_tb
            $update_sql = "UPDATE cart_tb SET quantity = ?, total_price = ? WHERE cart_id = ?";
            $update_stmt = $conn->prepare($update_sql);
            if ($update_stmt) {
                $update_stmt->bind_param("idi", $quantity, $new_total_price, $cart_id);
                if ($update_stmt->execute()) {
                    echo json_encode(['success' => true, 'new_total_price' => $new_total_price]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Failed to update cart.']);
                }
                $update_stmt->close();
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to prepare update statement.']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to fetch product price.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid input.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}

$conn->close();
?>
