<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");  // Redirect to login page if not logged in
    exit();
}

$username = $_SESSION['username'];  // Get the logged-in username

// Set the timezone
date_default_timezone_set('Asia/Kolkata');

// Include the database connection
$conn = new mysqli("localhost", "root", "", "attendanceapp");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get today's date
$today = date("Y-m-d");

// Initialize message
$message = "";

// Check if the user has already taken attendance twice today
$sql = "SELECT COUNT(*) AS count FROM attendance WHERE username = ? AND DATE(time) = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $username, $today);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$attendanceCount = $row['count'];
$stmt->close();

// Restrict attendance if already taken twice
if ($attendanceCount >= 2) {
    $message = "You have already marked attendance for today.";
} else {
    // Insert attendance data into the database when the form is submitted
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $action = $_POST["action"];
        $photo_url = $_POST["photo_url"];
        $time = date("Y-m-d H:i:s"); // Get the current time

        // Decode the base64 image data
        if (!empty($photo_url)) {
            $image_parts = explode(";base64,", $photo_url);
            $image_base64 = base64_decode($image_parts[1]);
            $file_name = "uploads/" . uniqid() . ".png"; // Generate unique file name

            // Save the image to the 'uploads' folder
            if (file_put_contents($file_name, $image_base64)) {
                // Insert attendance record into the database
                $sql = "INSERT INTO attendance (username, action, time, photo_url) VALUES (?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssss", $username, $action, $time, $file_name);

                if ($stmt->execute()) {
                    $message = "Attendance recorded successfully!";
                    $attendanceCount++; // Increment the count
                } else {
                    $message = "Error recording attendance: " . $stmt->error;
                }

                $stmt->close();
            } else {
                $message = "Failed to save the image. Please try again.";
            }
        } else {
            $message = "No photo captured. Please try again.";
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Page</title>
    <style>
        /* General Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: lucida;
        }

        body {
            background: white;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .container {
            width: 100%;
            max-width: 600px;
            background: #fff;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0);
            text-align: center;
            position: relative;
            transition: box-shadow 0.3s ease;
        }

        .container:hover {
            box-shadow: 0 0 40px 10px rgba(0, 0, 255);
        }

        h1 {
            font-size: 24px;
            color: #333;
        }

        button {
            margin: 10px 5px;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            background-color: #4c9ff5;
            color: white;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #357ab7;
        }

        #camera {
            margin-top: 20px;
        }

        video, canvas {
            border: 2px solid #ddd;
            border-radius: 5px;
            margin-top: 10px;
        }

        .back-btn {
            margin-top: 20px;
            text-decoration: none;
            display: inline-block;
            background-color: #28a745;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
        }

        .back-btn:hover {
            background-color: #218838;
        }

        /* Message Styles */
        #message {
            font-size: 16px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Welcome, <?php echo htmlspecialchars($username); ?>! Please mark your attendance.</h1>

        <!-- Message container -->
        <?php if (!empty($message)) { ?>
            <p id="message" style="color: green; font-weight: bold;">
                <?php echo htmlspecialchars($message); ?>
            </p>
        <?php } ?>

        <form method="POST" action="">
    <label><strong>Choose Action:</strong></label><br>
    <button type="button" onclick="takePhoto('login')" 
        <?php if ($attendanceCount >= 2) echo 'disabled'; ?>>Check_in</button>
    <button type="button" onclick="takePhoto('logout')" 
        <?php if ($attendanceCount >= 2) echo 'disabled'; ?>>Check_out</button><br><br>

    <input type="hidden" name="action" id="action">
    <input type="hidden" name="photo_url" id="photo_url">

    <div id="camera" style="display:none;">
        <video id="video" width="320" height="240" autoplay></video><br>
        <button type="button" onclick="takeSnapshot()">Take Snapshot</button>
        <canvas id="canvas" width="320" height="240" style="display:none;"></canvas><br>
    </div>

    <p id="message"><?php echo htmlspecialchars($message); ?></p>

    <button type="submit" <?php if ($attendanceCount >= 2) echo 'disabled'; ?>>Submit Attendance</button>
</form>


        <a href="user_dashboard.php" class="back-btn">Go to Dashboard</a>
		<div class="back-btn-container">
        <a href="login.php" class="back-btn">Back</a>
    </div>
    </div>
	

    <script>
        function takePhoto(action) {
            // Set the action (login or logout)
            document.getElementById("action").value = action;

            // Show the camera for taking the photo
            document.getElementById("camera").style.display = 'block';
            document.getElementById("message").innerText = ""; // Clear any previous messages
            startCamera();
        }

        function startCamera() {
            const video = document.getElementById('video');

            // Request camera access
            navigator.mediaDevices.getUserMedia({ video: true })
                .then(function (stream) {
                    video.srcObject = stream;
                    video.play();
                })
                .catch(function (err) {
                    document.getElementById('camera').style.display = 'none';
                    const message = "Camera access is required but not available: " + err.message;
                    document.getElementById('message').innerHTML = message;
                    document.getElementById('message').style.color = "red";
                    console.error(message);
                });
        }

        function takeSnapshot() {
            const video = document.getElementById('video');
            const canvas = document.getElementById('canvas');
            const context = canvas.getContext('2d');

            // Ensure video is active
            if (!video.srcObject) {
                document.getElementById('message').innerHTML = "Camera is not active. Please try again.";
                document.getElementById('message').style.color = "red";
                return;
            }

            // Draw the video frame to the canvas
            context.drawImage(video, 0, 0, canvas.width, canvas.height);

            // Get the image data as a base64 string
            const photoUrl = canvas.toDataURL('image/png');
            document.getElementById('photo_url').value = photoUrl;

            // Notify the user
            document.getElementById('message').innerHTML = "Photo captured successfully!";
            document.getElementById('message').style.color = "green";

            // Hide the camera
            document.getElementById('camera').style.display = 'none';
        }

        function recordAttendance() {
            const action = document.getElementById('action').value;
            const photoUrl = document.getElementById('photo_url').value;

            if (!action || !photoUrl) {
                document.getElementById('message').innerText = "Please take a photo before submitting!";
                return false; // Prevent form submission
            }

            // Simulate recording attendance (Replace this with server-side processing)
            document.getElementById('message').innerText = 
                "Photo captured successfully! Attendance recorded successfully!";
    return true; // Allow form submission
}

    </script>
</body>
</html>
