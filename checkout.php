<?php
session_start();
include('cnn.php');
include('link.php');

if (isset($_SESSION['customer_id'])) {
    include('menu.php'); // Include the menu for logged-in users
    $customer_id = $_SESSION['customer_id'];

    // Fetch the default address of the logged-in user from the customer_address table
    $address_query = "SELECT customer_first_name, customer_middle_name, customer_last_name, phone_number, 
                      house_number, Street, `Village/Subdivision`, Province, City, Barangay 
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

    // Fetch selected products from the cart
    $selected_products = [];
    if (isset($_SESSION['buy_now_item'])) {
        // Handle "Buy Now" product
        $selected_products = [$_SESSION['buy_now_item']];
        unset($_SESSION['buy_now_item']); // Clear the session after use
    } elseif (isset($_SESSION['selected_cart_items']) && is_array($_SESSION['selected_cart_items'])) {
        // Handle selected cart items
        $cart_ids = implode(',', array_map('intval', $_SESSION['selected_cart_items']));
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
    }
} else {
    include('indexMenu.php'); // Include the menu for guests
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    if (!empty($selected_products)) {
        $payment_method = $_POST['payment_method'] ?? 'gcash';
        $message = $_POST['message'] ?? '';
        $current_date = date('Y-m-d H:i:s'); // Get the current date and time

        // Convert guaranteed dates into a plain string
        $guaranteed_date_start = date('d M', strtotime('+2 days')); // Calculate the date 2 days ahead
        $guaranteed_date_end = date('d M', strtotime('+3 days')); // Calculate the date 3 days ahead
        $estimated_delivery = $guaranteed_date_start . ' - ' . $guaranteed_date_end;

        // Simulate auto-increment for order_id
        $order_id_query = "SELECT MAX(order_id) AS max_order_id FROM order_tb";
        $result = $conn->query($order_id_query);
        if ($result && $row = $result->fetch_assoc()) {
            $order_id = $row['max_order_id'] + 1;
        } else {
            $order_id = 1; // Start with 1 if no orders exist
        }

        // Check if the payment method is GCash
        if ($payment_method === 'gcash') {
            // Validate GCash-specific fields
            if (empty($_FILES['proof_of_payment']['tmp_name']) || empty($_POST['reference_number'])) {
                echo "<script>alert('Please upload proof of payment and provide a reference number for GCash payment.'); window.location.href = 'checkout.php';</script>";
                exit;
            }

            // Handle proof_of_payment file upload
            $proof_of_payment_data = file_get_contents($_FILES['proof_of_payment']['tmp_name']);
            $reference_number = $_POST['reference_number'];
        } else {
            // For "Pick-up" or "Cash on Delivery," set GCash-specific fields to null
            $proof_of_payment_data = null;
            $reference_number = null;
        }

        foreach ($selected_products as $product) {
            $insert_query = "INSERT INTO order_tb (order_id, user_id, name, product_id, product, price, quantity, discounted, total_price, payment_method, message, date_order, image, size, estimated_delivery, proof_of_payment, reference_number) 
                             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($insert_query);

            if ($stmt) {
                $name = $default_address['customer_first_name'] . ' ' . 
                        $default_address['customer_middle_name'] . ' ' . 
                        $default_address['customer_last_name'];
                $stmt->bind_param(
                    "iisissiddssssssss",
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
                    $estimated_delivery,
                    $proof_of_payment_data,
                    $reference_number
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
        echo "<script>alert('No products selected for the order.'); window.location.href = 'checkout.php';</script>";
        exit;
    }
}
$guaranteed_date_start = date('d M', strtotime('+2 days')); // Calculate the date 2 days ahead
$guaranteed_date_end = date('d M', strtotime('+3 days')); // Calculate the date 3 days ahead
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - KM Tanay</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #fff;
            /* Changed background color to white */
            margin: 0;
            padding: 0;
        }

        .container {
            width: 90%;
            max-width: 1200px;
            margin: 30px auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .section {
            margin: 20px 0;
            padding: 15px;
            background-color: #fce3ec;
            border-radius: 10px;
        }

        .section h3 {
            margin: 0 0 10px 0;
            font-size: 18px;
            color: #333;
        }

        .product {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }

        .product img {
            width: 60px;
            height: 60px;
            margin-right: 15px;
            border-radius: 5px;
            border: 1px solid #ddd;
        }

        .product-info {
            flex-grow: 1;
        }

        .product-info small {
            display: block;
            color: #555;
        }

        .product-pricing {
            text-align: right;
            font-weight: bold;
        }

        .place-order {
            text-align: right;
            margin-top: 20px;
        }

        .place-order button {
            padding: 12px 25px;
            background-color: #f79dbc;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }

        .place-order button:hover {
            background-color: #f56a8e;
        }

        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.5);
        }

        .modal-content {
            background-color: #fff;
            margin: 10% auto;
            padding: 20px;
            border-radius: 10px;
            width: 90%;
            max-width: 400px;
            text-align: center;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }

        .modal-content img {
            display: block; /* Center the image horizontally */
            margin: 0 auto; /* Add auto margins for centering */
            width: 150px;
            margin-bottom: 15px;
        }

        .modal-content input[type="file"],
        .modal-content input[type="text"] {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .modal-content button {
            padding: 10px 20px;
            margin: 5px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }

        .modal-content .cancel-btn {
            background-color: #f56a8e;
            color: white;
        }

        .modal-content .proceed-btn {
            background-color: #f79dbc;
            color: white;
        }
    </style>
    <script>
        function toggleGcashDetails() {
            const paymentMethod = document.querySelector('input[name="payment_method"]:checked').value;
            const gcashDetailsSection = document.getElementById('gcashDetailsSection');
            const proofOfPayment = document.querySelector('input[name="proof_of_payment"]');
            const referenceNumber = document.querySelector('input[name="reference_number"]');

            if (paymentMethod === 'gcash') {
                gcashDetailsSection.style.display = 'block';
                proofOfPayment.setAttribute('required', 'required');
                referenceNumber.setAttribute('required', 'required');
            } else {
                gcashDetailsSection.style.display = 'none';
                proofOfPayment.removeAttribute('required');
                referenceNumber.removeAttribute('required');
            }
        }

        function updateFormAction() {
            const paymentMethod = document.querySelector('input[name="payment_method"]:checked').value;
            const form = document.getElementById('checkoutForm');
            if (paymentMethod === 'gcash') {
                form.action = 'process_gcash.php';
            } else {
                form.action = 'process_order.php'; // Redirect to process_order.php for "Pick-up" or "Cash on Delivery"
            }
        }

        function openModal() {
            document.getElementById('gcashModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('gcashModal').style.display = 'none';
        }

        // Close the modal when clicking outside of it
        window.onclick = function(event) {
            const modal = document.getElementById('gcashModal');
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        };

        document.addEventListener('DOMContentLoaded', () => {
            // Initialize GCash details visibility and form action on page load
            toggleGcashDetails();
            updateFormAction();

            // Add event listeners to payment method radio buttons
            const paymentMethodRadios = document.querySelectorAll('input[name="payment_method"]');
            paymentMethodRadios.forEach(radio => {
                radio.addEventListener('change', () => {
                    toggleGcashDetails();
                    updateFormAction();
                });
            });
        });
    </script>
</head>

<body>
    <div class="container">
        <div class="section">
            <h3><strong>Delivery Address</strong></h3>
            <?php if (isset($default_address)): ?>
                <p>
                    <?php echo htmlspecialchars($default_address['customer_first_name'] . ' ' . $default_address['customer_middle_name'] . ' ' . $default_address['customer_last_name']); ?>
                    <strong>(+63) <?php echo htmlspecialchars($default_address['phone_number']); ?></strong><br>
                    <?php echo htmlspecialchars($default_address['house_number'] . ', ' . $default_address['Street']); ?><br>
                    <?php echo htmlspecialchars($default_address['Village/Subdivision']); ?><br>
                    <?php echo htmlspecialchars($default_address['Barangay'] . ', ' . $default_address['City'] . ', ' . $default_address['Province']); ?>
                </p>
                <form action="user_address.php" method="GET" style="margin-top: 10px;">
                    <button type="submit" style="background-color: #f79dbc; color: white; border: none; padding: 8px 12px; border-radius: 5px; cursor: pointer;">Change</button>
                </form>
            <?php else: ?>
                <p>No default address found. Please update your profile.</p>
                <form action="user_address.php" method="GET" style="margin-top: 10px;">
                    <button type="submit" style="background-color: #f79dbc; color: white; border: none; padding: 8px 12px; border-radius: 5px; cursor: pointer;">Add Address</button>
                </form>
            <?php endif; ?>
        </div>

        <div class="section">
            <h3><strong>Product Ordered</strong></h3>
            <table style="width: 100%; border-collapse: collapse; text-align: left;">
                <thead>
                    <tr style="background-color: #fce3ec;">
                        <th style="padding: 10px;">Image</th>
                        <th style="padding: 10px;">Product</th>
                        <th style="padding: 10px;">Size</th>
                        <th style="padding: 10px;">Price</th>
                        <th style="padding: 10px;">Quantity</th>
                        <th style="padding: 10px;">Discount</th>
                        
                        <th style="padding: 10px;">Sub Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($selected_products)): ?>
                        <?php foreach ($selected_products as $product): ?>
                            <tr>
                                <td style="padding: 10px;">
                                    <img src="<?php echo htmlspecialchars("/KMTanayAdmin/" . $product['image']); ?>"
                                        alt="<?php echo htmlspecialchars($product['product_name']); ?>"
                                        style="width: 60px; height: 60px; border-radius: 5px; border: 1px solid #ddd;">
                                </td>
                                <td style="padding: 10px;"><?php echo htmlspecialchars($product['product_name']); ?></td>
                                <td style="padding: 10px;"><?php echo htmlspecialchars($product['size']); ?></td>
                                <td style="padding: 10px;">₱<?php echo number_format($product['price'], 2); ?></td>
                                <td style="padding: 10px;"><?php echo htmlspecialchars($product['quantity']); ?></td>
                                <td style="padding: 10px;"><?php echo htmlspecialchars($product['discount'] * 100); ?>%</td>
                               
                                <td style="padding: 10px;">₱<?php echo number_format($product['total_price'], 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="padding: 10px; text-align: center;">No products selected.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <form id="checkoutForm" method="POST" action="process_gcash.php" enctype="multipart/form-data">
            <!-- The action will be dynamically updated by JavaScript -->
            <input type="hidden" name="estimated_delivery" value="<?php echo htmlspecialchars($guaranteed_date_start . ' - ' . $guaranteed_date_end); ?>">
            <div class="section">
                <h3><strong>Message for Seller</strong></h3>
                <textarea name="message" placeholder="Type Here" style="width: 100%; height: 60px; padding: 10px; border: 1px solid #ccc; border-radius: 5px;"></textarea>
            </div>

            <div class="section">
                <h3><strong>Payment Method</strong></h3>
                <label><input type="radio" name="payment_method" value="cod"> <i class="fa-solid fa-money-bill"></i>   Cash on Delivery</label><br>
                <label><input type="radio" name="payment_method" value="pickup"> <i class="fa-solid fa-box-open"></i>   On Pick-up</label><br>
                <label><input type="radio" name="payment_method" value="gcash" checked> <i class="fa-solid fa-g"></i>   G-Cash Payment</label>
            </div>

            <div id="gcashDetailsSection" class="section" style="display: none;">
                <h3><strong>G-Cash Payment Details</strong></h3>
                <p style="text-align: center; font-size: 14px; color: #555;">(Click the image to expand)</p>
                <img src="uploads/gcash.jpg" alt="G-Cash QR Code" style="width: 150px; margin-bottom: 15px; display: block; margin-left: auto; margin-right: auto; border: 1px solid #ddd; border-radius: 5px; cursor: pointer;" onclick="openModal()">
                <p style="text-align: center; font-weight: bold;">MA***N M.</p>
                <p style="text-align: center; font-weight: bold;">09948411246</p>
                <input type="file" name="proof_of_payment" required>
                <p>*Upload the Proof of Payment*</p>
                <input type="text" name="reference_number" placeholder="Reference Number" required>
                <p>*Enter the 13-digit Reference Number*</p>
                <div style="margin-top: 15px; padding: 10px; background-color: #fff3cd; border: 1px solid #ffeeba; border-radius: 5px; color: #856404;">
                    <strong>Note:</strong> The payment status will initially be set to <strong>"PENDING"</strong> upon submission of payment details. This status indicates that the payment is awaiting verification. An administrator will review and verify the submitted payment information before updating the status to <strong>"PAID"</strong>.
                </div>
            </div>

            <!-- Modal for expanded image, name, and number -->
            <div id="gcashModal" class="modal">
                <div class="modal-content">
                    <span class="close" onclick="closeModal()">&times;</span>
                    <img src="uploads/gcash.jpg" alt="G-Cash QR Code" style="width: 100%; max-width: 400px; margin-bottom: 15px; border: 1px solid #ddd; border-radius: 5px;">
                    <p style="text-align: center; font-weight: bold; font-size: 18px;">MA***N M.</p>
                    <p style="text-align: center; font-weight: bold; font-size: 18px;">09948411246</p>
                </div>
            </div>

            <div class="section">
                <h3><strong>Shipping Information</strong></h3>
                <p>Standard Local</p>
                <p>Estimated to arrive by: <i class="fa-solid fa-truck-fast"></i> <?php echo $guaranteed_date_start; ?> - <?php echo $guaranteed_date_end; ?></p> <!-- Updated date range -->
                <p>Shipping Fee: ₱40</p>
                <small>*If you chose <strong>“Pick-up”</strong> option you will get notified once the order(s) is ready*</small>
            </div>

            <div class="section">
                <h3><strong>Order Summary</strong></h3>
                <?php
                $merchandise_subtotal = 0;
                $shipping_fee = 40; // Default shipping fee for "Standard Local"

                // Calculate merchandise subtotal
                foreach ($selected_products as $product) {
                    $merchandise_subtotal += $product['total_price'];
                }

                // Calculate total payment
                $total_payment = $merchandise_subtotal + $shipping_fee;
                ?>
                <table style="width: 100%; border-collapse: collapse; text-align: left;">
                    <tr>
                        <td style="padding: 10px;">Merchandise Subtotal</td>
                        <td style="padding: 10px; text-align: right;">₱<?php echo number_format($merchandise_subtotal, 2); ?></td>
                    </tr>
                    <tr>
                        <td style="padding: 10px;">Shipping Subtotal</td>
                        <td style="padding: 10px; text-align: right;">₱<?php echo number_format($shipping_fee, 2); ?></td>
                    </tr>
                    <tr style="font-weight: bold;">
                        <td style="padding: 10px;">Total Payment</td>
                        <td style="padding: 10px; text-align: right;">₱<?php echo number_format($total_payment, 2); ?></td>
                    </tr>
                </table>
            </div>

            <div class="place-order">
                <button type="submit" name="place_order" style="padding: 12px 25px; background-color: #f79dbc; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 16px;">Place Order</button>
            </div>
        </form>
    </div>

    <?php include('footer.php'); ?>
</body>

</html>