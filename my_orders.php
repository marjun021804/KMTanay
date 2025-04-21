<?php
header('Content-Type: text/html; charset=UTF-8');
session_start();
include('cnn.php'); // Ensure this path is correct

// Corrected login check: Use 'customer_id' or another variable set during login
$isLoggedIn = isset($_SESSION['customer_id']);
// Optional: Get user name if needed, using a variable set during login
$username = $isLoggedIn && isset($_SESSION['full_name']) ? $_SESSION['full_name'] : null;

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders</title>
    <link rel="stylesheet" href="style.css">
    <?php include('link.php'); ?>
    <style>
        /* Paste the <style> content from your original file here */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #fff;
        }

        /* Remove the .navbar style if it's handled by menu.php/indexMenu.php */
        /* .navbar { ... } */

        .search-bar {
            text-align: center;
            margin: 20px;
            padding-top: 80px; /* Add padding to push content below fixed header */
        }

        .search-bar input {
            width: 50%;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        .tabs {
            display: flex;
            justify-content: center;
            background: #f8a5c2;
            padding: 10px;
            flex-wrap: wrap; /* Allow tabs to wrap on smaller screens */
        }

        .tabs button {
            background: none;
            border: none;
            padding: 10px 15px;
            color: white;
            cursor: pointer;
            font-size: 14px; /* Adjust font size */
            font-weight: bold; /* Make text bold */
        }

        .tabs button:hover:not(.active) { /* Style for hover, excluding active buttons */
            background-color: #e56b92;
            border-radius: 5px;
        }

        .tabs button.active {
            background-color: #e56b92; /* Same color for active buttons, including "All" */
            border-radius: 5px;
        }

        .container {
            width: 90%; /* Adjust width */
            max-width: 1200px; /* Add max-width */
            margin: auto;
            padding: 20px 0; /* Adjust padding */
        }

        .order-box {
            background: #fff; /* Changed background color to white */
            padding: 15px;
            margin: 15px auto; /* Center the section horizontally */
            border-radius: 10px;
            display: flex;
            flex-direction: column; /* Stack content vertically */
            gap: 15px; /* Add gap between elements */
            box-shadow: 0 2px 4px rgba(0,0,0,0.1); /* Optional shadow */
            max-width: 800px; /* Limit the maximum width */
        }

        .order-header {
            display: flex;
            width: 100%;
            justify-content: space-between; /* Space out the image and details */
            align-items: center;
        }

        .order-box img {
            width: 70px;
            height: 70px;
            object-fit: cover; /* Ensure image covers the area */
            border-radius: 5px; /* Optional rounded corners */
            margin-right: 15px;
            flex-shrink: 0; /* Prevent image from shrinking */
        }

        .order-box .details {
            flex-grow: 1; /* Allow details to take up space */
            min-width: 200px; /* Minimum width before wrapping */
        }
        .order-box .details strong {
            display: block; /* Ensure name is on its own line */
            margin-bottom: 5px;
        }
        .order-box .details small {
            display: block;
            margin-top: 5px;
            color: #666;
        }

        .order-box .buttons {
            display: flex;
            flex-wrap: wrap; /* Allow buttons to wrap */
            gap: 10px;
            margin-left: auto; /* Push buttons to the right */
            align-items: center; /* Align buttons vertically */
        }

        .order-box button {
            background: #f8a5c2;
            border: none;
            padding: 8px 12px;
            color: white;
            border-radius: 5px;
            cursor: pointer;
            font-size: 13px; /* Adjust button text size */
            white-space: nowrap; /* Prevent button text wrapping */
            font-weight: bold; /* Make text bold */
        }
        .order-box button:hover {
            background-color: #e56b92;
        }

        .order-status-top-right {
            position: absolute;
            top: 10px;
            right: 10px;
            background: #f8a5c2;
            padding: 5px 10px;
            border-radius: 5px;
            color: white;
        }

        .order-actions {
            display: flex;
            flex-direction: column; /* Arrange buttons vertically */
            align-items: flex-end; /* Align buttons to the right */
            gap: 10px; /* Add spacing between buttons */
            margin-left: auto; /* Push the buttons to the right side of the section */
        }

        .action-buttons {
            display: flex;
            flex-direction: column; /* Ensure buttons are stacked vertically */
            gap: 10px; /* Add spacing between buttons */
        }

        .order-details {
            width: 100%; /* Make details take full width */
            margin: 15px auto; /* Center the details vertically */
            padding: 15px;
            background: #fff; /* Add a background color for contrast */
            border-radius: 10px; /* Add rounded corners */
            box-shadow: 0 2px 4px rgba(0,0,0,0.1); /* Optional shadow */
        }

        .view-details-button {
            margin-top: 15px; /* Add spacing above the button */
            align-self: flex-start; /* Align the button to the left */
        }

        /* Footer style is likely in footer.php, ensure it's included */

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .search-bar input {
                width: 80%;
            }
            .container {
                width: 95%;
            }
            .tabs button {
                padding: 8px 10px;
                font-size: 12px;
            }
            .order-box {
                flex-direction: column; /* Stack elements vertically */
                align-items: flex-start; /* Align items left */
            }
            .order-box img {
                margin-bottom: 10px;
            }
            .order-box .buttons {
                margin-left: 0; /* Remove margin override */
                width: 100%; /* Make buttons take full width */
                justify-content: flex-start; /* Align buttons left */
                margin-top: 10px;
            }
        }
        @media (max-width: 480px) {
            .tabs button {
                flex-grow: 1; /* Make tabs distribute space */
                font-size: 11px;
                padding: 8px 5px;
            }
            .search-bar input {
                width: 90%;
            }
            .order-box button {
                width: calc(50% - 5px); /* Two buttons per row */
                text-align: center;
            }
            .order-box button:nth-child(3) { /* Adjust third button if needed */
                width: 100%;
            }
        }

        .btn-cancel-request {
            background: #ff6b6b; /* Red color for cancellation */
            border: none;
            padding: 8px 12px;
            color: white;
            border-radius: 5px;
            cursor: pointer;
            font-size: 13px; /* Adjust button text size */
            white-space: nowrap; /* Prevent button text wrapping */
        }

        .btn-cancel-request:hover {
            background-color: #e63946; /* Darker red on hover */
        }
    </style>

    <script>
        // JavaScript for toggling order details
        function toggleDetails(button) {
            // Correctly find the .order-details sibling of the button's parent container
            const orderBox = button.closest('.order-box'); // Find the closest .order-box
            const details = orderBox.querySelector('.order-details'); // Find the .order-details within the same .order-box

            if (details.style.display === "none" || details.style.display === "") {
                details.style.display = "block"; // Show details
                button.textContent = "Hide Details"; // Update button text
            } else {
                details.style.display = "none"; // Hide details
                button.textContent = "View Details"; // Update button text
            }
        }

        // JavaScript for filtering orders by status
        function filterOrders(status) {
            const orderBoxes = document.querySelectorAll('.order-box');
            orderBoxes.forEach(orderBox => {
                const orderStatus = orderBox.getAttribute('data-status'); // Get the order status from the data attribute
                if (status === 'all' || orderStatus === status) {
                    orderBox.style.display = 'block'; // Show matching orders
                } else {
                    orderBox.style.display = 'none'; // Hide non-matching orders
                }
            });
        }

        function requestCancellation(productId) {
            if (confirm("Are you sure you want to request cancellation for this order?")) {
                // Redirect to a PHP script to handle the cancellation request
                window.location.href = `request_cancellation.php?product_id=${productId}`;
            }
        }

        function markOrderReceived(productId) {
            if (confirm("Have you received this order?")) {
                // Redirect to a PHP script to handle marking the order as received
                window.location.href = `mark_order_received.php?product_id=${productId}`;
            }
        }
    </script>
