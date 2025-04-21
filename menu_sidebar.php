<?php
// Ensure no output is sent before session_start() or header() calls
if (session_status() === PHP_SESSION_NONE) {
    session_start(); // Start the session to access session variables
}

include('cnn.php');

// Prevent accidental output before headers
ob_start();

$customer_first_name = "Guest"; // Default to "Guest"
$profile_picture = '<i class="fa-solid fa-circle-user" style="font-size: 80px; color: #fff;"></i>'; // Default profile icon with white color

// Debugging: Check if session customer_id is set
if (isset($_SESSION['customer_id'])) {
    error_log("Session customer_id: " . $_SESSION['customer_id']); // Log the session customer_id
} else {
    error_log("Session customer_id is not set."); // Log if session customer_id is not set
}

if (isset($_SESSION['customer_id']) && isset($conn) && $conn instanceof mysqli) { // Ensure $conn is valid
    $customer_id = $_SESSION['customer_id'];

    // Check if there is only one address for the customer
    $address_count_sql = "SELECT COUNT(*) AS address_count FROM customer_address WHERE customer_id = ?";
    $address_count_stmt = $conn->prepare($address_count_sql);
    if ($address_count_stmt) {
        $address_count_stmt->bind_param("i", $customer_id);
        $address_count_stmt->execute();
        $address_count_result = $address_count_stmt->get_result();
        $address_count_row = $address_count_result->fetch_assoc();

        if ($address_count_row['address_count'] == 1) {
            // Fetch the single address ID
            $single_address_sql = "SELECT address_id, Default_address FROM customer_address WHERE customer_id = ?";
            $single_address_stmt = $conn->prepare($single_address_sql);
            if ($single_address_stmt) {
                $single_address_stmt->bind_param("i", $customer_id);
                $single_address_stmt->execute();
                $single_address_result = $single_address_stmt->get_result();
                $single_address_row = $single_address_result->fetch_assoc();

                if ($single_address_row['Default_address'] != 1) {
                    // Set the single address as the default
                    $update_default_sql = "UPDATE customer_address SET Default_address = 1 WHERE address_id = ?";
                    $update_default_stmt = $conn->prepare($update_default_sql);
                    if ($update_default_stmt) {
                        $update_default_stmt->bind_param("i", $single_address_row['address_id']);
                        $update_default_stmt->execute();
                        $update_default_stmt->close();
                    }
                }
                $single_address_stmt->close();
            }
        }
        $address_count_stmt->close();
    }

    $sql = "SELECT customer_first_name, profile_picture FROM customer_tb WHERE customer_id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("i", $customer_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result && $row = $result->fetch_assoc()) {
            $customer_first_name = htmlspecialchars($row['customer_first_name']); // Fetch the first name
            if (!empty($row['profile_picture'])) {
                $profile_picture = '<img src="' . htmlspecialchars($row['profile_picture']) . '" alt="Profile Picture" style="width: 80px; height: 80px; border-radius: 50%;">';
            }
        } else {
            error_log("No customer found for customer_id: " . $customer_id); // Debugging: Log if no customer is found
        }
        $stmt->close();
    } else {
        error_log("Failed to prepare statement: " . $conn->error); // Debugging: Log SQL preparation error
    }
} else {
    error_log("Session customer_id is not set or database connection is invalid."); // Debugging: Log if session or connection is invalid
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['sign_out'])) {
    session_destroy();
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile_picture'])) {
    if (isset($_SESSION['customer_id']) && isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/profile_pictures/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true); // Create directory if it doesn't exist
        }
        $fileName = uniqid() . '_' . basename($_FILES['profile_picture']['name']);
        $targetFilePath = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $targetFilePath)) {
            $customer_id = $_SESSION['customer_id'];
            $sql = "UPDATE customer_tb SET profile_picture = ? WHERE customer_id = ?";
            $stmt = $conn->prepare($sql);
            if ($stmt) {
                $stmt->bind_param("si", $targetFilePath, $customer_id); // Use the full file path
                $stmt->execute();
                $stmt->close();
                $profile_picture = '<img src="' . htmlspecialchars($targetFilePath) . '" alt="Profile Picture" style="width: 80px; height: 80px; border-radius: 50%;">';
            }
        }
    }
}

ob_end_flush(); // Ensure no output is sent before headers
?>
<div class="sidebar" style="background-color: #ffb6c1; padding: 20px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);">
    <div class="profile-section" style="text-align: center; margin-bottom: 20px; position: relative;">
        <div id="profile-picture-container" style="display: inline-block; position: relative; cursor: pointer;">
            <?php echo $profile_picture; ?>
        </div>
        <a href="#" onclick="showProfilePictureForm(event)" style="display: block; margin-top: 10px; text-decoration: underline; color: white; font-size: 14px;">
            <i class="fa-solid fa-pen-to-square"></i> Edit picture
        </a>
        <form id="profile-picture-form" method="POST" enctype="multipart/form-data" style="display: none; margin-top: 10px;">
            <input type="file" name="profile_picture" accept="image/*" style="margin-bottom: 10px;">
            <button type="submit" name="update_profile_picture" style="background-color: #e75480; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; font-size: 14px;">Update Picture</button>
            <button type="button" onclick="hideProfilePictureForm()" style="background-color: white; color: black; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; font-size: 14px; margin-left: 10px;">Cancel</button>
        </form>
        <p style="font-size: 18px; font-weight: bold; color: #333;"><?php echo $customer_first_name; ?></p>
    </div>
    <p class="highlight" style="font-weight: bold; color: white; margin-bottom: 10px;">My Account</p>
    <a href="user_profile.php" style="display: block; color: #333; text-decoration: none; margin: 5px 0; font-size: 14px;">Profile</a>
    <a href="user_address.php" style="display: block; color: #333; text-decoration: none; margin: 5px 0; font-size: 14px;">Addresses</a>
    <a href="user_password.php" style="display: block; color: #333; text-decoration: none; margin: 5px 0; font-size: 14px;">Change Password</a>
    <a href="user_setting.php" style="display: block; color: #333; text-decoration: none; margin: 5px 0; font-size: 14px;">Privacy Settings</a> <!-- Updated to match other links -->
    <a href="my_orders.php" style="display: block; color: #333; text-decoration: none; margin: 5px 0; font-size: 14px;">My Purchase</a>
    <form method="POST" style="margin-top: 10px;">
        <button type="submit" name="sign_out" style="background: none; color: #e75480; border: none; text-decoration: underline; cursor: pointer; font-size: 14px;">Sign Out</button>
    </form>
</div>

<style>
    #profile-picture-container img {
        width: 80px;
        height: 80px;
        object-fit: cover; /* Ensures the image is not stretched */
        border-radius: 50%; /* Makes the image circular */
    }
</style>

<script>
    function showProfilePictureForm(event) {
        event.preventDefault();
        document.getElementById('profile-picture-form').style.display = 'block'; // Show the form when the link is clicked
    }

    function hideProfilePictureForm() {
        document.getElementById('profile-picture-form').style.display = 'none'; // Hide the form when the cancel button is clicked
    }
</script>
