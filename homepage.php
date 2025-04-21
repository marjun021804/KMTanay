<?php
session_start();
include('cnn.php'); // Ensure this path is correct

// Check if user is logged in (using 'customer_id' which seems more reliable from login_process.php)
$isLoggedIn = isset($_SESSION['customer_id']);
$username = $isLoggedIn && isset($_SESSION['full_name']) ? $_SESSION['full_name'] : null; // Use full_name if available

// --- Start Banner Fetching Logic ---
// Fetch the left banner (position = 1)
$sql_banner_left = "SELECT banner FROM slide_banner WHERE position = 1 ORDER BY banner_id DESC";
$result_banner_left = $conn->query($sql_banner_left);

$left_banners = [];
if ($result_banner_left) {
    while ($row = $result_banner_left->fetch_assoc()) {
        // Construct the web-accessible path directly
        $web_path = "/KMTanayAdmin/image/banner/" . $row['banner'];
        // You might want a physical path check for debugging, but web path is needed for src
        $physical_path = $_SERVER['DOCUMENT_ROOT'] . $web_path;
        // For display, just add the web path. Add a check if you want to ensure file exists physically.
        // if (file_exists($physical_path)) {
        $left_banners[] = $web_path;
        // } else {
        // Optional: log error or skip if file doesn't exist physically
        // error_log("Banner image not found at physical path: " . $physical_path);
        // }
    }
} else {
    echo "Error fetching left banners: " . $conn->error; // Basic error handling
}


// Fetch right banner for position 2 (top one)
$sql_banner_top_right = "SELECT banner FROM slide_banner WHERE position = 2 ORDER BY banner_id DESC LIMIT 1";
$result_banner_top_right = $conn->query($sql_banner_top_right);
$top_right_banner_row = $result_banner_top_right ? $result_banner_top_right->fetch_assoc() : null;
$top_right_image = $top_right_banner_row ? "/KMTanayAdmin/image/banner/" . $top_right_banner_row['banner'] : "/KMTanayAdmin/image/default.png"; // Default image path

// Fetch right banner for position 3 (bottom one)
$sql_banner_bottom_right = "SELECT banner FROM slide_banner WHERE position = 3 ORDER BY banner_id DESC LIMIT 1";
$result_banner_bottom_right = $conn->query($sql_banner_bottom_right);

if ($result_banner_bottom_right && $result_banner_bottom_right->num_rows > 0) {
    $bottom_right_banner_row = $result_banner_bottom_right->fetch_assoc();
    $bottom_right_image = "/KMTanayAdmin/image/banner/" . $bottom_right_banner_row['banner'];
} else {
    $bottom_right_image = "/KMTanayAdmin/image/default.png"; // Default image path
}
// --- End Banner Fetching Logic ---


// --- Start Product Fetching Logic ---
$search_term = isset($_GET['search']) ? trim($_GET['search']) : null;
$selected_category = isset($_GET['category']) ? trim($_GET['category']) : null;

$sql = "";
$params = [];
$types = "";

// Build the base query
$base_sql = "SELECT product_name, image, price, product_id FROM product_tb";
$where_clauses = ["status = 0"]; // Always filter by status (assuming 0 means active)

if ($search_term) {
    $like_term = "%" . $search_term . "%";
    if ($selected_category && $selected_category !== '') {
        // Search within a specific category (only product name)
        $where_clauses[] = "category = ?";
        $where_clauses[] = "product_name LIKE ?";
        $params = [$selected_category, $like_term];
        $types = "ss";
    } else {
        // Search across all products (product_name or category)
        $where_clauses[] = "(product_name LIKE ? OR category LIKE ?)";
        $params = [$like_term, $like_term];
        $types = "ss";
    }
} elseif ($selected_category && $selected_category !== '') {
    // Category view without search
    $where_clauses[] = "category = ?";
    $params = [$selected_category];
    $types = "s";
}

// Combine WHERE clauses
if (count($where_clauses) > 0) {
    $sql = $base_sql . " WHERE " . implode(" AND ", $where_clauses);
}

