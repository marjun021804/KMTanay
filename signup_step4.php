<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
    <link rel="stylesheet" href="style.css">
    <?php include('link.php'); ?>
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

        .verification-container {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
            width: 350px;
            text-align: center;
        }

        .verification-container img {
            width: 50px;
        }

        .upload-container {
            display: flex;
            justify-content: space-between;
            margin: 10px 0;
        }

        .upload-box {
            width: 48%;
            text-align: center;
        }

        .icon-large {
            font-size: 50px;
            color: gray;
        }

        .upload-box img {
            width: 150px;
            height: auto;
            border-radius: 5px;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
        }

        /* Overlay */
        .overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            justify-content: center;
            align-items: center;
            flex-direction: column;
            z-index: 9999;
        }

        .overlay-content {
            background: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            color: black;
        }

        .loader {
            border: 5px solid #f3f3f3;
            border-top: 5px solid #f48fb1;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
            margin: 10px auto;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        /* Camera Modal */
        .camera-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            justify-content: center;
            align-items: center;
            flex-direction: column;
            z-index: 9999;
        }

        .camera-modal video {
            width: 80%;
            max-width: 400px;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(255, 255, 255, 0.2);
        }

        .camera-buttons {
            margin-top: 20px;
            display: flex;
            gap: 10px;
        }
    </style>
</head>

<body>

    <!-- Fixed Header -->
    <header>
        <div class="header-container">
            <h2>Sign Up</h2>
        </div>
    </header>

    <!-- Signup Form Container -->
    <div class="signup-container">

        <!-- Left Side - Logo -->
        <div class="logo-container">
            <img src="/KMTanayAdmin/image/kmtanaylogo.png" alt="KM Tanay Logo">
        </div>

        <div class="verification-container">
            <img src="https://cdn-icons-png.flaticon.com/512/747/747376.png" alt="Verify Icon">
            <h2><strong>Verify Your Identity</strong></h2>
            <p>Please take a photo of a valid ID so we're sure it's you<br>
                • Photos must be in PNG and JPG<br>
                • Make sure your chosen ID is not blurred or cropped.</p>

            <div class="upload-container">
                <div class="upload-box">
                    <p>Photo of valid ID</p>
                    <i class="fa-regular fa-file-image icon-large" id="idIcon"></i>
                    <img id="idPreview" src="" style="display: none;">
                    <div class="button-container">
                        <button class="btn1" onclick="openCamera('idPreview', 'idIcon')">Capture ID</button>
                    </div>
                </div>

                <div class="upload-box">
                    <p>Take a selfie</p>
                    <i class="fa-regular fa-file-image icon-large" id="selfieIcon"></i>
                    <img id="selfiePreview" src="" style="display: none;">
                    <div class="button-container">
                        <button class="btn1" onclick="openCamera('selfiePreview', 'selfieIcon')">Capture Selfie</button>
                    </div>
                </div>
            </div>

            <button class="btn1" onclick="showOverlay()">Submit</button>
        </div>
    </div>

    <form id="imageUploadForm" method="POST" action="process_signup.php" style="display: none;">

        <input type="hidden" name="valid_id_base64" id="valid_id_base64">
        <input type="hidden" name="selfie_base64" id="selfie_base64">
    </form>

    <!-- Overlay -->
    <div id="overlay" class="overlay">
        <div class="overlay-content">
            <div class="loader"></div>
            <h2>Processing your submission...</h2>
            <p>Please wait while we verify your details. We will send a notification to your email once it is verified.
            </p>
        </div>
    </div>

    <!-- Camera Modal -->
    <div id="cameraModal" class="camera-modal">
        <video id="video" autoplay></video>
        <div class="camera-buttons">
            <button class="btn1" onclick="captureImage()">Take Picture</button>
            <button class="btn1" onclick="closeCamera()">Cancel</button>
        </div>
        <canvas id="canvas" style="display: none;"></canvas>
    </div>

    <script>
        let currentTargetId = "";
        let currentIconId = "";

        function openCamera(targetId, iconId) {
            currentTargetId = targetId;
            currentIconId = iconId;
            const video = document.getElementById('video');
            const cameraModal = document.getElementById('cameraModal');

            navigator.mediaDevices.getUserMedia({ video: true })
                .then((stream) => {
                    video.srcObject = stream;
                    cameraModal.style.display = 'flex';
                })
                .catch((err) => {
                    alert("Camera access denied.");
                });
        }

        function captureImage() {
            const video = document.getElementById('video');
            const canvas = document.getElementById('canvas');
            const context = canvas.getContext('2d');

            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            context.drawImage(video, 0, 0, canvas.width, canvas.height);

            const imageData = canvas.toDataURL("image/png");

            document.getElementById(currentTargetId).src = imageData;
            document.getElementById(currentTargetId).style.display = 'block';
            document.getElementById(currentIconId).style.display = 'none';

            closeCamera();
        }

        function closeCamera() {
            const video = document.getElementById('video');
            const cameraModal = document.getElementById('cameraModal');

            if (video.srcObject) {
                video.srcObject.getTracks().forEach(track => track.stop());
            }
            cameraModal.style.display = 'none';
        }

        function showOverlay() {
            const idImage = document.getElementById("idPreview").src;
            const selfieImage = document.getElementById("selfiePreview").src;

            if (!idImage || !idImage.startsWith("data:image")) {
                alert("Please capture your valid ID before submitting.");
                return;
            }

            if (!selfieImage || !selfieImage.startsWith("data:image")) {
                alert("Please capture your selfie before submitting.");
                return;
            }

            document.getElementById("overlay").style.display = "flex";

            // Set the hidden input values
            document.getElementById("valid_id_base64").value = idImage;
            document.getElementById("selfie_base64").value = selfieImage;

            // Submit the form after a short delay
            setTimeout(() => {
                document.getElementById("imageUploadForm").submit();
            }, 1000);
        }



    </script>
</body>

</html>