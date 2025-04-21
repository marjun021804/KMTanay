<?php
header('Content-Type: text/html; charset=UTF-8');
session_start();
include('cnn.php');
include('link.php');

$isLoggedIn = isset($_SESSION['customer_id']);
$username = $isLoggedIn && isset($_SESSION['full_name']) ? $_SESSION['full_name'] : null;

$product_details = null;
$error_message = '';
$product_name = '';
$available_sizes = []; // Initialize available sizes array
$size_prices = []; // Initialize size prices array


if (isset($_GET['product_name'])) {
    $product_name_url = trim($_GET['product_name']);
    $product_name = urldecode($product_name_url);

    // Modified SQL: Added 'category' column to fetch the category for size-based pricing
    $sql = "SELECT product_id, product_name, image, price, description, category, quantity, size
            FROM product_tb
            WHERE product_name = ? AND status = 0
            LIMIT 1";

    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("s", $product_name);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            $product_details = $result->fetch_assoc();
            error_log("[DEBUG] Product Details Fetched: " . print_r($product_details, true));

            if ($product_details && isset($product_details['size']) && !empty(trim($product_details['size']))) {
                $raw_sizes = explode(',', $product_details['size']);
                $available_sizes = array_filter(array_map('trim', $raw_sizes));

                if (!empty($product_details['category'])) {
                    $category_table = strtolower(preg_replace('/[^a-zA-Z0-9\- ]/', '', $product_details['category'])) . "_sizes";
                    $size_prices = [];

                    // Removed the display of the current selected <category>_sizes table name
                    // $category_table is still used internally for queries

                    $size_query = "SELECT size, price FROM `$category_table` WHERE size IN ('" . implode("','", $available_sizes) . "')";
                    $size_result = $conn->query($size_query);

                    if ($size_result && $size_result->num_rows > 0) {
                        while ($row = $size_result->fetch_assoc()) {
                            $size_prices[$row['size']] = $row['price'];
                        }
                    } else {
                        error_log("[DEBUG] No size prices found or table does not exist: $category_table");
                    }
                } else {
                    error_log("[DEBUG] Category is empty or not set for product: " . $product_name);
                }

                error_log("[DEBUG] Sizes and Prices: " . print_r($size_prices, true));
            } else {
                error_log("[DEBUG] 'size' column is empty or not set for product: " . $product_name);
            }
        } else {
            $error_message = "Product not found or is unavailable.";
            error_log("[DEBUG] Product query failed or returned no rows for product name: " . $product_name);
        }
        $stmt->close();
    } else {
        $error_message = "Error preparing product query: " . $conn->error;
        error_log($error_message);
    }
} else {
    $error_message = "No product specified.";
    error_log("[DEBUG] Product name not found in GET parameters.");
}