// Add ORDER BY for search/category results if not random
if ($search_term || ($selected_category && $selected_category !== '')) {
    $sql .= " ORDER BY product_name ASC";
} else {
    // Default view (random products) - Add status filter here too if not already handled
    if (!in_array("status = 0", $where_clauses)) { // Avoid duplicate status check if needed
        $where_clauses[] = "status = 0";
    }
    $sql = $base_sql . (count($where_clauses) > 0 ? " WHERE " . implode(" AND ", $where_clauses) : "") . " ORDER BY RAND() LIMIT 20";
    // Clear params/types as RAND() query doesn't need them
    $params = [];
    $types = "";
}

// Fetch products
$products = [];
if (!empty($sql)) {
    if (!empty($params)) {
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    $products[] = $row;
                }
            } else {
                error_log("Error getting result: " . $stmt->error); // Log error
            }
            $stmt->close();
        } else {
            error_log("Error preparing statement: " . $conn->error); // Log error
        }
    } else {
        // For the RAND() query without params
        $result = $conn->query($sql);
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $products[] = $row;
            }
        } elseif (!$result) {
            error_log("Error executing random query: " . $conn->error); // Log error
        }
    }
}
// --- End Product Fetching Logic ---

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KM Tanay Store</title>
    <link rel="stylesheet" href="style.css"> <?php include('link.php'); ?>
    <style>
        /* Paste all the <style> content from the previous homepage.php here */
        /* Including banner styles, main-container, section, categories, products, footer, etc. */
        /* ... (Ensure all styles from the original file's <style> tag are here) ... */
        .banner {
            display: flex;
            justify-content: center;
            align-items: flex-start;
            /* Align items to the top */
            max-width: 90%;
            margin: 20px auto;
            /* Added margin */
            gap: 10px;
            min-height: 300px;
            /* Minimum height for the banner area */
        }

        .banner-left {
            flex: 2;
            /* Takes 2/3 of the space */
            overflow: hidden;
            border-radius: 5px;
            position: relative;
            height: 500px;
            /* Fixed height for slideshow container */
        }

        .banner-left img.slideshow-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            /* Cover the container */
            border-radius: 5px;
            transition: opacity 0.5s ease-in-out;
            position: absolute;
            top: 0;
            left: 0;
        }

        .banner-right {
            flex: 1;
            /* Takes 1/3 of the space */
            display: flex;
            flex-direction: column;
            gap: 10px;
            /* Gap between the two right images */
            height: 500px;
            /* Match the height of the left banner */
        }

        .banner-item {
            flex: 1;
            /* Each item takes half the height */
            overflow: hidden;
            /* Hide overflow */
            border-radius: 5px;
        }

        .banner-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            /* Cover the container */
            display: block;
            /* Remove extra space below image */
            border-radius: 5px;
        }

        .main-container {
            max-width: 1200px;
            /* Increased max-width */
            margin: auto;
            padding: 0 15px;
        }

        .section {
            background-color: #ffe4ec;
            padding: 30px;
            margin: 40px 0 20px;
            border-radius: 12px;
        }

        .section h3 {
            /* Changed from h2 to h3 */
            margin-bottom: 20px;
            color: #333;
            font-weight: bold;
            /* Make headings bold */
        }

        .categories {
            display: flex;
            flex-wrap: wrap;
            /* Allow wrapping */
            justify-content: center;
            /* Center items */
            gap: 15px;
            padding: 10px 0;
        }

        .category-card {
            background-color: #fdaec8;
            border-radius: 15px;
            padding: 10px;
            text-align: center;
            width: 120px;
            flex: 0 0 auto;
            /* Prevent shrinking/growing */
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s;
            text-decoration: none;
            /* Remove underline from links */
        }

        .category-card img {
            width: 60px;
            height: 60px;
            object-fit: contain;
        }

        .category-card p {
            margin-top: 10px;
            font-size: 14px;
            color: #000;
            font-weight: 500;
        }

        .category-card:hover {
            transform: scale(1.05);
        }

        .products {
            display: grid;
            /* grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); */
            /* Responsive grid */
            grid-template-columns: repeat(5, 1fr);
            /* Maintain 5 columns for larger screens */
            gap: 20px;
        }

        .product-card-wrapper {
            position: relative;
            /* Needed for discount tag positioning */
            display: flex;
            /* Use flexbox for better control */
            flex-direction: column;
            height: 100%;
            /* Make wrapper take full height */
        }

        .product-card {
            background-color: #fff;
            border-radius: 15px;
            width: 100%;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            /* Ensure content stays within rounded corners */
            display: flex;
            flex-direction: column;
            flex-grow: 1;
            /* Allow card to grow */
        }

        .product-card img {
            object-fit: cover;
            height: 140px;
            /* Fixed height for images */
            width: 100%;
            border-top-left-radius: 12px;
            /* Match card radius */
            border-top-right-radius: 12px;
            display: block;
        }

        .product-info {
            padding: 10px;
            text-align: left;
            /* Align text to the left */
            overflow-wrap: break-word;
            /* Wrap long product names */
            flex-grow: 1;
            /* Allow info section to grow */
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            /* Push price down */
        }

        .product-info h4 {
            font-size: 14px;
            margin: 0 0 5px 0;
            /* Adjusted margin */
            font-weight: 500;
            line-height: 1.2;
            /* Improve readability */
            /* Limit name to 2 lines */
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
            min-height: 34px;
            /* Approximate height for 2 lines */
        }

        .product-info p {
            /* Price */
            font-size: 14px;
            /* Slightly larger price */
            color: #e75480;
            /* Pink color for price */
            margin: 5px 0 0 0;
            /* Add some top margin */
            font-weight: bold;
        }

        .custom-footer {
            background-color: #ffaec8;
            padding: 15px 20px 30px 20px;
            font-family: 'Segoe UI', sans-serif;
            font-size: 14px;
            position: relative;
        }

        .footer-content {
            display: flex;
            justify-content: center;
            /* Center all columns */
            align-items: flex-start;
            gap: 50px;
            /* Optional: spacing between columns */
            flex-wrap: wrap;
            text-align: center;
            /* Center text inside each column */
        }

        .footer-column {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            /* Align items to the left */
            justify-content: flex-start;
            min-width: 170px;
        }

        .footer-column h4 {
            font-weight: bold;
            margin-bottom: 5px;
        }

        .footer-column img {
            width: 80px;
            height: auto;
            margin-top: 5px;
        }

        .follow-us-content {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .follow-us i {
            font-size: 20px;
        }

        .follow-us span {
            font-size: 14px;
        }


        .follow-us img {
            width: 20px;
            height: 20px;
            vertical-align: middle;
            margin-right: 5px;
        }

        .follow-us span {
            font-size: 14px;
            vertical-align: middle;
            display: inline-block;
            margin-top: 5px;
        }

        .footer-bottom {
            width: 100%;
            text-align: center;
            margin-top: 20px;
            font-size: 12px;
            color: #333;
            position: static;
        }

        .contact-info {
            display: flex;
            gap: 30px;
            flex-wrap: wrap;
        }

        .contact-info .address,
        .contact-info .email-phone {
            flex: 1;
            min-width: 200px;
        }

        /* Responsive adjustments */
        @media (max-width: 992px) {
            .products {
                grid-template-columns: repeat(4, 1fr);
                /* 4 columns for tablets */
            }

            .banner-left,
            .banner-right {
                height: 400px;
                /* Adjust height */
            }
        }

        @media (max-width: 768px) {
            .products {
                grid-template-columns: repeat(3, 1fr);
                /* 3 columns for smaller tablets */
            }

            .banner {
                flex-direction: column;
            }

            .banner-left,
            .banner-right {
                height: auto;
                /* Auto height for mobile */
                width: 100%;
            }

            .banner-left {
                min-height: 250px;
                /* Ensure minimum height */
            }

            .banner-right {
                flex-direction: row;
                /* Side-by-side on mobile */
                height: 150px;
                /* Adjust height for right items */
            }
        }

        @media (max-width: 576px) {
            .products {
                grid-template-columns: repeat(2, 1fr);
                /* 2 columns for mobile */
            }

            .section {
                padding: 20px;
            }

            .category-card {
                width: 100px;
                /* Smaller category cards */
            }

            .category-card img {
                width: 50px;
                height: 50px;
            }

            .product-info h4 {
                font-size: 13px;
                min-height: 31px;
                /* Adjust min-height */
            }

            .product-info p {
                font-size: 13px;
            }

            .banner-right {
                height: 120px;
                /* Adjust height */
            }
        }

        @media (max-width: 400px) {
            .products {
                grid-template-columns: repeat(1, 1fr);
                /* 1 column for very small screens */
            }

            .banner-right {
                flex-direction: column;
                /* Stack right items */
                height: auto;
            }

            .banner-item {
                height: 150px;
                /* Give items some height */
            }
        }
    </style>
</head>

<body>

    <?php
    if ($isLoggedIn) {
        include('menu.php'); // Logged-in menu
    } else {
        include('indexMenu.php'); // Logged-out menu/header
    }
    ?>

    <p class="subtitle">"We Print While You Wait"</p>

    <form class="search-bar" method="GET" action="homepage.php">
        <input type="text" name="search" placeholder="Search products or categories"
            value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
        <button type="submit"><i class="fas fa-search"></i></button>
    </form>

    <section class="banner">
        <div class="banner-left">
            <?php if (!empty($left_banners)): ?>
                <?php foreach ($left_banners as $index => $image): ?>
                    <img src="<?php echo htmlspecialchars($image); ?>" class="slideshow-img"
                        alt="Banner Image <?php echo $index + 1; ?>" style="opacity: <?php echo $index === 0 ? '1' : '0'; ?>;">
                <?php endforeach; ?>
            <?php else: ?>
                <img src="/KMTanayAdmin/image/default.png" class="slideshow-img" alt="Default Banner">
            <?php endif; ?>
        </div>

        <div class="banner-right">
            <div class="banner-item">
                <img src="<?php echo htmlspecialchars($top_right_image); ?>" alt="Top Right Banner">
            </div>
            <div class="banner-item">
                <img src="<?php echo htmlspecialchars($bottom_right_image); ?>" alt="Bottom Right Banner">
            </div>
        </div>
    </section>

    <div class="main-container">

        <div class="section">
            <h3>Categories</h3>
            <div class="categories">
                <?php
                // Fetch all category names from the product_category table
                $category_names = [];
                $cat_query = "SELECT category FROM product_category ORDER BY category ASC";
                $cat_result = $conn->query($cat_query);

                if ($cat_result && $cat_result->num_rows > 0) {
                    while ($row_cat = $cat_result->fetch_assoc()) {
                        $category_names[] = $row_cat['category'];
                    }
                }

                // Loop through each category to display card
                foreach ($category_names as $category_name) {
                    // Query 1 random product image from this category WHERE status = 0
                    $img_query = "SELECT image FROM product_tb WHERE category = ? AND status = 0 ORDER BY RAND() LIMIT 1";
                    $img_stmt = $conn->prepare($img_query);
                    if ($img_stmt) {
                        $img_stmt->bind_param("s", $category_name);
                        $img_stmt->execute();
                        $img_result = $img_stmt->get_result();

                        $img_path = "/KMTanayAdmin/image/default_cat.png"; // Define a default category image
                        if ($img_row = $img_result->fetch_assoc()) {
                            // Check if image file exists before setting path
                            $physical_img_path = $_SERVER['DOCUMENT_ROOT'] . "/KMTanayAdmin/" . $img_row['image'];
                            if (file_exists($physical_img_path)) {
                                $img_path = "/KMTanayAdmin/" . $img_row['image'];
                            }
                        }
                        $img_stmt->close();
                    } else {
                        $img_path = "/KMTanayAdmin/image/default_cat.png"; // Fallback on prepare error
                        error_log("Error preparing image query: " . $conn->error);
                    }


                    // Output the category card link
                    $category_url = 'homepage.php?category=' . urlencode($category_name); // Link back to homepage with category filter
                    echo '
                        <a href="' . htmlspecialchars($category_url) . '" class="category-card">
                            <img src="' . htmlspecialchars($img_path) . '" alt="' . htmlspecialchars($category_name) . '">
                            <p>' . htmlspecialchars($category_name) . '</p>
                        </a>';
                }
                ?>
            </div>
        </div>

        <div class="section">
            <h3>
                <?php
                if ($search_term) {
                    echo 'Search Results for "' . htmlspecialchars($search_term) . '"';
                } elseif ($selected_category && $selected_category !== '') {
                    echo htmlspecialchars($selected_category) . ' Products';
                } else {
                    echo 'Recommended Products';
                }
                ?>
            </h3>

            <div class="products">
                <?php
                if (empty($products)) {
                    echo "<p>No products found matching your criteria.</p>";
                } else {
                    foreach ($products as $product) {
                        $image_path = "/KMTanayAdmin/" . $product['image'];
                        // Optional: Check if image exists physically before displaying
                        // $physical_product_path = $_SERVER['DOCUMENT_ROOT'] . $image_path;
                        // if (!file_exists($physical_product_path)) {
                        //    $image_path = "/KMTanayAdmin/image/default_product.png"; // Default product image
                        // }
                
                        echo '<div class="product-card-wrapper">';

                        // Check if the product has a discount
                        $discount_percentage = null;
                        $discount_query = "SELECT discount FROM discount WHERE product_id = ?";
                        $discount_stmt = $conn->prepare($discount_query);
                        if ($discount_stmt) {
                            $discount_stmt->bind_param("i", $product['product_id']);
                            $discount_stmt->execute();
                            $discount_result = $discount_stmt->get_result();
                            if ($discount_row = $discount_result->fetch_assoc()) {
                                $discount_percentage = (int) $discount_row['discount'];
                            }
                            $discount_stmt->close();
                        }

                        // Display discount banner if applicable
                        if ($discount_percentage !== null) {
                            echo '<div class="discount-banner" style="position: absolute; top: 10px; left: 10px; background-color: red; color: white; padding: 5px 10px; font-size: 12px; border-radius: 5px;">';
                            echo htmlspecialchars($discount_percentage) . '% OFF';
                            echo '</div>';
                        }

                        echo '
<a href="product_detail.php?product_name=' . urlencode($product['product_name']) . '" style="text-decoration: none; color: inherit;"> 
    <div class="product-card">
        <img src="' . htmlspecialchars($image_path) . '" alt="' . htmlspecialchars($product['product_name']) . '">
        <div class="product-info">
            <h4>' . htmlspecialchars($product['product_name']) . '</h4>';

// Fetch price range for the product
$min_price = null;
$max_price = null;

// Check if the product has variants in the product_variant_tb table
$sql_price_range = "SELECT MIN(price) AS min_price, MAX(price) AS max_price FROM product_variant_tb WHERE product_name = ?";
$stmt_price_range = $conn->prepare($sql_price_range);
if ($stmt_price_range) {
    $stmt_price_range->bind_param("s", $product['product_name']);
    $stmt_price_range->execute();
    $result_price_range = $stmt_price_range->get_result();
    if ($row_price_range = $result_price_range->fetch_assoc()) {
        $min_price = (float) $row_price_range['min_price'];
        $max_price = (float) $row_price_range['max_price'];
    }
    $stmt_price_range->close();
}

// If no variants exist, fall back to the base product price
if ($min_price === null || $max_price === null) {
    $min_price = $max_price = (float) $product['price'];
}

// Display price range or single price
if ($min_price !== null && $max_price !== null) {
    if ($min_price !== $max_price) {
        echo '<p>₱' . number_format($min_price, 0) . '-₱' . number_format($max_price, 0) . '</p>';
    } else {
        echo '<p>₱' . number_format($min_price, 0) . '</p>';
    }
} else {
    echo '<p>Price not available</p>';
}

echo '
        </div>
    </div>
</a>
</div>';

                    }
                }
                ?>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            let slideIndex = 0;
            let slides = document.querySelectorAll(".banner-left .slideshow-img");

            if (slides.length > 1) { // Only run slideshow if there's more than one image
                function showNextSlide() {
                    slides.forEach((img, index) => {
                        // Explicitly set opacity for cross-fading effect
                        img.style.opacity = index === slideIndex ? '1' : '0';
                    });
                    slideIndex = (slideIndex + 1) % slides.length;
                }
                // Initial display
                slides.forEach((img, index) => { img.style.opacity = index === 0 ? '1' : '0'; });
                // Change slide every 5 seconds
                setInterval(showNextSlide, 5000);
            } else if (slides.length === 1) {
                slides[0].style.opacity = '1'; // Ensure the single image is visible
            }
        });
    </script>

    <?php include('footer.php'); ?>

</body>

</html>