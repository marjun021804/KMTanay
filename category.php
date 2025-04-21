<?php
include('cnn.php');

$category = isset($_GET['category']) ? $_GET['category'] : '';

$sql = "SELECT product_name, image, price FROM product_tb WHERE category = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $category);
$stmt->execute();
$result = $stmt->get_result();

$products = [];
while ($row = $result->fetch_assoc()) {
    $products[] = $row;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title><?php echo htmlspecialchars($category); ?> Products</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<h2>Products under "<?php echo htmlspecialchars($category); ?>"</h2>

<div class="products">
    <?php foreach ($products as $product): ?>
        <div class="product-card-wrapper">
            <div class="product-card">
                <img src="/KMTanayAdmin/<?php echo $product['image']; ?>" alt="<?php echo $product['product_name']; ?>">
                <div class="product-info">
                    <h4><?php echo $product['product_name']; ?></h4>
                    <p>₱<?php echo number_format($product['price'], 2); ?></p>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

</body>
</html>
