<?php
session_start();
include('cnn.php');

if (!isset($_SESSION['customer_id'])) {
    echo "Error: User not logged in.";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_id = $_SESSION['customer_id'];
    $customer_first_name = htmlspecialchars($_POST['customer_first_name']);
    $customer_middle_name = htmlspecialchars($_POST['customer_middle_name']);
    $customer_last_name = htmlspecialchars($_POST['customer_last_name']);
    $phone_number = htmlspecialchars($_POST['phone_number']);
    $House_number = htmlspecialchars($_POST['House_number']);
    $Street = htmlspecialchars($_POST['Street']);
    $Village = htmlspecialchars($_POST['Village']);
    $City = htmlspecialchars($_POST['City']);
    $Province = htmlspecialchars($_POST['Province']);
    $Barangay = htmlspecialchars($_POST['Barangay']);

    // Check if this is the only address for the customer
    $default_address = 0;
    $check_sql = "SELECT COUNT(*) AS address_count FROM customer_address WHERE customer_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $customer_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    $row = $check_result->fetch_assoc();

    if ($row['address_count'] == 0) {
        $default_address = 1; // Automatically set as default if no other addresses exist
    }
    $check_stmt->close();

    // Insert the new address
    $insert_sql = "INSERT INTO customer_address 
        (customer_id, customer_first_name, customer_middle_name, customer_last_name, phone_number, House_number, Street, `Village/Subdivision`, City, Province, Barangay, Default_address) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $insert_stmt = $conn->prepare($insert_sql);
    $insert_stmt->bind_param(
        "issssssssssi",
        $customer_id,
        $customer_first_name,
        $customer_middle_name,
        $customer_last_name,
        $phone_number,
        $House_number,
        $Street,
        $Village,
        $City,
        $Province,
        $Barangay,
        $default_address
    );

    if ($insert_stmt->execute()) {
        echo "Address added successfully.";
    } else {
        echo "Error: " . $insert_stmt->error;
    }

    $insert_stmt->close();
    $conn->close();
} else {
    echo "Invalid request method.";
}
?>
