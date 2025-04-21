<?php
ob_start(); // Start output buffering
session_start();

// Clear guest cart if the session has expired
if (!isset($_SESSION['customer_id']) && isset($_SESSION['guest_cart'])) {
    if (!isset($_SESSION['last_activity']) || (time() - $_SESSION['last_activity'] > 1800)) { // 30 minutes timeout
        unset($_SESSION['guest_cart']); // Clear the guest cart
    }
    $_SESSION['last_activity'] = time(); // Update last activity timestamp
}

if (isset($_SESSION['customer_id'])) {
    include('menu.php'); // Include the menu for logged-in users
    $customer_id = $_SESSION['customer_id'];
} else {
    include('indexMenu.php'); // Include the menu for guests
    $customer_id = "guest"; // Set customer_id to "guest" for non-logged-in users
}

include('cnn.php'); // Include the database connection

$cart_items = [];
$total_price = 0;

if ($customer_id !== "guest") {
    $sql = "SELECT c.cart_id, c.product_id, c.product_name, c.quantity, c.discount, c.total_price, c.size, c.price, p.image 
            FROM cart_tb c 
            JOIN product_tb p ON c.product_id = p.product_id 
            WHERE c.customer_id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("i", $customer_id);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            // Check for discounts in the discount table
            $discountQuery = "SELECT minimum_quantity, discount, is_bulk FROM discount WHERE product_id = ?";
            $discountStmt = $conn->prepare($discountQuery);
            if ($discountStmt) {
                $discountStmt->bind_param("i", $row['product_id']);
                $discountStmt->execute();
                $discountResult = $discountStmt->get_result();
                if ($discountResult && $discountRow = $discountResult->fetch_assoc()) {
                    if ($discountRow['is_bulk'] === 1 && $row['quantity'] >= $discountRow['minimum_quantity']) {
                        // Apply the discount if is_bulk = 1 and minimum quantity is met
                        $row['discount'] = $discountRow['discount'];
                    } else {
                        // Reset the discount if conditions are not met
                        $row['discount'] = 0;
                    }
                    $row['total_price'] = ($row['price'] * $row['quantity']) - ($row['price'] * $row['quantity'] * ($row['discount'] / 100));
                    
                    // Update the database
                    $updateCartQuery = "UPDATE cart_tb SET discount = ?, total_price = ? WHERE cart_id = ?";
                    $updateCartStmt = $conn->prepare($updateCartQuery);
                    if ($updateCartStmt) {
                        $updateCartStmt->bind_param("dii", $row['discount'], $row['total_price'], $row['cart_id']);
                        $updateCartStmt->execute();
                        $updateCartStmt->close();
                    }
                }
                $discountStmt->close();
            }
            $cart_items[] = $row;
            $total_price += $row['total_price'];
        }
        $stmt->close();
    } else {
        echo "Error: Failed to fetch cart items. " . $conn->error;
    }
} else {
    // Use session-based cart for guests
    if (!isset($_SESSION['guest_cart']) || !is_array($_SESSION['guest_cart'])) {
        $_SESSION['guest_cart'] = []; // Ensure it's always an array
    }
    foreach ($_SESSION['guest_cart'] as &$item) {
        // Check for discounts in the discount table
        $discountQuery = "SELECT minimum_quantity, discount, is_bulk FROM discount WHERE product_id = ?";
        $discountStmt = $conn->prepare($discountQuery);
        if ($discountStmt) {
            $discountStmt->bind_param("i", $item['product_id']);
            $discountStmt->execute();
            $discountResult = $discountStmt->get_result();
            if ($discountResult && $discountRow = $discountResult->fetch_assoc()) {
                if ($discountRow['is_bulk'] === 1 && $item['quantity'] >= $discountRow['minimum_quantity']) {
                    // Apply the discount if is_bulk = 1 and minimum quantity is met
                    $item['discount'] = $discountRow['discount'];
                } else {
                    // Reset the discount if conditions are not met
                    $item['discount'] = 0;
                }
                $item['total_price'] = ($item['price'] * $item['quantity']) - ($item['price'] * $item['quantity'] * ($item['discount'] / 100));
            }
            $discountStmt->close();
        }
        $cart_items[] = $item;
        $total_price += $item['total_price'];
    }
}