if ($product_details) {
    $product_id = $product_details['product_id'];
    $discount_details = null;

    // Fetch discount details for the product
    $discountQuery = "SELECT minimum_quantity, discount, date_range, is_bulk FROM discount WHERE product_id = ?";
    $discountStmt = $conn->prepare($discountQuery);
    if ($discountStmt) {
        $discountStmt->bind_param("i", $product_id);
        $discountStmt->execute();
        $discountResult = $discountStmt->get_result();
        if ($discountResult && $discountResult->num_rows > 0) {
            $discount_details = $discountResult->fetch_assoc();
        }
        $discountStmt->close();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'buy_now') {
        if (!$isLoggedIn) {
            // Redirect to login page if user is not logged in
            header("Location: login.php");
            exit;
        } else {
            // Redirect to checkout.php with product details for logged-in users
            $_SESSION['buy_now_item'] = [
                'product_id' => $_POST['product_id'] ?? null,
                'product_name' => $_POST['product_name'] ?? null,
                'image' => $_POST['image'] ?? null,
                'price' => $_POST['real_price'] ?? 0,
                'quantity' => $_POST['quantity'] ?? 1,
                'discount' => $_POST['discount'] ?? 0,
                'total_price' => ($_POST['real_price'] ?? 0) * ($_POST['quantity'] ?? 1) - ($_POST['discount'] ?? 0),
                'size' => $_POST['selected_size'] ?? null,
            ];
            header("Location: checkout.php");
            exit;
        }
    }

    if ($_POST['action'] === 'add_cart') {
        $product_id = $_POST['product_id'] ?? null;
        $product_name = $_POST['product_name'] ?? null;
        $image = $_POST['image'] ?? null;
        $price = $_POST['real_price'] ?? 0;
        $quantity = $_POST['quantity'] ?? 1;
        $size = $_POST['selected_size'] ?? null;

        // Calculate discounted price if applicable
        if ($discount_details && isset($discount_details['is_bulk']) && $discount_details['is_bulk'] === '0') {
            $discount_percentage = (float) $discount_details['discount'];
            $price = round($price - ($price * ($discount_percentage / 100)), 2); // Apply discount
        }

        $total_price = $price * $quantity;

        if ($isLoggedIn) {
            $customer_id = $_SESSION['customer_id'];

            // Check if the product with the same name and size already exists in the cart
            $check_sql = "SELECT cart_id, quantity FROM cart_tb WHERE customer_id = ? AND product_name = ? AND size = ?";
            $check_stmt = $conn->prepare($check_sql);
            if ($check_stmt) {
                $check_stmt->bind_param("iss", $customer_id, $product_name, $size);
                $check_stmt->execute();
                $check_result = $check_stmt->get_result();

                if ($check_result->num_rows > 0) {
                    // Update the quantity if the product already exists
                    $existing_row = $check_result->fetch_assoc();
                    $new_quantity = $existing_row['quantity'] + $quantity;
                    $new_total_price = $price * $new_quantity;

                    $update_sql = "UPDATE cart_tb SET quantity = ?, total_price = ? WHERE cart_id = ?";
                    $update_stmt = $conn->prepare($update_sql);
                    if ($update_stmt) {
                        $update_stmt->bind_param("idi", $new_quantity, $new_total_price, $existing_row['cart_id']);
                        $update_stmt->execute();
                        $update_stmt->close();
                    }
                } else {
                    // Insert a new row if the product does not exist
                    $insert_sql = "INSERT INTO cart_tb (customer_id, product_id, image, product_name, price, quantity, total_price, size)
                                   VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                    $insert_stmt = $conn->prepare($insert_sql);
                    if ($insert_stmt) {
                        $insert_stmt->bind_param(
                            "iissdids",
                            $customer_id,
                            $product_id,
                            $image,
                            $product_name,
                            $price,
                            $quantity,
                            $total_price,
                            $size
                        );
                        $insert_stmt->execute();
                        $insert_stmt->close();
                    }
                }
                $check_stmt->close();
            }
        } else {
            // Ensure $_SESSION['guest_cart'] is always an array
            if (!isset($_SESSION['guest_cart']) || !is_array($_SESSION['guest_cart'])) {
                $_SESSION['guest_cart'] = [];
            }

            $cart_item = [
                'product_id' => $product_id,
                'product_name' => $product_name,
                'image' => $image,
                'price' => $price,
                'quantity' => $quantity,
                'total_price' => $total_price,
                'size' => $size,
            ];

            // Check if the product with the same name and size already exists in the session cart
            $found = false;
            foreach ($_SESSION['guest_cart'] as &$item) {
                if ($item['product_id'] === $product_id && $item['size'] === $size) {
                    $item['quantity'] += $quantity;
                    $item['total_price'] = $item['price'] * $item['quantity'];
                    $found = true;
                    break;
                }
            }

            if (!$found) {
                $_SESSION['guest_cart'][] = $cart_item;
            }

            // Persist the updated cart in the session
            $_SESSION['guest_cart'] = array_values($_SESSION['guest_cart']);
        }

        // Ensure session data is saved before redirecting
        session_write_close(); // Save session data to disk
        header("Location: cart.php");
        exit;
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $product_details ? htmlspecialchars($product_details['product_name']) : 'Product Detail'; ?> - KM
        Tanay</title>
    <link rel="stylesheet" href="style.css">
    <?php include('link.php'); ?>
    <style>
        .product-detail-container {
            display: flex;
            flex-wrap: wrap;
            max-width: 1100px;
            margin: 30px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            gap: 30px;
        }

        .product-image-section {
            flex: 1 1 40%;
            min-width: 280px;
            text-align: center;
            align-self: flex-start;
        }

        .product-image-section img {
            max-width: 100%;
            max-height: 500px;
            height: auto;
            border-radius: 8px;
            border: 1px solid #eee;
        }

        .product-info-section {
            flex: 1 1 55%;
            min-width: 300px;
        }

        .product-info-section h2 {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 10px;
            color: #333;
        }

        .rating-sold {
            font-size: 14px;
            color: #666;
            margin-bottom: 15px;
        }

        .rating-sold .stars {
            color: #ffc107;
        }

        .price {
            font-size: 28px;
            font-weight: bold;
            color: #e75480;
            margin-bottom: 20px;
        }

        .shipping-info {
            font-size: 14px;
            color: #555;
            margin-bottom: 20px;
            line-height: 1.6;
            border-top: 1px solid #eee;
            padding-top: 15px;
        }

        .shipping-info span {
            display: block;
            margin-bottom: 5px;
        }

        .shipping-info i {
            margin-right: 8px;
            color: #f8a5c2;
        }

        .variations {
            margin-bottom: 20px;
        }

        .variation-group {
            margin-bottom: 15px;
        }

        .variation-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 15px;
            color: #444;
        }

        .variation-group input[type="file"] {
            display: block;
            margin-top: 5px;
            font-size: 14px;
            color: #555;
        }

        .variation-group input[type="file"]::file-selector-button {
            background-color: #fff;
            border: 1px solid #ccc;
            padding: 6px 10px;
            margin-right: 10px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.2s, border-color 0.2s;
            font-size: 13px;
        }

        .variation-group input[type="file"]::file-selector-button:hover {
            border-color: #f8a5c2;
            background-color: #fff0f5;
        }

        .variation-group button,
        .size-chart-link {
            background-color: #fff;
            border: 1px solid #ccc;
            padding: 8px 12px;
            margin-right: 8px;
            margin-bottom: 8px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.2s, border-color 0.2s;
            font-size: 14px;
        }

        .variation-group button:hover:not(.active),
        .variation-group button:focus {
            border-color: #f09dbb;
        }

        .variation-group button.active {
            border-color: #e75480;
            background-color: #fff0f5;
            box-shadow: 0 0 0 1px #e75480;
        }

        .variation-group button[disabled] {
            cursor: not-allowed;
            opacity: 0.6;
            background-color: #f8f8f8;
        }

        .size-chart-link {
            display: inline-block;
            margin-left: 10px;
            color: #007bff;
            text-decoration: none;
            font-size: 14px;
            border: none;
            background: none;
            padding: 0;
        }

        .size-chart-link:hover {
            text-decoration: underline;
        }

        .quantity-selector {
            display: flex;
            align-items: center;
            margin-bottom: 25px;
        }

        .quantity-selector label {
            font-weight: 600;
            margin-right: 15px;
            font-size: 15px;
            color: #444;
        }

        .quantity-selector button {
            background-color: #eee;
            border: 1px solid #ccc;
            width: 30px;
            height: 30px;
            cursor: pointer;
            font-size: 18px;
            line-height: 1;
        }

        .quantity-selector input {
            width: 40px;
            text-align: center;
            border: 1px solid #ccc;
            height: 30px;
            margin: 0 5px;
            -moz-appearance: textfield;
        }

        .quantity-selector input::-webkit-outer-spin-button,
        .quantity-selector input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        .availability {
            font-size: 13px;
            color: #777;
            margin-left: 15px;
        }

        .action-buttons {
            display: flex;
            gap: 15px;
            margin-bottom: 25px;
            flex-wrap: wrap;
        }

        .action-buttons button {
            padding: 12px 20px;
            border-radius: 5px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            flex-grow: 1;
            transition: background-color 0.2s;
            min-width: 120px;
        }

        .action-buttons button:disabled {
            cursor: not-allowed;
            opacity: 0.6;
            background-color: #ccc !important;
            border-color: #ccc !important;
            color: #666 !important;
        }

        .btn-add-cart {
            background-color: #fff0f5;
            border: 1px solid #f8a5c2;
            color: #e75480;
        }

        .btn-add-cart:hover:not(:disabled) {
            background-color: #fde8ef;
        }

        .btn-buy-now {
            background-color: #f8a5c2;
            border: 1px solid #f8a5c2;
            color: white;
        }

        .btn-buy-now:hover:not(:disabled) {
            background-color: #e56b92;
        }

        .discount-note {
            font-size: 12px;
            color: #888;
            margin-top: 10px;
        }

        .description {
            margin-top: 30px;
            border-top: 1px solid #eee;
            padding-top: 20px;
            line-height: 1.7;
            color: #444;
        }

        .description h4 {
            font-weight: 600;
            margin-bottom: 10px;
            color: #333;
        }

        #error-message-display {
            color: red;
            font-size: 14px;
            margin-top: 15px;
            display: none;
        }

        @media (max-width: 768px) {
            body {
                padding-top: 60px;
            }

            .product-detail-container {
                flex-direction: column;
                margin-top: 20px;
                padding: 15px;
                gap: 20px;
            }

            .product-image-section,
            .product-info-section {
                flex: 1 1 100%;
                min-width: unset;
            }

            .price {
                font-size: 24px;
            }

            .action-buttons {
                flex-direction: column;
            }

            .action-buttons button {
                width: 100%;
            }
        }

        @media (max-width: 480px) {
            .product-info-section h2 {
                font-size: 20px;
            }

            .price {
                font-size: 22px;
            }

            .variation-group button {
                font-size: 13px;
                padding: 6px 10px;
            }

            .action-buttons button {
                font-size: 15px;
                padding: 10px 15px;
            }
        }
    </style>
