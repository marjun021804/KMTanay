<?php
session_start();
include('cnn.php');

$isLoggedIn = isset($_SESSION['customer_id']); // Define $isLoggedIn based on session

$full_name = "Guest"; // Default to "Guest" if not logged in
$email = "Not Set"; // Default email if not logged in or not found
$gender = "Not Set"; // Default gender if not logged in or not found
$other_gender = ""; // Default for "Other" gender
$birthday = "Not Set"; // Default birthday if not logged in or not found
$phone_number = "Not Set"; // Default phone number if not logged in or not found

if ($isLoggedIn) {
    $customer_id = $_SESSION['customer_id'];
    $sql = "SELECT customer_first_name, customer_middle_name, customer_last_name, customer_suffix, email, customer_sex AS customer_gender, customer_birthday, phone_number FROM customer_tb WHERE customer_id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("i", $customer_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result && $row = $result->fetch_assoc()) {
            $first_name = htmlspecialchars($row['customer_first_name']);
            $middle_name = htmlspecialchars($row['customer_middle_name']);
            $last_name = htmlspecialchars($row['customer_last_name']);
            $suffix = htmlspecialchars($row['customer_suffix'] ?? ''); // Fetch suffix if available
            $email = htmlspecialchars($row['email']);
            $gender = htmlspecialchars($row['customer_gender']);
            $birthday = htmlspecialchars($row['customer_birthday']);
            $phone_number = htmlspecialchars($row['phone_number']); // Fetch phone number
            if (!in_array($gender, ["Male", "Female", "Other"])) {
                $other_gender = $gender;
                $gender = "Other";
            }
            $full_name = trim("$first_name $middle_name $last_name $suffix");
        }
        $stmt->close();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_name'])) {
    $first_name = htmlspecialchars(trim($_POST['first_name']));
    $middle_name = htmlspecialchars(trim($_POST['middle_name']));
    $last_name = htmlspecialchars(trim($_POST['last_name']));
    $suffix = htmlspecialchars(trim($_POST['suffix']));

    if ($isLoggedIn) {
        $customer_id = $_SESSION['customer_id'];
        $sql = "UPDATE customer_tb SET customer_first_name = ?, customer_middle_name = ?, customer_last_name = ?, customer_suffix = ? WHERE customer_id = ?";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("ssssi", $first_name, $middle_name, $last_name, $suffix, $customer_id);
            $stmt->execute();
            $stmt->close();
            $full_name = trim("$first_name $middle_name $last_name $suffix"); // Update the displayed name
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_gender'])) {
    $gender = htmlspecialchars(trim($_POST['gender']));
    $other_gender = ($gender === "Other") ? htmlspecialchars(trim($_POST['other_gender'])) : "";

    if ($isLoggedIn) {
        $customer_id = $_SESSION['customer_id'];
        $final_gender = ($gender === "Other") ? $other_gender : $gender;
        $sql = "UPDATE customer_tb SET customer_sex = ? WHERE customer_id = ?";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("si", $final_gender, $customer_id);
            $stmt->execute();
            $stmt->close();
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_birthday'])) {
    $birthday = htmlspecialchars(trim($_POST['birthday']));

    if ($isLoggedIn) {
        $customer_id = $_SESSION['customer_id'];
        $sql = "UPDATE customer_tb SET customer_birthday = ? WHERE customer_id = ?";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("si", $birthday, $customer_id);
            $stmt->execute();
            $stmt->close();
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_phone_number'])) {
    $phone_number = htmlspecialchars(trim($_POST['phone_number']));

    if ($isLoggedIn) {
        $customer_id = $_SESSION['customer_id'];
        $sql = "UPDATE customer_tb SET phone_number = ? WHERE customer_id = ?";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("si", $phone_number, $customer_id);
            $stmt->execute();
            $stmt->close();
        }
    }
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Include PHPMailer files
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_verification_code'])) {
    $new_email = htmlspecialchars(trim($_POST['email']));

    if ($isLoggedIn) {
        $verification_code = rand(100000, 999999); // Generate a 6-digit verification code
        $_SESSION['verification_code'] = $verification_code; // Store the code in the session
        $_SESSION['pending_email'] = $new_email; // Store the new email in the session

        // Send the verification code using PHPMailer
        $mail = new PHPMailer(true);
        try {
            // SMTP configuration
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com'; // Replace with your SMTP server
            $mail->SMTPAuth = true;
            $mail->Username = 'marjun.manalo12@gmail.com'; // Replace with your email address
            $mail->Password = 'idlw krkn mjse qwnf'; // Replace with your app password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587; // Common SMTP port for STARTTLS

            // Email settings
            $mail->setFrom('your-email@gmail.com', 'KM Tanay'); // Replace with your email address
            $mail->addAddress($new_email);
            $mail->Subject = 'Email Verification Code';
            $mail->Body = "Your verification code is: $verification_code";

            $mail->send();
            echo "<script>alert('A verification code has been sent to $new_email. Please enter it to confirm the change.');</script>";
        } catch (Exception $e) {
            echo "<script>alert('Failed to send verification code. Error: {$mail->ErrorInfo}');</script>";
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify_code'])) {
    $entered_code = htmlspecialchars(trim($_POST['verification_code']));

    if ($isLoggedIn && isset($_SESSION['verification_code']) && isset($_SESSION['pending_email'])) {
        if ($entered_code == $_SESSION['verification_code']) {
            $customer_id = $_SESSION['customer_id'];
            $new_email = $_SESSION['pending_email'];

            $sql = "UPDATE customer_tb SET email = ? WHERE customer_id = ?";
            $stmt = $conn->prepare($sql);
            if ($stmt) {
                $stmt->bind_param("si", $new_email, $customer_id);
                $stmt->execute();
                $stmt->close();

                $email = $new_email; // Update the displayed email
                unset($_SESSION['verification_code'], $_SESSION['pending_email']); // Clear the session variables

                echo "<script>alert('Your email has been successfully updated.');</script>";
            }
        } else {
            echo "<script>alert('Invalid verification code. Please try again.');</script>";
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['sign_out'])) {
    session_destroy(); // Destroy the session
    header("Location: login.php"); // Redirect to login.php
    exit(); // Ensure no further code is executed
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KM Tanay - My Profile</title>
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

        .profile-details {
            flex: 3 1 600px;
            background-color: #fff; /* Changed to white */
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .profile-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .profile-header h2 {
            font-size: 24px;
            color: #333;
        }

        .profile-header p {
            font-size: 14px;
            color: #666;
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

        .form-group input[type="text"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .form-group .value {
            font-weight: bold;
            color: #555;
        }

        .form-actions {
            text-align: right;
        }

        .form-actions button {
            background-color: #ffb6c1; /* Light pink background */
            color: white; /* White text */
            border: none;
            text-align: center;
            width: auto; /* Adjust width to fit content */
            padding: 12px 30px; /* Increased padding for better appearance */
            font-size: 14px; /* Standard font size */
            font-weight: bold; /* Bold text */
            border-radius: 8px; /* Rounded corners */
            text-decoration: none;
            cursor: pointer;
            margin: 5px; /* Add spacing around buttons */
            transition: background-color 0.3s, color 0.3s; /* Smooth transitions */
        }

        .form-actions button:hover {
            color: white; /* Ensure text remains white on hover */
            background-color: #ff8da1; /* Darker pink background on hover */
            text-decoration: none;
        }

        .form-actions button:active {
            background-color: #f78faa; /* Darker pink background when clicked */
            border-color: #e75480; /* Vibrant pink border when clicked */
        }

        .btn1 {
            background-color: #ffb6c1; /* Light pink background */
            color: white; /* White text */
            border: none;
            text-align: center;
            padding: 10px 20px; /* Adjusted padding for better appearance */
            font-size: 14px; /* Standard font size */
            font-weight: bold; /* Bold text */
            border-radius: 8px; /* Rounded corners */
            cursor: pointer;
            margin: 5px; /* Add spacing around buttons */
            transition: background-color 0.3s, transform 0.2s; /* Smooth transitions */
        }

        .btn1:hover {
            background-color: #ff8da1; /* Darker pink background on hover */
            transform: scale(1.05); /* Slight zoom effect on hover */
        }

        .btn1:active {
            background-color: #f78faa; /* Darker pink background when clicked */
            transform: scale(0.95); /* Slight shrink effect on click */
        }

        .change-link {
            text-decoration: underline;
            color: #e75480; /* Match the color from user_address */
            cursor: pointer;
        }

        .change-link:hover {
            color: #ff8da1; /* Match the hover color from user_address */
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

<div class="main-container">
    <div class="profile-sidebar">
        <?php include('menu_sidebar.php'); ?> <!-- Sidebar on the left -->
    </div>

    <div class="profile-details">
        <div class="profile-header">
            <h2>My Profile</h2>
            <p>Manage your account</p>
        </div>


        <div class="form-group">
            <label>Name</label>
            <div class="value" id="name-display">
                <?php echo $full_name; ?> 
                <a href="#" class="change-link" onclick="editName(event)">Change</a>
            </div>
            <form id="name-edit" style="display: none;" method="POST">
                <div class="form-group">
                    <label for="first_name">First Name</label>
                    <input type="text" id="first_name" name="first_name" placeholder="First Name" value="<?php echo htmlspecialchars($first_name ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="middle_name">Middle Name</label>
                    <input type="text" id="middle_name" name="middle_name" placeholder="Middle Name" value="<?php echo htmlspecialchars($middle_name ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="last_name">Last Name</label>
                    <input type="text" id="last_name" name="last_name" placeholder="Last Name" value="<?php echo htmlspecialchars($last_name ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="suffix">Suffix</label>
                    <input type="text" id="suffix" name="suffix" placeholder="Suffix (e.g., Jr., Sr., III)" value="<?php echo htmlspecialchars($suffix ?? ''); ?>">
                </div>
                <div class="form-actions">
                    <button class="btn1" type="submit" name="update_name">Save</button>
                    <button class="btn1" type="button" onclick="cancelEditName(event)">Cancel</button>
                </div>
            </form>
        </div>

        <div class="form-group">
            <label>Email</label>
            <div class="value" id="email-display">
                <?php echo $email; ?> 
                <a href="#" class="change-link" onclick="editEmail(event)">Change</a>
            </div>
            <form id="email-edit" style="display: none;" method="POST">
                <div class="form-group">
                    <label for="email">New Email</label>
                    <input type="email" id="email" name="email" placeholder="Enter new email" required>
                </div>
                <div class="form-actions">
                    <button class="btn1" type="button" onclick="sendVerificationCode()">Send Verification Code</button>
                    <button class="btn1" type="button" onclick="cancelEditEmail(event)">Cancel</button>
                </div>
            </form>
        </div>

        <!-- Modal for Verification Code -->
        <div id="verification-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5); z-index: 1000; justify-content: center; align-items: center;">
            <div style="background: white; padding: 20px; border-radius: 8px; width: 300px; text-align: center;">
                <h3>Enter Verification Code</h3>
                <form id="verification-edit" method="POST">
                    <div class="form-group">
                        <label for="verification_code">Verification Code</label>
                        <input type="text" id="verification_code" name="verification_code" placeholder="Enter verification code" required style="width: 100%; padding: 10px; margin-bottom: 10px; border: 1px solid #ccc; border-radius: 5px;">
                    </div>
                    <div class="form-actions">
                        <button class="btn1" type="submit" name="verify_code">Verify and Update</button>
                        <button class="btn1" type="button" onclick="closeVerificationModal()">Cancel</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="form-group">
            <label>Phone Number</label>
            <div class="value" id="phone-number-display">
                <?php echo $phone_number ?? "Not Set"; ?> 
                <a href="#" class="change-link" onclick="editPhoneNumber(event)">Change</a>
            </div>
            <form id="phone-number-edit" style="display: none;" method="POST">
                <div class="form-group">
                    <label for="phone_number">New Phone Number</label>
                    <input type="text" id="phone_number" name="phone_number" placeholder="Enter new phone number" value="<?php echo htmlspecialchars($phone_number ?? ''); ?>" required>
                </div>
                <div class="form-actions">
                    <button class="btn1" type="submit" name="update_phone_number">Save</button>
                    <button class="btn1" type="button" onclick="cancelEditPhoneNumber(event)">Cancel</button>
                </div>
            </form>
        </div>

        <div class="form-group">
            <label>Gender</label>
            <div class="value" id="gender-display">
                <?php echo $gender === "Other" ? htmlspecialchars($other_gender) : $gender; ?> 
                <a href="#" class="change-link" onclick="editGender(event)">Change</a>
            </div>
            <form id="gender-edit" style="display: none;" method="POST">
                <div>
                    <label><input type="radio" name="gender" value="Male" <?php echo $gender === "Male" ? "checked" : ""; ?>> Male</label>
                    <label><input type="radio" name="gender" value="Female" <?php echo $gender === "Female" ? "checked" : ""; ?>> Female</label>
                    <label><input type="radio" name="gender" value="Other" <?php echo $gender === "Other" ? "checked" : ""; ?> onclick="toggleOtherGender(true)"> Other</label>
                    <input type="text" name="other_gender" id="other-gender" placeholder="Specify" value="<?php echo htmlspecialchars($other_gender); ?>" style="display: <?php echo $gender === "Other" ? 'inline-block' : 'none'; ?>;">
                </div>
                <div class="form-actions">
                    <button class="btn1" type="submit" name="update_gender">Save</button>
                    <button class="btn1" type="button" onclick="cancelEditGender(event)">Cancel</button>
                </div>
            </form>
        </div>

        <div class="form-group">
            <label>Date of Birth</label>
            <div class="value" id="birthday-display">
                <?php echo $birthday; ?> 
                <a href="#" class="change-link" onclick="editBirthday(event)">Change</a>
            </div>
            <form id="birthday-edit" style="display: none;" method="POST">
                <input type="date" name="birthday" value="<?php echo htmlspecialchars($birthday); ?>" required>
                <div class="form-actions">
                    <button class="btn1" type="submit" name="update_birthday">Save</button>
                    <button class="btn1" type="button" onclick="cancelEditBirthday(event)">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include('footer.php'); ?>

<script>
    function editName(event) {
        event.preventDefault();
        document.getElementById('name-display').style.display = 'none';
        document.getElementById('name-edit').style.display = 'block';
    }

    function saveName(event) {
        event.preventDefault();
        const nameInput = document.getElementById('name-input').value;
        document.getElementById('name-display').innerHTML = `
            ${nameInput} 
            <a href="#" class="change-link" onclick="editName(event)">Change</a>
        `;
        document.getElementById('name-display').style.display = 'block';
        document.getElementById('name-edit').style.display = 'none';
    }

    function cancelEditName(event) {
        event.preventDefault();
        document.getElementById('name-display').style.display = 'block';
        document.getElementById('name-edit').style.display = 'none';
    }

    function editGender(event) {
        event.preventDefault();
        document.getElementById('gender-display').style.display = 'none';
        document.getElementById('gender-edit').style.display = 'block';
    }

    function cancelEditGender(event) {
        event.preventDefault();
        document.getElementById('gender-display').style.display = 'block';
        document.getElementById('gender-edit').style.display = 'none';
    }

    function toggleOtherGender(show) {
        const otherGenderInput = document.getElementById('other-gender');
        otherGenderInput.style.display = show ? 'inline-block' : 'none';
        if (!show) {
            otherGenderInput.value = '';
        }
    }

    function editBirthday(event) {
        event.preventDefault();
        document.getElementById('birthday-display').style.display = 'none';
        document.getElementById('birthday-edit').style.display = 'block';
    }

    function cancelEditBirthday(event) {
        event.preventDefault();
        document.getElementById('birthday-display').style.display = 'block';
        document.getElementById('birthday-edit').style.display = 'none';
    }

    function editEmail(event) {
        event.preventDefault();
        document.getElementById('email-display').style.display = 'none';
        document.getElementById('email-edit').style.display = 'block';
    }

    function cancelEditEmail(event) {
        event.preventDefault();
        document.getElementById('email-display').style.display = 'block';
        document.getElementById('email-edit').style.display = 'none';
    }

    function sendVerificationCode() {
        const emailInput = document.getElementById('email').value;

        if (!emailInput) {
            alert('Please enter a valid email address.');
            return;
        }

        // Send the verification code via AJAX
        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'send_verification_code.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function () {
            if (xhr.status === 200) {
                alert(xhr.responseText); // Success message
                openVerificationModal();
            } else {
                alert(xhr.responseText); // Error message
            }
        };
        xhr.send('email=' + encodeURIComponent(emailInput));
    }

    function openVerificationModal() {
        document.getElementById('verification-modal').style.display = 'flex';
    }

    function closeVerificationModal() {
        document.getElementById('verification-modal').style.display = 'none';
    }

    function editPhoneNumber(event) {
        event.preventDefault();
        document.getElementById('phone-number-display').style.display = 'none';
        document.getElementById('phone-number-edit').style.display = 'block';
    }

    function cancelEditPhoneNumber(event) {
        event.preventDefault();
        document.getElementById('phone-number-display').style.display = 'block';
        document.getElementById('phone-number-edit').style.display = 'none';
    }
</script>

</body>
</html>