// Handle quantity update for guests and logged-in users
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update_quantity') {
        $cart_index = $_POST['cart_index'] ?? null;
        $new_quantity = (int) ($_POST['quantity'] ?? 1); // Ensure quantity is an integer

        if ($customer_id === "guest" && isset($_SESSION['guest_cart'][$cart_index])) {
            // Update guest cart
            $_SESSION['guest_cart'][$cart_index]['quantity'] = $new_quantity;
            $_SESSION['guest_cart'][$cart_index]['total_price'] = ($_SESSION['guest_cart'][$cart_index]['price'] * $new_quantity) - $_SESSION['guest_cart'][$cart_index]['discount'];
        } elseif ($customer_id !== "guest") {
            // Update database for logged-in users
            $cart_id = $_POST['cart_id'] ?? null;
            if ($cart_id) {
                $sql = "UPDATE cart_tb SET quantity = ?, total_price = (price * ? - discount) WHERE cart_id = ? AND customer_id = ?";
                $stmt = $conn->prepare($sql);
                if ($stmt) {
                    $stmt->bind_param("iiii", $new_quantity, $new_quantity, $cart_id, $customer_id);
                    $stmt->execute();
                    $stmt->close();
                }
            }
        }
    } elseif ($_POST['action'] === 'delete_item') {
        $cart_index = $_POST['cart_index'] ?? null;

        if ($customer_id === "guest" && isset($_SESSION['guest_cart'][$cart_index])) {
            // Delete item from guest cart
            unset($_SESSION['guest_cart'][$cart_index]);
            $_SESSION['guest_cart'] = array_values($_SESSION['guest_cart']); // Reindex the array
        } elseif ($customer_id !== "guest") {
            // Delete item from database for logged-in users
            $cart_id = $_POST['cart_id'] ?? null;
            if ($cart_id) {
                $sql = "DELETE FROM cart_tb WHERE cart_id = ? AND customer_id = ?";
                $stmt = $conn->prepare($sql);
                if ($stmt) {
                    $stmt->bind_param("ii", $cart_id, $customer_id);
                    $stmt->execute();
                    $stmt->close();
                }
            }
        }
    } elseif ($_POST['action'] === 'checkout') {
        if ($customer_id === "guest") {
            echo "<script>alert('Please log in to proceed to checkout.'); window.location.href = 'login.php';</script>";
            exit;
        }

        $selected_items = json_decode($_POST['selected_items'] ?? '[]', true);
        if (empty($selected_items)) {
            echo "<script>alert('Please select at least one item to proceed to checkout.'); window.location.href = 'cart.php';</script>";
            exit;
        }

        // Store selected cart item IDs in the session
        $_SESSION['selected_cart_items'] = $selected_items;

        // Redirect logged-in users to checkout.php
        header("Location: checkout.php");
        exit;
    }

    header("Location: cart.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - KM Tanay</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .cart-container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        .cart-header,
        .cart-item {
            display: grid;
            grid-template-columns: 5% 25% 15% 10% 10% 15% 15%;
            align-items: center;
            gap: 10px;
            text-align: center;
        }
        .cart-header {
            background: #f8a5c2;
            padding: 15px;
            border-radius: 10px;
            color: white;
            font-weight: bold;
        }
        .cart-item {
            background: #ffffff;
            padding: 15px;
            margin: 10px 0;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }
        .cart-item img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 5px;
            border: 1px solid #ddd;
        }
        .cart-item .quantity-container {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        .cart-item input[type="number"] {
            width: 60px;
            text-align: center;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 5px;
        }
        .cart-item button {
            background: #ff4d80;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }
        .cart-item button:hover {
            background: #ff3366;
        }
        .delete-btn {
            background: #ff4d80;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            display: block;
            margin: 0 auto; /* Center the button */
        }
        .delete-btn:hover {
            background: #ff3366;
        }
        .confirm-btn {
            background: #4CAF50;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }
        .confirm-btn:hover {
            background: #45a049;
        }
        .reload-btn {
            background: #4CAF50;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }
        .reload-btn:hover {
            background: #45a049;
        }
        .cart-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 20px;
            padding: 15px;
            background: #f8a5c2;
            border-radius: 10px;
            font-weight: bold;
            flex-wrap: wrap; /* Allow wrapping for better alignment */
        }
        .cart-footer .left-section {
            flex: 1;
            text-align: left; /* Align "Select All" to the left */
        }
        .cart-footer .right-section {
            flex: 2;
            text-align: right; /* Align "Total" and "Check Out" to the right */
        }
        .checkout-btn {
            background: #ff6699;
            color: white;
            padding: 12px 20px;
            border: none;
            cursor: pointer;
            border-radius: 5px;
            font-size: 16px;
            margin-left: 20px; /* Add spacing between total and button */
        }
        .checkout-btn:hover {
            background: #ff4d80;
        }
        .empty-cart {
            text-align: center;
            font-size: 18px;
            color: #777;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="cart-container">
        <h2>Shopping Cart</h2>
        <div class="cart-header">
            <span>Select</span>
            <span>Product</span>
            <span>Price</span>
            <span>Quantity</span>
            <span>Bulk Discount</span>
            <span>Total Price</span>
            <span>Action</span>
        </div>

        <?php if (!empty($cart_items)): ?>
            <?php foreach ($cart_items as $index => $item): ?>
                <div class="cart-item">
                    <input type="checkbox" class="cart-item-checkbox" 
                        data-total-price="<?php echo (float) $item['total_price']; ?>" 
                        data-cart-id="<?php echo isset($item['cart_id']) ? $item['cart_id'] : $index; ?>">
                    <div>
                        <img src="<?php echo htmlspecialchars("/KMTanayAdmin/" . $item['image']); ?>" 
                             alt="<?php echo htmlspecialchars($item['product_name']); ?>">
                        <p><strong><?php echo htmlspecialchars($item['product_name']); ?></strong></p>
                        <p>Size: <?php echo htmlspecialchars($item['size']); ?></p>
                    </div>
                    <span>₱<?php echo number_format((float) $item['price'], 2); ?></span>
                    <div class="quantity-container">
                        <form method="POST" action="">
                            <input type="hidden" name="action" value="update_quantity">
                            <input type="hidden" name="cart_index" value="<?php echo $index; ?>">
                            <?php if ($customer_id !== "guest"): ?>
                                <input type="hidden" name="cart_id" value="<?php echo $item['cart_id']; ?>">
                            <?php endif; ?>
                            <input type="number" name="quantity" value="<?php echo htmlspecialchars($item['quantity']); ?>" min="1" onchange="this.form.submit()">
                        </form>
                    </div>
                    <span><?php echo $item['discount']; ?>%</span>
                    <span>₱<?php echo number_format((float) $item['total_price'], 2); ?></span>
                    <form method="POST" action="" style="display: inline;">
                        <input type="hidden" name="action" value="delete_item">
                        <input type="hidden" name="cart_index" value="<?php echo $index; ?>">
                        <?php if ($customer_id !== "guest"): ?>
                            <input type="hidden" name="cart_id" value="<?php echo $item['cart_id']; ?>">
                        <?php endif; ?>
                        <button type="submit" class="delete-btn">Delete</button>
                    </form>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="empty-cart">Your cart is empty.</p>
        <?php endif; ?>

        <div class="cart-footer">
            <div class="left-section">
                <input type="checkbox" id="select-all-checkbox">
                <label for="select-all-checkbox"><b>Select All</b></label>
            </div>
            <div class="right-section">
                <span>Total (<span id="selected-items-count">0</span> item<span id="item-plural">s</span>): <b id="total-price">₱0.00</b></span>
                <form method="POST" action="" style="display: inline;">
                    <input type="hidden" name="action" value="checkout">
                    <input type="hidden" id="selected-items" name="selected_items" value="">
                    <button type="submit" class="checkout-btn">Check Out</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function updateTotal() {
            let total = 0;
            let selectedCount = 0;
            const selectedItems = [];
            document.querySelectorAll('.cart-item-checkbox:checked').forEach(checkbox => {
                total += parseFloat(checkbox.getAttribute('data-total-price'));
                selectedCount++;
                selectedItems.push(checkbox.getAttribute('data-cart-id'));
            });
            document.getElementById('total-price').textContent = total.toLocaleString('en-US', { style: 'currency', currency: 'PHP' });
            document.getElementById('selected-items-count').textContent = selectedCount;
            document.getElementById('item-plural').style.display = selectedCount === 1 ? 'none' : 'inline';
            document.getElementById('selected-items').value = JSON.stringify(selectedItems);
        }

        document.addEventListener('DOMContentLoaded', () => {
            const checkboxes = document.querySelectorAll('.cart-item-checkbox');
            const selectAllCheckbox = document.getElementById('select-all-checkbox');

            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', () => {
                    updateTotal();
                    const allChecked = Array.from(checkboxes).every(cb => cb.checked);
                    selectAllCheckbox.checked = allChecked;
                    selectAllCheckbox.indeterminate = !allChecked && Array.from(checkboxes).some(cb => cb.checked);
                });
            });

            selectAllCheckbox.addEventListener('change', () => {
                checkboxes.forEach(checkbox => checkbox.checked = selectAllCheckbox.checked);
                updateTotal();
            });

            updateTotal();
        });

        function deleteItem(cartIndex) {
            if (!confirm("Are you sure you want to delete this item?")) return;

            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '';

            const actionInput = document.createElement('input');
            actionInput.type = 'hidden';
            actionInput.name = 'action';
            actionInput.value = 'delete_item';
            form.appendChild(actionInput);

            const indexInput = document.createElement('input');
            indexInput.type = 'hidden';
            indexInput.name = 'cart_index';
            indexInput.value = cartIndex;
            form.appendChild(indexInput);

            document.body.appendChild(form);
            form.submit();
        }
    </script>

    <?php include('footer.php'); // Include the footer ?>
    <?php ob_end_flush(); // Flush the output buffer ?>
</body>
</html>
