<?php
session_start();
include('cnn.php');

$isLoggedIn = isset($_SESSION['customer_id']); // Check if the user is logged in

$current_password_hashed = ""; // Initialize variable to store the hashed password

if ($isLoggedIn) {
    $customer_id = $_SESSION['customer_id'];
    $sql = "SELECT password FROM customer_tb WHERE customer_id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("i", $customer_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $current_password_hashed = $row['password']; // Fetch the hashed password
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
    <title>KM Tanay - Change Password</title>
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

        .password-details {
            flex: 3 1 600px;
            background-color: #fff;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .password-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .password-header h2 {
            font-size: 24px;
            color: #333;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
            color: #333;
        }

        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .form-actions {
            text-align: right;
        }

        .form-actions button {
            background-color: #ffb6c1;
            color: white;
            border: none;
            padding: 10px 20px;
            font-size: 14px;
            font-weight: bold;
            border-radius: 8px;
            cursor: pointer;
            margin: 5px;
            transition: background-color 0.3s;
        }

        .form-actions button:hover {
            background-color: #ff8da1;
        }

        .form-actions button:active {
            background-color: #f78faa;
        }
    </style>
</head>
<body>

<?php
if ($isLoggedIn) {
    include('menu.php'); // Include the logged-in menu
} else {
    include('indexMenu.php'); // Include the logged-out menu
}
?>

<div class="main-container">
    <div class="profile-sidebar">
        <?php include('menu_sidebar.php'); ?> <!-- Include the sidebar -->
    </div>

    <div class="password-details">
        <div class="password-header">
            <h2>Change Password</h2>
            <p>For your account’s security, do not share your password with anyone else.</p>
        </div>

        <form id="passwordForm" method="POST" action="update_password.php">
            <div class="form-group">
                <label for="current_password">Current Password</label>
                <input type="password" id="current_password" name="current_password" required>
            </div>
            <div class="form-group">
                <label for="new_password">New Password</label>
                <input type="password" id="new_password" name="new_password" required>
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            <div class="form-actions">
                <button type="submit">Save</button>
            </div>
        </form>
    </div>
</div>

<?php include('footer.php'); ?> <!-- Include the footer -->

<script>
    document.getElementById('passwordForm').addEventListener('submit', function(event) {
        event.preventDefault();

        const formData = new FormData(this);

        fetch('update_password.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            if (data.includes('Error: Current password is incorrect')) {
                alert('The current password you entered is incorrect. Please try again.');
            } else if (data.includes('Error: New password cannot be the same as the current password')) {
                alert('The new password cannot be the same as the current password. Please choose a different password.');
            } else if (data.includes('Error: New password and confirm password do not match')) {
                alert('The new password and confirm password do not match. Please try again.');
            } else if (data.includes('Password updated successfully')) {
                alert('Password updated successfully.');
                document.getElementById('current_password').value = '';
                document.getElementById('new_password').value = '';
                document.getElementById('confirm_password').value = '';
            } else {
                alert('An error occurred: ' + data);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An unexpected error occurred. Please try again later.');
        });
    });
</script>

</body>
</html>