</head>

<body>
    <?php
    // This logic now correctly includes the right menu based on login status
    if ($isLoggedIn) {
        include('menu.php'); // Logged-in menu
    } else {
        include('indexMenu.php');
        echo "<p style='text-align: center; padding: 20px;'>Please <a href='login.php'>log in</a> to view your orders.</p>";
        exit(); // Exit if the user is not logged in
    }
    ?>

   

    <div class="tabs">
        <button onclick="filterOrders('all')">All</button>
        <button onclick="filterOrders('to pay')">To Pay</button>
        <button onclick="filterOrders('to ship')">To Ship</button>
        <button onclick="filterOrders('to receive')">To Receive</button>
        <button onclick="filterOrders('completed')">Completed</button>
        <button onclick="filterOrders('cancelled')">Cancelled</button>
    </div>

    <div class="container">
        <?php
        // Fetch orders from the database for the logged-in user
        if ($isLoggedIn) {
            $sql_orders = "SELECT name, product, price, quantity, discounted, total_price, payment_method, payment_status, date_order, message, image, size, estimated_delivery, order_status, product_id 
                           FROM order_tb 
                           WHERE user_id = ? 
                           ORDER BY date_order DESC";
            $stmt_orders = $conn->prepare($sql_orders);

            if ($stmt_orders) {
                $stmt_orders->bind_param("i", $_SESSION['customer_id']);
                $stmt_orders->execute();
                $result_orders = $stmt_orders->get_result();

                if ($result_orders->num_rows > 0) {
                    while ($order = $result_orders->fetch_assoc()) {
                        echo '<div class="order-box" data-status="' . htmlspecialchars(strtolower($order['order_status'])) . '">'; // Add data-status attribute
                        echo '<div class="order-header">';
                        echo '<img src="' . htmlspecialchars("/KMTanayAdmin/" . $order['image']) . '" alt="Product Image">';
                        echo '<div class="order-info">';
                        echo '<strong>' . htmlspecialchars($order['product']) . '</strong>';
                        echo '<p>Quantity: x' . htmlspecialchars($order['quantity']) . '</p>';
                        echo '<p><strong>Total Price:</strong> ₱' . number_format($order['total_price'], 2) . '</p>';
                        echo '</div>';
                        echo '<div class="order-actions">';
                        echo '<div class="action-buttons">';
                        echo '<button class="btn-contact" onclick="location.href=\'chat.php\'">Contact KM Tanay</button>';
                        echo '<button class="btn-buy-again" onclick="location.href=\'product_detail.php?product_name=' . urlencode($order['product']) . '\'">Buy Again</button>';
                        
                        // Check if the order is in "to receive" status and payment status is "paid"
                        if (strtolower($order['order_status']) === 'to receive' && strtolower($order['payment_status']) === 'paid') {
                            echo '<button class="btn-order-received" onclick="markOrderReceived(' . htmlspecialchars($order['product_id']) . ')">Order Received</button>';
                        } elseif (strtolower($order['order_status']) !== 'completed') {
                            // Show "Request for Cancellation" only if the order is not "completed"
                            echo '<button class="btn-cancel-request" onclick="requestCancellation(' . htmlspecialchars($order['product_id']) . ')">Request for Cancellation</button>';
                        }

                        echo '</div>';
                        echo '</div>';
                        echo '</div>'; // Close order-header
                        echo '<button class="btn-toggle-details view-details-button" onclick="toggleDetails(this)">View Details</button>'; // Button aligned to the left
                        echo '<div class="order-details" style="display: none;">'; // Centered details
                        echo '<p><strong>Size:</strong> ' . htmlspecialchars($order['size']) . '</p>';
                        echo '<p><strong>Price:</strong> ₱' . number_format($order['price'], 2) . '</p>';
                        echo '<p><strong>Discount:</strong> ' . htmlspecialchars($order['discounted']) . '%</p>';
                        echo '<p><strong>Payment Method:</strong> ' . htmlspecialchars(ucfirst($order['payment_method'])) . '</p>';
                        echo '<p><strong>Order Date:</strong> ' . htmlspecialchars($order['date_order']) . '</p>';
                        echo '<p><strong>Estimated Delivery:</strong> ' . htmlspecialchars($order['estimated_delivery']) . '</p>'; // Added estimated delivery
                        echo '<p><strong>Message:</strong> ' . htmlspecialchars($order['message']) . '</p>';
                        echo '<p><strong>Payment Status:</strong> ' . htmlspecialchars(strtoupper($order['payment_status'])) . '</p>';
                        echo '</div>';
                        echo '</div>'; // Close order-box
                    }
                } else {
                    echo "<p>You haven't placed any orders yet.</p>";
                }

                $stmt_orders->close();
            } else {
                echo "<p>Error fetching orders.</p>";
                error_log("Error preparing order query: " . $conn->error);
            }
        }
        ?>
    </div>

    <?php include('footer.php'); ?>
</body>
</html>