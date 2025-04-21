<?php
session_start();
include('cnn.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    if (isset($_SESSION['customer_id']) && isset($_SESSION['selected_cart_items']) && is_array($_SESSION['selected_cart_items'])) {
        $customer_id = $_SESSION['customer_id'];
        $cart_ids = implode(',', array_map('intval', $_SESSION['selected_cart_items']));
        $payment_method = $_POST['payment_method'] ?? 'cod';
        $message = $_POST['message'] ?? '';
        $current_date = date('Y-m-d H:i:s'); // Get the current date and time
        $estimated_delivery = $_POST['estimated_delivery'] ?? 'Standard Delivery';

        // Set estimated delivery to "Pick-up" if the payment method is "Pick-up"
        if ($payment_method === 'pickup') {
            $estimated_delivery = 'Pick-up';
        }

        // Fetch the default address of the logged-in user
        $address_query = "SELECT customer_first_name, customer_middle_name, customer_last_name 
                          FROM customer_address 
                          WHERE customer_id = ? AND Default_address = 1 LIMIT 1";
        $stmt = $conn->prepare($address_query);
        $default_address = null;

        if ($stmt) {
            $stmt->bind_param("i", $customer_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result && $result->num_rows > 0) {
                $default_address = $result->fetch_assoc();
            }
            $stmt->close();
        }

        if (!$default_address) {
            echo "<script>alert('No default address found. Please update your profile.'); window.location.href = 'checkout.php';</script>";
            exit;
        }

        // Simulate auto-increment for order_id
        $order_id_query = "SELECT MAX(order_id) AS max_order_id FROM order_tb";
        $result = $conn->query($order_id_query);
        if ($result && $row = $result->fetch_assoc()) {
            $order_id = $row['max_order_id'] + 1;
        } else {
            $order_id = 1; // Start with 1 if no orders exist
        }

        // Fetch selected products from the cart
        $selected_products = [];
        $product_query = "SELECT p.image, c.product_name, c.price, c.quantity, c.discount, c.total_price, c.size, c.product_id 
                          FROM cart_tb c 
                          JOIN product_tb p ON c.product_id = p.product_id 
                          WHERE c.cart_id IN ($cart_ids) AND c.customer_id = ?";
        $stmt = $conn->prepare($product_query);
        if ($stmt) {
            $stmt->bind_param("i", $customer_id);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $selected_products[] = $row;
            }
            $stmt->close();
        }

        if (empty($selected_products)) {
            echo "<script>alert('No products selected for the order.'); window.location.href = 'checkout.php';</script>";
            exit;
        }

        // Insert order details into the database
        foreach ($selected_products as $product) {
            $insert_query = "INSERT INTO order_tb (order_id, user_id, name, product_id, product, price, quantity, discounted, total_price, payment_method, message, date_order, image, size, estimated_delivery, proof_of_payment, reference_number) 
                             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NULL, NULL)";
            $stmt = $conn->prepare($insert_query);

            if ($stmt) {
                $name = $default_address['customer_first_name'] . ' ' . 
                        $default_address['customer_middle_name'] . ' ' . 
                        $default_address['customer_last_name'];
                $stmt->bind_param(
                    "iisissiddssssss",
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
                    $message,
                    $current_date,
                    $product['image'],
                    $product['size'],
                    $estimated_delivery
                );

                if (!$stmt->execute()) {
                    echo "<script>alert('Error inserting order: " . $stmt->error . "');</script>";
                    exit;
                }

                $stmt->close();
            } else {
                echo "<script>alert('Error preparing statement: " . $conn->error . "');</script>";
                exit;
            }
        }

        // Delete selected products from the cart
        $delete_query = "DELETE FROM cart_tb WHERE cart_id IN ($cart_ids) AND customer_id = ?";
        $stmt = $conn->prepare($delete_query);
        if ($stmt) {
            $stmt->bind_param("i", $customer_id);
            $stmt->execute();
            $stmt->close();
        } else {
            echo "<script>alert('Error deleting cart items: " . $conn->error . "');</script>";
            exit;
        }

        // Clear selected cart items after placing the order
        unset($_SESSION['selected_cart_items']);

        // Redirect to a confirmation page
        header("Location: order_confirmation.php");
        exit;
    } else {
        echo "<script>alert('Invalid request.'); window.location.href = 'checkout.php';</script>";
        exit;
    }
}
?>
