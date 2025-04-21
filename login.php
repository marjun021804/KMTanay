<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log In</title>
    <link rel="stylesheet" href="style.css">
    <?php include('link.php'); ?>


    <!-- Facebook SDK -->
    <script async defer crossorigin="anonymous" src="https://connect.facebook.net/en_US/sdk.js"></script>

    <!-- Google Platform API -->
    <script src="https://accounts.google.com/gsi/client" async defer></script>
    <style>
        /* Global Styles */
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
        }

        /* Header */
        header {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            background-color: #f48fb1;
            padding: 15px 20px;
            text-align: left;
            display: flex;
            align-items: center;
            box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.1);
        }

        .header-container {
            width: 100%;
            text-align: center;
        }

        header h2 {
            margin: 0;
            font-size: 40px;
            color: white;
            font-weight: bold;
        }

        /* Main Container */
        .signup-container {
            display: flex;
            align-items: center;
            justify-content: center;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
            max-width: 800px;
            width: 100%;
            margin: 130px auto 0;
            /* Increased margin to push the form down */
        }

        /* Left Side - Logo */
        .logo-container {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .logo-container img {
            width: 250px;
        }

        /* Right Side - Form */
        .form-container {
            flex: 1;
            padding: 20px;
            text-align: center;
        }

        .form-container h2 {
            font-size: 24px;
            margin-bottom: 20px;
        }

        .input-field {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
        }



        /* Social Buttons */
        .social-buttons {
            display: flex;
            justify-content: center;
            gap: 10px;
            /* Space between buttons */
            margin-top: 10px;
        }

        .social-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 8px;
            width: 40%;
            border: 1px solid #ccc;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            background: white;
        }

        .social-btn i {
            margin-right: 5px;
        }

        .social-btn.facebook {
            color: #1877F2;
            border-color: #1877F2;
        }

        .social-btn.google {
            color: #DB4437;
            border-color: #DB4437;
        }

        .social-btn:hover {
            background: #f1f1f1;
        }

        /* Login Link */
        .login-link {
            margin-top: 15px;
            font-size: 14px;
            color: #555;
        }

        .login-link a {
            color: #007BFF;
            text-decoration: none;
        }

        .login-link a:hover {
            text-decoration: underline;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .signup-container {
                flex-direction: column;
                text-align: center;
            }

            .logo-container img {
                width: 180px;
            }

            .social-buttons {
                flex-direction: column;
            }

            .social-btn {
                width: 100%;
                margin-bottom: 10px;
            }
        }
    </style>
</head>

<body>

    <!-- Fixed Header -->
    <header>
        <div class="header-container">
            <h2>Log In</h2>
        </div>
    </header>

    <!-- Signup Form Container -->
    <div class="signup-container">
        <!-- Left Side - Logo -->
        <div class="logo-container">
            <a href="index.php">
                <img src="/KMTanayAdmin/image/kmtanaylogo.png" alt="KM Tanay Logo">
            </a>
        </div>

        <!-- Right Side - Sign Up Form -->
        <div class="form-container">
            <h2><strong>Log In</strong></h2>

            <form action="login_process.php" method="POST">
                <input type="email" name="email" placeholder="Email" class="input-field" required>
                <input type="password" name="password" placeholder="Password" class="input-field" required>
                <button type="submit" class="btn1">Log In</button>
            </form>


            <div class="divider">
                <hr><span>OR</span>
                <hr>
            </div>

            <!-- Centered & Smaller Social Buttons -->
            <div class="social-buttons">
                <!-- Facebook Login Button -->
                <button class="social-btn facebook" onclick="loginWithFacebook()">
                    <i class="fa-brands fa-facebook"></i> Facebook
                </button>
                <div id="g_id_onload"
                    data-client_id="706544271646-2q54pek1897bqaabst0q47jhf6mnpme8.apps.googleusercontent.com"
                    data-callback="handleGoogleLogin" data-auto_prompt="false">
                </div>
                <div class="g_id_signin" data-type="standard"></div>
            </div>

            <!-- Enter as Guest Button -->
            <div style="margin-top: 20px;">
                <button class="btn1" onclick="window.location.href='index.php'">Enter as Guest</button>
            </div>

            <p class="login-link">
                Do not have an account? <a href="signup.php">Sign Up</a>
            </p>
        </div>
    </div>

    <script>
        // Facebook Login Function
        function loginWithFacebook() {
            FB.init({
                appId: '1598790274155456',
                cookie: true,
                xfbml: true,
                version: 'v17.0'
            });

            FB.login(function (response) {
                if (response.authResponse) {
                    FB.api('/me', { fields: 'name, email' }, function (response) {
                        console.log('Successful login:', response);
                        window.location.href = "index.php"; // Redirect on success
                    });
                } else {
                    alert('User cancelled login or did not fully authorize.');
                }
            }, { scope: 'email' });
        }

        // Google Login Function
        async function handleGoogleLogin(response) {
            try {
                const responsePayload = jwt_decode(response.credential);
                console.log("Google Login Success:", responsePayload);

                const email = responsePayload.email;

                // Send the email to the server to check if it is registered
                const formData = new FormData();
                formData.append('email', email);

                const serverResponse = await fetch('login_process_google.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await serverResponse.json();

                if (result.success) {
                    window.location.href = "homepage.php"; // Redirect to homepage on success
                } else {
                    alert(result.message); // Show error message if email is not registered
                }
            } catch (error) {
                console.error("Error decoding Google token:", error);
                alert("Google Login failed. Please try again.");
            }
        }
    </script>
    <!-- Google Platform API -->
    <script src="https://accounts.google.com/gsi/client" async defer></script>
    <script src="https://cdn.jsdelivr.net/npm/jwt-decode/build/jwt-decode.min.js"></script>

</body>

</html>