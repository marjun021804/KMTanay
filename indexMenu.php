<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>KM Tanay Navbar</title>
    <link rel="stylesheet" href="styles.css">
   <?php include('link.php');?> 
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
    padding-left: 20px;
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

/* Header text alignment */
.header-container {
    display: flex;
    align-items: center;
    padding: 10px 20px;
    width: 100%;
}

.header-container h2 {
    margin-left: 10px;
    text-align: left;
    color: white;
    flex-grow: 1;
    text-decoration: none; /* Remove underline */
}

.auth-links a i:hover {
    
    color: black; /* Black hover effect for the cart icon */
}
</style>

<body>
<header>
    <div class="header-container">
        <a href="index.php">
            <img src="/KMTanayAdmin/image/kmtanaylogo.png" alt="KM Tanay Logo" class="logo">
        </a>
        <a href="index.php" style="text-decoration: none;"> <!-- Removed underline -->
            <h2>WELCOME TO KM TANAY!</h2>
        </a>
        <div class="auth-links">
            <a href="cart.php" style="margin-right: 10px; color: white; transition: color 0.3s ease;">
                <i class="fas fa-shopping-cart" style="font-size: 30px;"></i> <!-- Cart icon size set to 30px -->
            </a>
            <a href="signup.php">Sign Up</a> | <a href="login.php">Log In</a>
        </div>
    </div>
</header>
</body>
</html>
