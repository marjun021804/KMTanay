<?php
session_start();
include('cnn.php');

$customer_first_name = "Guest"; // Default to "Guest" if not logged in

if (isset($_SESSION['customer_id'])) {
    $customer_id = $_SESSION['customer_id'];
    $sql = "SELECT customer_first_name FROM customer_tb WHERE customer_id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("i", $customer_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result && $row = $result->fetch_assoc()) {
            $customer_first_name = htmlspecialchars($row['customer_first_name']);
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Privacy Settings - KM Tanay</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        .main-container {
            max-width: 1200px;
            margin: auto;
            padding: 20px;
            display: flex;
            gap: 20px;
        }

        .profile-sidebar {
            flex: 1 1 250px;
        }

        .settings-details {
            flex: 3 1 600px;
            background-color: #fff;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            position: relative;
        }

        .settings-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .settings-header h2 {
            font-size: 24px;
            color: #333;
        }

        .request-box {
            border: 1px solid #ccc;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .request-box p {
            font-size: 16px;
            color: #333;
            margin: 0;
        }

        .delete-button {
            background-color: #ff4d4d;
            color: white;
            border: none;
            padding: 10px 20px;
            font-size: 14px;
            font-weight: bold;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .delete-button:hover {
            background-color: #ff1a1a;
        }

        .delete-button:active {
            background-color: #e60000;
        }
    </style>
</head>
<body>

<?php
if (isset($_SESSION['customer_id'])) {
    include('menu.php'); // Include the logged-in menu
} else {
    include('indexMenu.php'); // Include the logged-out menu
}
?>

<div class="main-container">
    <div class="profile-sidebar">
        <?php include('menu_sidebar.php'); ?> <!-- Include the sidebar -->
    </div>

    <div class="settings-details">
        <div class="settings-header">
            <h2>Privacy Settings</h2>
        </div>

        <div class="request-box">
            <p>Request Account Deletion</p>
            <button class="delete-button" onclick="requestAccountDeletion()">Delete</button>
        </div>
    </div>
</div>

<?php include('footer.php'); ?> <!-- Include the footer -->

<script>
    function requestAccountDeletion() {
        if (confirm("Are you sure you want to delete your account?")) {
            location.href = 'user_setting2.php'; // Redirect to user_setting2.php
        }
    }
</script>

</body>
</html>
