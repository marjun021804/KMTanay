<?php
session_start();
include('cnn.php');

$customer_first_name = "Guest"; // Default to "Guest" if not logged in
$customer_name = ""; // Initialize customer_name

if (isset($_SESSION['customer_id'])) {
    $customer_id = $_SESSION['customer_id'];
    $sql = "SELECT customer_first_name, customer_middle_name, customer_last_name FROM customer_tb WHERE customer_id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("i", $customer_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result && $row = $result->fetch_assoc()) {
            $customer_first_name = htmlspecialchars($row['customer_first_name']);
            $customer_middle_name = htmlspecialchars($row['customer_middle_name'] ?? "");
            $customer_last_name = htmlspecialchars($row['customer_last_name']);
            $customer_name = trim("$customer_first_name $customer_middle_name $customer_last_name");
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
    <title>Request Account Deletion</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }

        body {
            background-color: #ffffff;
        }

        .header {
            background-color: #fdaac2;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header-left {
            display: flex;
            align-items: center;
        }

        .header-left img {
            height: 50px;
            margin-right: 20px;
        }

        .header-nav a {
            color: white;
            text-decoration: none;
            margin: 0 15px;
            font-weight: bold;
        }

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

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            font-size: 14px;
            margin-bottom: 8px;
            display: block;
            color: #333;
        }

        .radio-group {
            margin-left: 20px;
        }

        .radio-option {
            margin-bottom: 10px;
        }

        textarea {
            width: 100%;
            height: 80px;
            margin-top: 10px;
            padding: 8px;
            font-size: 14px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        input[type="email"] {
            width: 100%;
            padding: 10px;
            font-size: 14px;
            margin-top: 8px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .checkbox-group {
            display: flex;
            align-items: center;
        }

        .checkbox-group input {
            margin-right: 10px;
        }

        .submit-button {
            background-color: #ffb6c1;
            color: white;
            border: none;
            padding: 10px 20px;
            font-size: 14px;
            font-weight: bold;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .submit-button:hover {
            background-color: #ff8da1;
        }

        .submit-button:active {
            background-color: #f78faa;
        }

        .footer {
            background-color: #fdaac2;
            padding: 20px;
            display: flex;
            justify-content: space-around;
            font-size: 14px;
            color: #333;
        }

        .footer div {
            max-width: 250px;
        }

        .footer b {
            display: block;
            margin-bottom: 10px;
        }

        .footer img {
            height: 20px;
            vertical-align: middle;
            margin-right: 5px;
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
            <h2>Reason for Deletion of Account</h2>
        </div>

        <form id="deletionForm" method="POST">
            <input type="hidden" name="customer_id" value="<?php echo htmlspecialchars($customer_id); ?>">
            <input type="hidden" name="customer_name" value="<?php echo htmlspecialchars($customer_name); ?>">
            <div class="form-group">
               
                <div class="radio-group">
                    <div class="radio-option">
                        <input type="radio" name="reason" id="username" value="I no longer need the service" required>
                        <label for="username">I no longer need the service</label>
                    </div>
                    <div class="radio-option">
                        <input type="radio" name="reason" id="no-longer" value="I have a duplicate account">
                        <label for="no-longer">I have a duplicate account</label>
                    </div>
                    <div class="radio-option">
                        <input type="radio" name="reason" id="others" value="Others: ">
                        <label for="others">Others</label><br>
                        <textarea id="otherReason" placeholder="Please provide more details." oninput="updateOtherReason(this)"></textarea>
                    </div>
                </div>
            </div>

            <div class="form-group checkbox-group">
                <input type="checkbox" name="terms" required>
                <label>I agree to delete my account permanently</label>
            </div>

            <button class="submit-button" type="submit">Submit</button>
        </form>
    </div>
</div>

<?php include('footer.php'); ?> <!-- Include the footer -->

<script>
    function updateOtherReason(textarea) {
        const othersRadio = document.getElementById('others');
        othersRadio.value = `Others: ${textarea.value}`;
    }

    document.getElementById('deletionForm').addEventListener('submit', function(event) {
        event.preventDefault();

        // Check if the terms checkbox is checked
        const termsCheckbox = document.querySelector('input[name="terms"]');
        if (!termsCheckbox.checked) {
            alert('You must agree to the terms and conditions before proceeding.');
            return;
        }

        const formData = new FormData(this);

        fetch('process_account_deletion.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            alert(data); // Show success or error message
            if (data.includes('successfully')) {
                location.href = 'index.php'; // Redirect to the homepage after successful submission
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while processing your request.');
        });
    });
</script>

</body>
</html>
