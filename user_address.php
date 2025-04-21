<?php
session_start();
include('cnn.php');

$isLoggedIn = isset($_SESSION['customer_id']); // Define $isLoggedIn based on session

$addresses = []; // Initialize an array to store addresses

if ($isLoggedIn) {
    $customer_id = $_SESSION['customer_id'];
    $sql = "SELECT address_id, customer_first_name, customer_middle_name, customer_last_name, phone_number, House_number, Street, `Village/Subdivision`, Province, City, Barangay, Default_address 
            FROM customer_address WHERE customer_id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("i", $customer_id);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $addresses[] = $row; // Store each address in the array
        }
        $stmt->close();

        // Automatically set Default_address = 1 if there is only one address
        if (count($addresses) === 1 && $addresses[0]['Default_address'] != 1) {
            $single_address_id = $addresses[0]['address_id'];
            $update_sql = "UPDATE customer_address SET Default_address = 1 WHERE address_id = ?";
            $update_stmt = $conn->prepare($update_sql);
            if ($update_stmt) {
                $update_stmt->bind_param("i", $single_address_id);
                $update_stmt->execute();
                $update_stmt->close();

                // Update the Default_address value in the $addresses array
                $addresses[0]['Default_address'] = 1;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KM Tanay - My Addresses</title>
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

        .address-details {
            flex: 3 1 600px;
            background-color: #fff;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            position: relative;
        }

        .address-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .address-header h2 {
            font-size: 24px;
            color: #333;
        }

        .address-box {
            border: 1px solid #ccc;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .address-box .form-group {
            margin-bottom: 10px;
        }

        .address-box .form-group label {
            font-weight: bold;
            color: #333;
        }

        .address-box .form-group span {
            display: block;
            margin-top: 5px;
            color: #555;
        }

        .address-box .change-link {
            text-decoration: underline;
            color: #e75480;
            cursor: pointer;
        }

        .address-box .change-link:hover {
            color: #ff8da1;
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

        .add-address-btn {
            position: absolute;
            top: 20px;
            right: 20px;
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

        .add-address-btn:hover {
            background-color: #ff8da1;
        }

        .add-address-btn:active {
            background-color: #f78faa;
        }

        .set-default-btn {
            background-color: #e75480;
            color: white;
            border: none;
            padding: 8px 15px;
            font-size: 14px;
            font-weight: bold;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .set-default-btn:hover {
            background-color: #d43d6e;
        }

        .set-default-btn:active {
            background-color: #c12c5b;
        }

        .delete-btn {
            background-color: #ff4d4d;
            color: white;
            border: none;
            padding: 8px 15px;
            font-size: 14px;
            font-weight: bold;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .delete-btn:hover {
            background-color: #ff1a1a;
        }

        .delete-btn:active {
            background-color: #e60000;
        }

        .popup-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .popup-content {
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            width: 500px;
            max-width: 90%;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .popup-content h3 {
            margin-top: 0;
            font-size: 22px;
            color: #333;
            text-align: center;
        }

        .popup-content .form-group {
            margin-bottom: 15px;
        }

        .popup-content .form-group label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
            color: #333;
        }

        .popup-content .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .popup-actions {
            text-align: center;
        }

        .popup-actions button {
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

        .popup-actions button:hover {
            background-color: #ff8da1;
        }

        .popup-actions button:active {
            background-color: #f78faa;
        }

        .form-group input[type="text"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 14px;
            color: #333;
            transition: border-color 0.3s;
        }

        .form-group input[type="text"]:focus {
            border-color: #ff8da1;
            outline: none;
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
    include('menu.php'); // Logged-in menu
} else {
    include('indexMenu.php'); // Logged-out menu/header
}
?>

<div class="main-container">
    <div class="profile-sidebar">
        <?php include('menu_sidebar.php'); ?> <!-- Sidebar on the left -->
    </div>

    <div class="address-details">
        <button class="add-address-btn" onclick="showPopup()">Add Address</button>
        <div class="address-header">
            <h2>My Addresses</h2>
        </div>

        <?php foreach ($addresses as $address): ?>
        <div class="address-box">
            <div class="form-group">
                <label>Full Name</label>
                <span id="name-display-<?php echo $address['address_id']; ?>">
                    <?php echo htmlspecialchars($address['customer_first_name'] . ' ' . $address['customer_middle_name'] . ' ' . $address['customer_last_name']); ?>
                    <a href="#" class="change-link" onclick="editField(event, 'name', <?php echo $address['address_id']; ?>)">Change</a>
                </span>
                <form id="name-edit-<?php echo $address['address_id']; ?>" style="display: none;" method="POST" action="update_address.php">
                    <input type="hidden" name="address_id" value="<?php echo $address['address_id']; ?>">
                    <input type="text" name="customer_first_name" value="<?php echo htmlspecialchars($address['customer_first_name']); ?>" required>
                    <input type="text" name="customer_middle_name" value="<?php echo htmlspecialchars($address['customer_middle_name']); ?>">
                    <input type="text" name="customer_last_name" value="<?php echo htmlspecialchars($address['customer_last_name']); ?>" required>
                    <div class="form-actions">
                        <button type="submit">Save</button>
                        <button type="button" onclick="cancelEdit(event, 'name', <?php echo $address['address_id']; ?>)">Cancel</button>
                    </div>
                </form>
            </div>

            <div class="form-group">
                <label>Phone Number</label>
                <span id="phone-display-<?php echo $address['address_id']; ?>">
                    <?php echo htmlspecialchars($address['phone_number']); ?>
                    <a href="#" class="change-link" onclick="editField(event, 'phone', <?php echo $address['address_id']; ?>)">Change</a>
                </span>
                <form id="phone-edit-<?php echo $address['address_id']; ?>" style="display: none;" method="POST" action="update_address.php">
                    <input type="hidden" name="address_id" value="<?php echo $address['address_id']; ?>">
                    <input type="text" name="phone_number" value="<?php echo htmlspecialchars($address['phone_number']); ?>" required>
                    <div class="form-actions">
                        <button type="submit">Save</button>
                        <button type="button" onclick="cancelEdit(event, 'phone', <?php echo $address['address_id']; ?>)">Cancel</button>
                    </div>
                </form>
            </div>

            <div class="form-group">
                <label>Address</label>
                <span id="address-display-<?php echo $address['address_id']; ?>">
                    <?php echo htmlspecialchars($address['House_number']); ?>
                    <?php echo htmlspecialchars($address['Street']); ?>
                    <?php echo htmlspecialchars($address['Village/Subdivision']); ?>
                    <?php echo htmlspecialchars($address['City']); ?>
                    <?php echo htmlspecialchars($address['Province']); ?>
                    <?php echo htmlspecialchars($address['Barangay']); ?>
                    <a href="#" class="change-link" onclick="editField(event, 'address', <?php echo $address['address_id']; ?>)">Change</a>
                </span>
                <form id="address-edit-<?php echo $address['address_id']; ?>" style="display: none;" method="POST" action="update_address.php">
                    <input type="hidden" name="address_id" value="<?php echo $address['address_id']; ?>">
                    <input type="text" name="House_number" value="<?php echo htmlspecialchars($address['House_number']); ?>" placeholder="House Number" required>
                    <input type="text" name="Street" value="<?php echo htmlspecialchars($address['Street']); ?>" placeholder="Street" required>
                    <input type="text" name="Village" value="<?php echo htmlspecialchars($address['Village/Subdivision']); ?>" placeholder="Village/Subdivision">
                    <input type="text" name="Province" value="<?php echo htmlspecialchars($address['Province']); ?>" placeholder="Province" required>
                    <input type="text" name="City" value="<?php echo htmlspecialchars($address['City']); ?>" placeholder="City" required>
                    <input type="text" name="Barangay" value="<?php echo htmlspecialchars($address['Barangay']); ?>" placeholder="Barangay" required>
                    <div class="form-actions">
                        <button type="submit">Save</button>
                        <button type="button" onclick="cancelEdit(event, 'address', <?php echo $address['address_id']; ?>)">Cancel</button>
                    </div>
                </form>
            </div>

            <div class="form-actions">
                <?php if ($address['Default_address'] != 1): ?>
                    <button class="set-default-btn" onclick="setDefaultAddress(<?php echo $address['address_id']; ?>)">Set Default</button>
                <?php endif; ?>
                <button class="delete-btn" onclick="deleteAddress(<?php echo $address['address_id']; ?>)">Delete</button>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<div class="popup-overlay" id="addAddressPopup">
    <div class="popup-content" id="step1">
        <h3>Enter Name and Phone Number</h3>
        <form id="step1Form">
            <div class="form-group">
                <label for="customer_first_name">First Name</label>
                <input type="text" id="customer_first_name" name="customer_first_name" required>
            </div>
            <div class="form-group">
                <label for="customer_middle_name">Middle Name</label>
                <input type="text" id="customer_middle_name" name="customer_middle_name">
            </div>
            <div class="form-group">
                <label for="customer_last_name">Last Name</label>
                <input type="text" id="customer_last_name" name="customer_last_name" required>
            </div>
            <div class="form-group">
                <label for="phone_number">Phone Number</label>
                <input type="text" id="phone_number" name="phone_number" required>
            </div>
            <div class="popup-actions">
                <button type="button" onclick="hidePopup()">Cancel</button>
                <button type="button" onclick="goToStep2()">Next</button>
            </div>
        </form>
    </div>

    <div class="popup-content" id="step2" style="display: none;">
        <h3>Enter Address</h3>
        <form id="step2Form">
            <div class="form-group">
                <label for="House_number">House Number</label>
                <input type="text" id="House_number" name="House_number" required>
            </div>
            <div class="form-group">
                <label for="Street">Street</label>
                <input type="text" id="Street" name="Street" required>
            </div>
            <div class="form-group">
                <label for="Village">Village/Subdivision</label>
                <input type="text" id="Village" name="Village">
            </div>
            <div class="form-group">
                <label for="City">City</label>
                <input type="text" id="City" name="City" required>
            </div>
            <div class="form-group">
                <label for="Province">Province</label>
                <input type="text" id="Province" name="Province" required>
            </div>
            <div class="form-group">
                <label for="Barangay">Barangay</label>
                <input type="text" id="Barangay" name="Barangay" required>
            </div>
            <div class="popup-actions">
                <button type="button" onclick="goToStep1()">Back</button>
                <button type="submit">Save</button>
            </div>
        </form>
    </div>
</div>

<?php include('footer.php'); ?>

<script>
    function editField(event, fieldType, id) {
        event.preventDefault();
        document.getElementById(`${fieldType}-display-${id}`).style.display = 'none';
        document.getElementById(`${fieldType}-edit-${id}`).style.display = 'block';
    }

    function cancelEdit(event, fieldType, id) {
        event.preventDefault();
        document.getElementById(`${fieldType}-display-${id}`).style.display = 'block';
        document.getElementById(`${fieldType}-edit-${id}`).style.display = 'none';
    }

    document.querySelectorAll('form[id^="name-edit-"], form[id^="phone-edit-"], form[id^="address-edit-"]').forEach(form => {
        form.addEventListener('submit', function(event) {
            event.preventDefault();

            const formData = new FormData(this);

            fetch('update_address.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                alert(data); // Show success or error message
                location.reload(); // Reload the page to reflect changes
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while updating the address.');
            });
        });
    });

    function setDefaultAddress(addressId) {
        fetch('set_default_address.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `address_id=${addressId}`
        })
        .then(response => response.text())
        .then(data => {
            alert(data); // Show success or error message
            location.reload(); // Reload the page to reflect changes
        })
        .catch(error => {
            console.error('Error:', error);
        });
    }

    function deleteAddress(addressId) {
        if (confirm("Are you sure you want to delete this address?")) {
            fetch('delete_address.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `address_id=${addressId}`
            })
            .then(response => response.text())
            .then(data => {
                alert(data); // Show success or error message
                location.reload(); // Reload the page to reflect changes
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }
    }

    function showPopup() {
        document.getElementById('addAddressPopup').style.display = 'flex';
        document.getElementById('step1').style.display = 'block';
        document.getElementById('step2').style.display = 'none';
    }

    function hidePopup() {
        document.getElementById('addAddressPopup').style.display = 'none';
    }

    function goToStep2() {
        document.getElementById('step1').style.display = 'none';
        document.getElementById('step2').style.display = 'block';
    }

    function goToStep1() {
        document.getElementById('step2').style.display = 'none';
        document.getElementById('step1').style.display = 'block';
    }

    document.getElementById('step2Form').addEventListener('submit', function(event) {
        event.preventDefault();

        const step1FormData = new FormData(document.getElementById('step1Form'));
        const step2FormData = new FormData(this);

        // Combine data from both steps
        step1FormData.forEach((value, key) => step2FormData.append(key, value));

        // Add the customer_id of the logged-in user
        step2FormData.append('customer_id', <?php echo json_encode($customer_id); ?>);

        fetch('add_address_handler.php', {
            method: 'POST',
            body: step2FormData
        })
        .then(response => response.text())
        .then(data => {
            alert(data); // Show success or error message
            location.reload(); // Reload the page to reflect changes
        })
        .catch(error => {
            console.error('Error:', error);
        });
    });
</script>

</body>
</html>


