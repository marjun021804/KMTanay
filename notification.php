<?php
session_start();
include('cnn.php');
include('link.php');

if (isset($_SESSION['customer_id'])) {
    include('menu.php'); // Include the menu for logged-in users
} else {
    include('indexMenu.php'); // Include the menu for guests
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - KM Tanay</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .notification-container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .notification-item {
            padding: 15px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #f9f9f9;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .notification-item:hover {
            background-color: #fce3ec;
        }

        .notification-item p {
            margin: 0;
            font-size: 16px;
            color: #333;
        }

        .notification-item .time {
            font-size: 14px;
            color: #777;
        }

        .notification-item .mark-read {
            background-color: #f79dbc;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }

        .notification-item .mark-read:hover {
            background-color: #f56a8e;
        }

        .empty-notifications {
            text-align: center;
            font-size: 18px;
            color: #777;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="notification-container">
        <h2>Notifications</h2>
        <?php
        // Fetch notifications from the database
        $notifications = [];
        if (isset($_SESSION['customer_id'])) {
            $customer_id = $_SESSION['customer_id'];
            $sql = "SELECT notification_id, message, created_at, is_read 
                    FROM notification 
                    WHERE customer_id = ? 
                    ORDER BY created_at DESC";
            $stmt = $conn->prepare($sql);
            if ($stmt) {
                $stmt->bind_param("i", $customer_id);
                $stmt->execute();
                $result = $stmt->get_result();
                while ($row = $result->fetch_assoc()) {
                    $notifications[] = $row;    
                }
                $stmt->close();
            }
        }

        if (!empty($notifications)): ?>
            <?php foreach ($notifications as $notification): ?>
                <div class="notification-item">
                    <div>
                        <p><?php echo htmlspecialchars($notification['message']); ?></p>
                        <span class="time"><?php echo date('F j, Y, g:i a', strtotime($notification['created_at'])); ?></span>
                    </div>
                    <?php if (!$notification['is_read']): ?>
                        <form method="POST" action="mark_notification_read.php">
                            <input type="hidden" name="notification_id" value="<?php echo $notification['notification_id']; ?>">
                            <button type="submit" class="mark-read">Mark as Read</button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="empty-notifications">You have no notifications.</p>
        <?php endif; ?>
    </div>

    <?php include('footer.php'); ?>
</body>
</html>
