<?php
// Ensure no whitespace or output before this line
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>KM Tanay Navbar</title>
    <link rel="stylesheet" href="styles.css">
    <?php include('link.php'); ?>
</head>

<style>
  * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Poppins', sans-serif;
}

header {
    background-color: #ffb6c1;
    padding: 15px 20px; /* Increased padding for larger navbar */
    text-align: center;
    position: relative;
}

.navbar-container {
    display: flex;
    align-items: center;
    justify-content: space-between;
    width: 100%;
    padding: 0 40px; /* Add left & right padding directly */
}


.logo {
    margin-right: 40px;
}
.logo img {
    width: 70px;
    height: auto;
    display: block;
}


/* Nav links */
.nav-links {
    list-style: none;
    display: flex;
    gap: 55px;
}

.nav-links li a {
    text-decoration: none;
    color: white;
    font-size: 24px; /* Slightly bigger text */
    transition: color 0.3s;
}

.nav-links li a:hover {
    color: #3f3f3f;
}

/* Nav icons */
.nav-icons {
    display: flex;
    align-items: center;
    padding-left:20px ;
    gap: 20px;
}

.nav-icons a i {
    font-size: 30px; /* Bigger icons */
    color: white; /* Default color */
    transition: color 0.3s ease;
}

.nav-icons a i:hover {
    color: #000; /* Black on hover */
}

/* Dropdown arrow */
.dropdown span {
   
    margin-left: 4px;
}

/* Optional: Header text alignment if needed later */
.header-container {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px 20px;
}

.header-container h1 {
    margin-left: 10px;
    text-align: left;
    color: white;
    flex-grow: 1;
}

.dropdown-content {
    display: none;
    position: absolute;
    background-color: #ffe4ec;
    min-width: 160px;
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
    z-index: 1;
    border-radius: 5px;
}

.dropdown-content a {
    color: #333;
    padding: 10px 15px;
    text-decoration: none;
    display: block;
    border-bottom: 1px solid #ddd;
}

.dropdown-content a:hover {
    background-color: #f89db2;
    color: white;
}

.dropdown:hover .dropdown-content {
    display: block;
}

.dropdown:hover a {
    color: #000;
}

</style>
<body>
    <header>
    <nav class="navbar">
        <div class="navbar-container">
            <div class="logo">
                <a href="homepage.php"><img src="/KMTanayAdmin/image/kmtanaylogo.png" alt="KM Tanay Logo"></a>
            </div>
            <ul class="nav-links">
                <li><a href="homepage.php">Home</a></li>
                <li><a href="chat.php">Chat with KM Tanay</a></li>
                <li><a href="my_orders.php">My Orders</a></li>
                <li><a href="faqs.php">FAQs</a></li>
                <li class="dropdown">
                    <a href="about_us.php">About Us <span>&#9662;</span></a>
                    <div class="dropdown-content">
                        <a href="about_us.php#history-section">History</a>
                        <a href="about_us.php#location-section">Location</a>
                    </div>
                </li>
            </ul>
            <div class="nav-icons">
                <a href="cart.php"><i class="fa-solid fa-cart-shopping"></i></a>
                <a href="notification.php"><i class="fa-solid fa-bell"></i></a>
                <a href="user_profile.php"><i class="fa-solid fa-circle-user"></i></a>
            </div>
        </div>
    </nav>
    </header>
</body>
</html>
