<?php
session_start();

// Ensure the user has reached this step properly
if (!isset($_SESSION['email'])) {
    header("Location: signup.php");
    exit();
}

// Include the database connection
include('cnn.php');


// Retrieve and sanitize session values
$email        = htmlspecialchars($_SESSION['email']);
$first_name   = htmlspecialchars($_SESSION['first_name']);
$middle_name  = htmlspecialchars($_SESSION['middle_name']);
$last_name    = htmlspecialchars($_SESSION['last_name']);
$password_plain = isset($_SESSION['password']) ? $_SESSION['password'] : '';
$phone_number = htmlspecialchars($_SESSION['phone_number']);



// Debugging: Check if password exists in session
if (empty($password_plain)) {
    echo "Error: Password is missing from the session. Please go back to the signup form.";
    echo "<br>Debug: Available session keys => ";
    print_r(array_keys($_SESSION));
    exit();
}


$suffix       = htmlspecialchars($_SESSION['suffix']);
$birthday     = htmlspecialchars($_SESSION['birthday']);
$sex          = htmlspecialchars($_SESSION['sex']);
$sex_other    = htmlspecialchars($_SESSION['sex_other']);
$nationality  = htmlspecialchars($_SESSION['nationality']);



// Retrieve profile picture from session
$profile_picture = htmlspecialchars($_SESSION['profile_picture']);


// Address data
$unit_no   = htmlspecialchars($_SESSION['unit_no']);
$street    = htmlspecialchars($_SESSION['street']);
$village   = htmlspecialchars($_SESSION['village']);
$province  = htmlspecialchars($_SESSION['province']);
$city      = htmlspecialchars($_SESSION['city']);
$barangay  = htmlspecialchars($_SESSION['barangay']);

// Check if images were uploaded
if (isset($_POST['valid_id_base64']) && isset($_POST['selfie_base64'])) {

    // Decode base64 images
    $valid_id_data = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $_POST['valid_id_base64']));
    $selfie_data   = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $_POST['selfie_base64']));

    // Generate unique filenames
    $timestamp = time();
    $valid_id_path = 'uploads/validid' . $timestamp . '.png';
    $selfie_path   = 'uploads/selfie' . $timestamp . '.png';

    // Save images to disk
    if (file_put_contents($valid_id_path, $valid_id_data) && file_put_contents($selfie_path, $selfie_data)) {

        // Prepare SQL statement for customer_tb
        $sql = "INSERT INTO customer_tb 
        (customer_first_name, customer_middle_name, customer_last_name, customer_suffix, phone_number, customer_birthday, customer_sex, nationality, email, password, Valid_ID, Selfie, profile_picture) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        if ($stmt = $conn->prepare($sql)) {
            // Bind parameters and execute the statement
            $stmt->bind_param(
                "sssssssssssss",
                $first_name,
                $middle_name,
                $last_name,
                $suffix,
                $phone_number,
                $birthday,
                $sex,
                $nationality,
                $email,
                $password_plain, // Use plain password
                $valid_id_path,
                $selfie_path,
                $profile_picture // Bind profile picture
            );

            // Execute and proceed if successful
            if ($stmt->execute()) {
                $customer_id = $stmt->insert_id; // Get the newly inserted customer ID

                // Check if there are existing addresses for this customer
                $check_sql = "SELECT Default_address FROM customer_address WHERE customer_id = ?";
                $check_stmt = $conn->prepare($check_sql);
                $check_stmt->bind_param("i", $customer_id);
                $check_stmt->execute();
                $check_result = $check_stmt->get_result();

                // Determine the value of Default_address
                $default_address = ($check_result->num_rows === 0) ? 1 : 0;

                // Insert into customer_address
                $address_sql = "INSERT INTO customer_address 
                (customer_id, customer_first_name, customer_middle_name, customer_last_name, phone_number, House_number, Street, `Village/Subdivision`, Province, City, Barangay, Default_address) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $address_stmt = $conn->prepare($address_sql);
                $address_stmt->bind_param(
                    "issssssssssi",
                    $customer_id,
                    $first_name,
                    $middle_name,
                    $last_name,
                    $phone_number,
                    $unit_no,
                    $street,
                    $village,
                    $province,
                    $city,
                    $barangay,
                    $default_address
                );

                if ($address_stmt->execute()) {
                    unset($_SESSION['password']); // Clear hashed password from session
                    header("Location: success.php");
                    exit();
                } else {
                    echo "Error inserting into customer_address: " . $address_stmt->error;
                }

                $address_stmt->close();
                $check_stmt->close();
            } else {
                echo "Database error: " . $stmt->error;
            }

            $stmt->close();
        } else {
            echo "Error preparing statement: " . $conn->error;
        }
    } else {
        echo "Error saving uploaded images.";
    }
} else {
    echo "Image data not received. Please try again.";
}

$conn->close();

?>
