<?php
// login.php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $conn = new mysqli("localhost", "root", "", "attendanceapp");

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $username = $_POST["username"];
    $password = $_POST["password"];

    // Check the credentials in the signup table
    $sql = "SELECT * FROM signup WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Verify the password
        if (password_verify($password, $user['password'])) {
            // Store the username in session
            $_SESSION['username'] = $username;

            // Redirect based on user role
            if ($user['role'] == 'Admin') {
                $_SESSION['admin'] = true;
                header("Location: admin.php"); // Redirect to admin page
                exit();
            } elseif ($user['role'] == 'Manager') {
                $_SESSION['manager'] = true;
                header("Location: manager.php"); // Redirect to manager page
                exit();
            } else {
                header("Location: attendance.php"); // Redirect to user attendance page
                exit();
            }
        } else {
            $error_message = "Invalid username or password.";
        }
    } else {
        $error_message = "Invalid username or password.";
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
	<link rel="icon" type="image/jpeg" href="logo.jpg">
    <style>
        /* General Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: lucida, times new roman;
        }

        body {
            background: white;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        /* Centered Container */
        .login-container {
            width: 100%;
            max-width: 400px;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        /* Login Box */
        .login-box {
            background: #fff;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0.1);
            text-align: center;
            width: 100%;
            position: relative;
            transition: box-shadow 0.3s ease;
        }

        .login-box:hover {
            box-shadow: 0 0 40px 10px rgba(0, 0, 255);
        }

        .login-box h2 {
            margin-bottom: 20px;
            color: #333;
            font-size: 26px;
            font-weight: 700;
            text-transform: uppercase;
        }

        /* Input Fields */
        .input-group {
            margin-bottom: 20px;
            position: relative;
        }

        .input-group input {
            width: 100%;
            padding: 12px;
            font-size: 16px;
            border: 2px solid #ccc;
            border-radius: 10px;
            outline: none;
            transition: 0.3s ease;
        }

        .input-group input:focus {
            border-color: #4c9ff5;
            box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);
        }

        /* Submit Button */
        .login-btn {
            width: 100%;
            padding: 12px;
            background-color: #4c9ff5;
            color: #fff;
            font-size: 18px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            margin-top: 10px;
        }

        .login-btn:hover {
            background-color: #357ab7;
        }

        /* Forgot Password Link */
        .forgot-password {
            margin-top: 15px;
        }

        .forgot-password a {
            color: #4c9ff5;
            font-size: 14px;
            text-decoration: none;
        }

        .forgot-password a:hover {
            text-decoration: underline;
        }

        /* Error Message */
        .error {
            color: red;
            font-size: 14px;
            margin-top: 10px;
        }
    </style>
</head>
<body>

    <div class="login-container">
        <div class="login-box">
            <h2>Login</h2>
            <form method="POST" action="login.php">
                <div class="input-group">
                    <input type="text" name="username" placeholder="Username" required>
                </div>
                <div class="input-group">
                    <input type="password" name="password" placeholder="Password" required>
                </div>
                <button type="submit" class="login-btn">Login</button>
                <?php if (isset($error_message)) { echo "<p class='error'>$error_message</p>"; } ?>
            </form>
        </div>
    </div>

</body>
</html>
