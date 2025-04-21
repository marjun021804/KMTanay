<?php
session_start();
include('cnn.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_id = $_SESSION['customer_id'] ?? null;
    if (!$customer_id) {
        echo "Error: You must be logged in to place an order.";
        exit;
    }

    $message = $_POST['message'] ?? '';
    $payment_method = $_POST['payment_method'] ?? 'gcash';
    $current_date = date('Y-m-d H:i:s');
    $proof_of_payment_data = null;
    $reference_number = null;

    // Handle GCash-specific fields if the payment method is GCash
    if ($payment_method === 'gcash') {
        if (isset($_FILES['proof_of_payment']) && $_FILES['proof_of_payment']['error'] === UPLOAD_ERR_OK) {
            $proof_of_payment_data = file_get_contents($_FILES['proof_of_payment']['tmp_name']);
        } else {
            echo "Error: Proof of payment is required for GCash.";
            exit;
        }

        $reference_number = $_POST['reference_number'] ?? null;
        if (empty($reference_number)) {
            echo "Error: Reference number is required for GCash.";
            exit;
        }
    }

    // Fetch the default address of the logged-in user
    $address_query = "SELECT customer_first_name, customer_middle_name, customer_last_name 
                      FROM customer_address 
                      WHERE customer_id = ? AND Default_address = 1 LIMIT 1";
    $stmt = $conn->prepare($address_query);
    if (!$stmt) {
        echo "Error: Failed to prepare address query. " . $conn->error;
        exit;
    }
    $stmt->bind_param("i", $customer_id);
    $stmt->execute();
    $address_result = $stmt->get_result();
    if (!$address_result || $address_result->num_rows === 0) {
        echo "Error: Default address not found.";
        exit;
    }
    $address = $address_result->fetch_assoc();
    $stmt->close();

    $name = $address['customer_first_name'] . ' ' . $address['customer_middle_name'] . ' ' . $address['customer_last_name'];

    // Simulate auto-increment for order_id
    $order_id_query = "SELECT MAX(order_id) AS max_order_id FROM order_tb";
    $result = $conn->query($order_id_query);
    if ($result && $row = $result->fetch_assoc()) {
        $order_id = $row['max_order_id'] + 1;
    } else {
        $order_id = 1; // Start with 1 if no orders exist
    }

    // Fetch selected products from the session
    $selected_products = $_SESSION['selected_cart_items'] ?? [];
    if (empty($selected_products)) {
        echo "Error: No products selected for the order.";
        exit;
    }

    $cart_ids = implode(',', array_map('intval', $selected_products));
    $product_query = "SELECT p.image, c.product_name, c.price, c.quantity, c.discount, c.total_price, c.size, c.product_id 
                      FROM cart_tb c 
                      JOIN product_tb p ON c.product_id = p.product_id 
                      WHERE c.cart_id IN ($cart_ids) AND c.customer_id = ?";
    $stmt = $conn->prepare($product_query);
    if (!$stmt) {
        echo "Error: Failed to prepare product query. " . $conn->error;
        exit;
    }
    $stmt->bind_param("i", $customer_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if (!$result) {
        echo "Error: Failed to execute product query. " . $stmt->error;
        exit;
    }
    $products = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    // Insert data into order_tb
    foreach ($products as $product) {
        $insert_query = "INSERT INTO order_tb (order_id, user_id, name, product_id, product, price, quantity, discounted, total_price, payment_method, date_order, image, size, estimated_delivery, proof_of_payment, reference_number) 
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($insert_query);
        if (!$stmt) {
            echo "Error: Failed to prepare insert query. " . $conn->error;
            exit;
        }

        $guaranteed_date_start = date('d M', strtotime('+2 days'));
        $guaranteed_date_end = date('d M', strtotime('+3 days'));
        $estimated_delivery = $guaranteed_date_start . ' - ' . $guaranteed_date_end;

        $stmt->bind_param(
            "iisissiddsssssss",
            $order_id,
            $customer_id,
            $name,
            $product['product_id'],
            $product['product_name'],
            $product['price'],
            $product['quantity'],
            $product['discount'],
            $product['total_price'],
            $payment_method,
            $current_date,
            $product['image'],
            $product['size'],
            $estimated_delivery,
            $proof_of_payment_data,
            $reference_number
        );

        if (!$stmt->execute()) {
            echo "Error: Failed to insert order. " . $stmt->error;
            exit;
        }

        $stmt->close();
    }

    // Delete selected products from the cart
    $delete_query = "DELETE FROM cart_tb WHERE cart_id IN ($cart_ids) AND customer_id = ?";
    $stmt = $conn->prepare($delete_query);
    if (!$stmt) {
        echo "Error: Failed to prepare delete query. " . $conn->error;
        exit;
    }
    $stmt->bind_param("i", $customer_id);
    if (!$stmt->execute()) {
        echo "Error: Failed to execute delete query. " . $stmt->error;
        exit;
    }
    $stmt->close();

    // Clear selected cart items after placing the order
    unset($_SESSION['selected_cart_items']);

    // Redirect to order confirmation page
    header("Location: order_confirmation.php");
    exit;
}
?>
