<?php
session_start();
include('cnn.php');

if (!isset($_SESSION['customer_id'])) {
    echo "Error: User not logged in.";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $address_id = $_POST['address_id'];
    $customer_id = $_SESSION['customer_id'];

    // Check if the address belongs to the logged-in user
    $check_sql = "SELECT * FROM customer_address WHERE address_id = ? AND customer_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    if ($check_stmt) {
        $check_stmt->bind_param("ii", $address_id, $customer_id);
        $check_stmt->execute();
        $result = $check_stmt->get_result();

        if ($result->num_rows > 0) {
            // Prepare the update query dynamically based on submitted fields
            $fields = [];
            $params = [];
            $types = "";

            if (isset($_POST['customer_first_name']) && isset($_POST['customer_last_name'])) {
                $fields[] = "customer_first_name = ?";
                $fields[] = "customer_middle_name = ?";
                $fields[] = "customer_last_name = ?";
                $params[] = $_POST['customer_first_name'];
                $params[] = $_POST['customer_middle_name'] ?? ""; // Optional middle name
                $params[] = $_POST['customer_last_name'];
                $types .= "sss";
            }

            if (isset($_POST['phone_number'])) {
                $fields[] = "phone_number = ?";
                $params[] = $_POST['phone_number'];
                $types .= "s";
            }

            if (isset($_POST['House_number']) && isset($_POST['Street'])) {
                $fields[] = "House_number = ?";
                $fields[] = "Street = ?";
                $fields[] = "`Village/Subdivision` = ?";
                $fields[] = "Province = ?";
                $fields[] = "City = ?";
                $fields[] = "Barangay = ?";
                $params[] = $_POST['House_number'];
                $params[] = $_POST['Street'];
                $params[] = $_POST['Village'] ?? ""; // Optional village
                $params[] = $_POST['Province'];
                $params[] = $_POST['City'];
                $params[] = $_POST['Barangay'];
                $types .= "ssssss";
            }

            if (!empty($fields)) {
                $params[] = $address_id;
                $types .= "i";

                $update_sql = "UPDATE customer_address SET " . implode(", ", $fields) . " WHERE address_id = ?";
                $update_stmt = $conn->prepare($update_sql);
                if ($update_stmt) {
                    $update_stmt->bind_param($types, ...$params);

                    if ($update_stmt->execute()) {
                        echo "Updated successfully.";
                    } else {
                        echo "Error: Could not update address.";
                    }

                    $update_stmt->close();
                } else {
                    echo "Error: Failed to prepare the update statement.";
                }
            } else {
                echo "Error: No fields to update.";
            }
        } else {
            echo "Error: Address not found or does not belong to you.";
        }

        $check_stmt->close();
    } else {
        echo "Error: Failed to prepare the check statement.";
    }

    $conn->close();
} else {
    echo "Invalid request method.";
}
?>