</head>

<body>

    <?php
    if (isset($_SESSION['customer_id'])) {
        include('menu.php'); // Logged-in menu
    } else {
        include('indexMenu.php'); // Guest menu
    }
    ?>

    <div class="product-detail-container">
        <?php if ($product_details):
            $stock_quantity = isset($product_details['quantity']) ? (int) $product_details['quantity'] : 0;
            $is_out_of_stock = ($stock_quantity <= 0);
            ?>
            <div class="product-image-section">
                <img src="<?php echo htmlspecialchars("/KMTanayAdmin/" . $product_details['image']); ?>"
                    alt="<?php echo htmlspecialchars($product_details['product_name']); ?>">
            </div>

            <div class="product-info-section">
                <form id="product-form" action="" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="product_id"
                        value="<?php echo htmlspecialchars($product_details['product_id'] ?? ''); ?>">
                    <input type="hidden" name="product_name"
                        value="<?php echo htmlspecialchars($product_details['product_name']); ?>">
                    <input type="hidden" name="image" value="<?php echo htmlspecialchars($product_details['image']); ?>"> <!-- Pass image -->
                    <input type="hidden" id="selected_size" name="selected_size" value="">
                    <input type="hidden" id="form_quantity" name="quantity" value="1">
                    <input type="hidden" id="real_price" name="real_price" value="<?php echo htmlspecialchars($product_details['price']); ?>"> <!-- Pass price -->
                    <input type="hidden" name="discount" value="0"> <!-- Default discount -->
                    <input type="hidden" name="action" value="add_cart">

                    <h2><?php echo htmlspecialchars($product_details['product_name']); ?></h2>
                    <?php if (!empty($product_details['description'])): ?>
                        <div class="description">
                            <p><?php echo nl2br(htmlspecialchars($product_details['description'])); ?></p>
                        </div>
                    <?php endif; ?>
                   

                    <?php if ($discount_details && isset($discount_details['is_bulk'])): ?>
                        <?php 
                            $is_bulk = $discount_details['is_bulk']; 
                            $original_price = (float) $product_details['price'];
                            $discount_percentage = (float) $discount_details['discount'];
                            $discounted_price = $original_price - ($original_price * ($discount_percentage / 100));
                        ?>
                        <?php if ($is_bulk === '0'): ?>
                            <div class="price" id="price-display">
                                <span style="text-decoration: line-through; color: #888;">₱<?php echo number_format($original_price, 2, '.', ','); ?></span>
                                <span style="color: #e75480; font-weight: bold;">₱<?php echo number_format(round($discounted_price), 0, '.', ','); ?></span>
                            </div>
                        <?php else: ?>
                            <div class="price" id="price-display">₱<?php echo number_format($original_price, 2, '.', ','); ?></div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="price" id="price-display">₱<?php echo number_format((float) $product_details['price'], 2, '.', ','); ?></div>
                    <?php endif; ?>

                    <div id="discount-display" style="font-size: 14px; color: #888; margin-top: 10px;"></div>

                    <?php if ($discount_details && isset($discount_details['is_bulk']) && $discount_details['is_bulk'] === '0'): ?>
                        <p class="discount-note">
                            *Enjoy a <?php echo htmlspecialchars($discount_details['discount']); ?>% discount for this product until 
                            <?php echo htmlspecialchars($discount_details['date_range']); ?> at 11:59 PM.
                        </p>
                    <?php else: ?>
                        <?php error_log("[DEBUG] Discount note not displayed. is_bulk: " . ($discount_details['is_bulk'] ?? 'undefined')); ?>
                    <?php endif; ?>



                    <div class="shipping-info">
                        <span><i class="fas fa-undo-alt"></i> Free & Easy Returns</span>
                        <span><i class="fas fa-box-open"></i> Pre-Order (Ships in 2 days)</span>
                        <span><i class="fas fa-truck"></i> Shipping Fee P40</span>
                    </div>

                  
                    <div class="variations">
                        <div class="variation-group" data-variation-type="size">
                            <label>Available Sizes:</label>
                            <?php if (!empty($available_sizes)): ?>
                                <?php foreach ($available_sizes as $size): ?>
                                    <button type="button"
                                        data-value="<?php echo htmlspecialchars($size); ?>"><?php echo htmlspecialchars($size); ?></button>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p style="font-size: 14px; color: #777;">Size not applicable for this product.</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="quantity-selector">
                        <label for="quantity_visible">Quantity:</label>
                        <button type="button" onclick="changeQuantity(-1)" <?php if ($is_out_of_stock)
                            echo 'disabled'; ?>>-</button>
                        <input type="number" id="quantity_visible" value="1" min="1" max="<?php echo $stock_quantity; ?>"
                            <?php if ($is_out_of_stock)
                                echo 'disabled'; ?>>
                        <button type="button" onclick="changeQuantity(1)" <?php if ($is_out_of_stock)
                            echo 'disabled'; ?>>+</button>
                        <span class="availability">
                            <?php echo $stock_quantity; ?> pieces available
                            <?php if ($is_out_of_stock)
                                echo '<strong style="color:red;"> (Out of Stock)</strong>'; ?>
                        </span>
                    </div>

                    <?php if ($discount_details && isset($discount_details['is_bulk']) && $discount_details['is_bulk'] === '1'): ?>
                        <p class="discount-note">
                            *Order at least <?php echo htmlspecialchars($discount_details['minimum_quantity']); ?> pieces to get a 
                            <?php echo htmlspecialchars($discount_details['discount']); ?>% discount on your total purchase until <?php echo htmlspecialchars($discount_details['date_range']); ?> at 11:59 PM.
                        </p>
                    <?php endif; ?>

                    <div class="action-buttons">
                        <button type="submit" name="action" value="add_cart" class="btn-add-cart" <?php if ($is_out_of_stock)
                            echo 'disabled'; ?>>
                            <i class="fas fa-cart-plus"></i> Add To Cart
                        </button>
                        <button type="submit" name="action" value="buy_now" class="btn-buy-now" <?php if ($is_out_of_stock)
                            echo 'disabled'; ?>>
                            Buy Now
                        </button>
                    </div>
                    <div id="error-message-display"></div>

                </form>



            </div>

        <?php elseif ($error_message): ?>
            <p style="text-align: center; color: red; width: 100%; padding: 40px;">
                <?php echo htmlspecialchars($error_message); ?>
            </p>
            <p style="text-align: center; width: 100%;"><a href="homepage.php">Go back to homepage</a></p>
        <?php else: ?>
            <p style="text-align: center; color: red; width: 100%; padding: 40px;">An unexpected error occurred fetching
                product details.</p>
            <p style="text-align: center; width: 100%;"><a href="homepage.php">Go back to homepage</a></p>
        <?php endif; ?>

    </div>

    <?php
    include('footer.php');
    if (isset($conn) && $conn instanceof mysqli && $conn->thread_id) {
        $conn->close();
    }
    ?>

    <script>
        function changeQuantity(amount) {
            const quantityInput = document.getElementById('quantity_visible');
            const quantityHiddenInput = document.getElementById('form_quantity');
            let currentValue = parseInt(quantityInput.value, 10);
            if (isNaN(currentValue)) currentValue = 1;

            let newValue = currentValue + amount;
            const minVal = parseInt(quantityInput.min) || 1;
            const maxVal = parseInt(quantityInput.max);
            const stockAvailable = isNaN(maxVal) ? Infinity : maxVal;

            if (newValue < minVal) newValue = minVal;
            if (!isNaN(stockAvailable) && stockAvailable >= 0 && newValue > stockAvailable) {
                newValue = stockAvailable;
            }

            quantityInput.value = newValue;
            if (quantityHiddenInput) {
                quantityHiddenInput.value = newValue;
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            const quantityInput = document.getElementById('quantity_visible');
            const quantityHiddenInput = document.getElementById('form_quantity');
            const stock = quantityInput ? parseInt(quantityInput.max) : NaN; // Check if quantityInput exists
            const isOutOfStock = !isNaN(stock) && stock <= 0;

            if (quantityInput) { // Ensure input exists before accessing properties
                if (isOutOfStock) {
                    quantityInput.value = 0;
                    if (quantityHiddenInput) quantityHiddenInput.value = 0;
                } else if (quantityHiddenInput) {
                    let initialVal = parseInt(quantityInput.value, 10);
                    const minVal = parseInt(quantityInput.min) || 1;
                    if (isNaN(initialVal) || initialVal < minVal) initialVal = minVal;
                    quantityHiddenInput.value = initialVal;
                    quantityInput.value = initialVal;
                }


                if (quantityInput && quantityHiddenInput) {
                    quantityInput.addEventListener('input', function () {
                        if (quantityHiddenInput) {
                            let val = parseInt(this.value, 10);
                            const minVal = parseInt(this.min) || 1;
                            const maxVal = parseInt(this.max);
                            const stockAvailable = isNaN(maxVal) ? Infinity : maxVal;

                            if (isNaN(val) || val < minVal) {
                                val = minVal;
                            }
                            if (!isNaN(stockAvailable) && stockAvailable >= 0 && val > stockAvailable) {
                                val = stockAvailable;
                            }
                            quantityHiddenInput.value = val;
                        }
                    });

                    quantityInput.addEventListener('change', function () {
                        let val = parseInt(this.value, 10);
                        const minVal = parseInt(this.min) || 1;
                        const maxVal = parseInt(this.max);
                        const stockAvailable = isNaN(maxVal) ? Infinity : maxVal;

                        if (isNaN(val) || val < minVal) { val = minVal; }
                        if (!isNaN(stockAvailable) && stockAvailable >= 0 && val > stockAvailable) { val = stockAvailable; }
                        this.value = val;
                        if (document.getElementById('form_quantity')) document.getElementById('form_quantity').value = val;
                    });
                }
            }


            document.querySelectorAll('.variations .variation-group').forEach(group => {
                const groupType = group.getAttribute('data-variation-type');
                let hiddenInputId = '';

                if (groupType === 'size') {
                    hiddenInputId = 'selected_size';
                }

                if (hiddenInputId) {
                    const hiddenInput = document.getElementById(hiddenInputId);
                    if (hiddenInput) {
                        group.querySelectorAll('button').forEach(button => {
                            button.addEventListener('click', function () {
                                group.querySelectorAll('button').forEach(btn => btn.classList.remove('active'));
                                this.classList.add('active');
                                hiddenInput.value = this.getAttribute('data-value');
                            });
                        });
                    }
                }
            });

            const productForm = document.getElementById('product-form');
            const errorMessageDiv = document.getElementById('error-message-display');
            if (productForm) {
                productForm.addEventListener('submit', function (event) {
                    let errors = [];
                    errorMessageDiv.style.display = 'none';
                    errorMessageDiv.innerHTML = '';

                    const sizeInput = document.getElementById('selected_size');
                    const sizeButtons = document.querySelectorAll('[data-variation-type="size"] button');
                    if (sizeButtons.length > 0 && (!sizeInput || sizeInput.value === '')) {
                        errors.push("Please select a size.");
                    }


                    const quantityInput = document.getElementById('quantity_visible');
                    if (quantityInput) {
                        const stock = parseInt(quantityInput.max);
                        const selectedQuantity = parseInt(quantityInput.value);

                        if (isNaN(selectedQuantity) || selectedQuantity < 1) {
                            errors.push("Quantity must be at least 1.");
                        }
                        if (!isNaN(stock) && stock >= 0 && selectedQuantity > stock) {
                            errors.push("Selected quantity exceeds available stock (" + stock + ").");
                        }
                        if (!isNaN(stock) && stock <= 0 && selectedQuantity > 0) {
                            errors.push("This item is currently out of stock.");
                        }
                    } else {
                        errors.push("Quantity input not found.");
                    }


                    if (errors.length > 0) {
                        event.preventDefault();
                        errorMessageDiv.innerHTML = errors.join('<br>');
                        errorMessageDiv.style.display = 'block';
                        window.scrollTo(0, errorMessageDiv.offsetTop - 100);
                    }
                });
            }


            const sizeButtons = document.querySelectorAll('[data-variation-type="size"] button');
            const priceDisplay = document.getElementById('price-display');
            const sizePrices = <?php echo json_encode($size_prices ?? []); ?>;
            const realPriceInput = document.getElementById('real_price');

            sizeButtons.forEach(button => {
                button.addEventListener('click', function () {
                    const selectedSize = this.getAttribute('data-value');
                    if (sizePrices[selectedSize]) {
                        const newPrice = parseFloat(sizePrices[selectedSize]);
                        priceDisplay.textContent = `₱${newPrice.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
                        realPriceInput.value = newPrice; // Update real_price
                    }
                });
            });

        });

    </script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const sizeButtons = document.querySelectorAll('.size-button');
        const sizeInput = document.getElementById('selected_size');
        const quantityInput = document.getElementById('quantity_visible');
        const addToCartBtn = document.getElementById('add-to-cart-btn');
        const buyNowBtn = document.getElementById('buy-now-btn');
        const stock = parseInt(quantityInput?.max) || 0;

        function validateForm() {
            let isValid = true;

            // Check if size is selected (if applicable)
            if (sizeButtons.length > 0 && (!sizeInput || sizeInput.value === '')) {
                isValid = false;
            }

            // Check if quantity is valid
            const quantity = parseInt(quantityInput?.value) || 0;
            if (quantity < 1 || quantity > stock) {
                isValid = false;
            }

            // Enable or disable buttons based on validation
            addToCartBtn.disabled = !isValid;
            buyNowBtn.disabled = !isValid;
        }

        // Automatically select the leftmost size button on the first load
        if (sizeButtons.length > 0) {
            const firstSizeButton = sizeButtons[0];
            firstSizeButton.classList.add('active');
            sizeInput.value = firstSizeButton.getAttribute('data-value');
            validateForm(); // Ensure the form is validated after selecting the first size
        }

        // Add event listeners to size buttons
        sizeButtons.forEach(button => {
            button.addEventListener('click', function () {
                sizeButtons.forEach(btn => btn.classList.remove('active'));
                this.classList.add('active');
                sizeInput.value = this.getAttribute('data-value');
                validateForm();
            });
        });

        // Add event listeners to quantity input
        quantityInput.addEventListener('input', validateForm);
        quantityInput.addEventListener('change', validateForm);

        // Initial validation
        validateForm();
    });
</script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const sizeButtons = document.querySelectorAll('.size-button');
        const sizeInput = document.getElementById('selected_size');
        const quantityInput = document.getElementById('quantity_visible');
        const addToCartBtn = document.getElementById('add-to-cart-btn');
        const buyNowBtn = document.getElementById('buy-now-btn');
        const stock = parseInt(quantityInput?.max) || 0;

        function validateForm() {
            let isValid = true;

            // Check if size is selected (if applicable)
            if (sizeButtons.length > 0 && (!sizeInput || sizeInput.value === '')) {
                isValid = false;
            }

            // Check if quantity is valid
            const quantity = parseInt(quantityInput?.value) || 0;
            if (quantity < 1 || quantity > stock) {
                isValid = false;
            }

            // Enable or disable buttons based on validation
            addToCartBtn.disabled = !isValid;
            buyNowBtn.disabled = !isValid;
        }

        // Automatically select the leftmost size button on the first load
        if (sizeButtons.length > 0) {
            const firstSizeButton = sizeButtons[0];
            firstSizeButton.classList.add('active');
            sizeInput.value = firstSizeButton.getAttribute('data-value');
            validateForm(); // Ensure the form is validated after selecting the first size
        }

        // Add event listeners to size buttons
        sizeButtons.forEach(button => {
            button.addEventListener('click', function () {
                sizeButtons.forEach(btn => btn.classList.remove('active'));
                this.classList.add('active');
                sizeInput.value = this.getAttribute('data-value');
                validateForm();
            });
        });

        // Add event listeners to quantity input
        quantityInput.addEventListener('input', validateForm);
        quantityInput.addEventListener('change', validateForm);

        // Add event listener to the form submission
        const productForm = document.getElementById('product-form');
        if (productForm) {
            productForm.addEventListener('submit', function (event) {
                if (sizeButtons.length > 0 && (!sizeInput || sizeInput.value === '')) {
                    event.preventDefault();
                    alert('Please select size');
                }
            });
        }

        // Initial validation
        validateForm();
    });
</script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const sizeButtons = document.querySelectorAll('[data-variation-type="size"] button');
        const priceDisplay = document.getElementById('price-display');
        const discountDisplay = document.getElementById('discount-display');
        const sizePrices = <?php echo json_encode($size_prices ?? []); ?>;
        const realPriceInput = document.getElementById('real_price');
        const discountDetails = <?php echo json_encode($discount_details ?? null); ?>;

        sizeButtons.forEach(button => {
            button.addEventListener('click', function () {
                const selectedSize = this.getAttribute('data-value');
                if (sizePrices[selectedSize]) {
                    const newPrice = parseFloat(sizePrices[selectedSize]);
                    realPriceInput.value = newPrice; // Update real_price

                    if (discountDetails && discountDetails.is_bulk === '0') {
                        const discountPercentage = parseFloat(discountDetails.discount);
                        const discountedPrice = Math.round(newPrice - (newPrice * (discountPercentage / 100))); // Round off
                        priceDisplay.innerHTML = `
                            <span style="text-decoration: line-through; color: #888;">₱${newPrice.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</span>
                            <span style="color: #e75480; font-weight: bold;">₱${discountedPrice.toLocaleString('en-US')}</span>
                        `;
                    } else {
                        priceDisplay.textContent = `₱${newPrice.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
                    }
                }
            });
        });
    });
</script>

</body>

</html>