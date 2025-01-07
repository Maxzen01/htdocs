<?php
// Start session and check if the user is an admin
session_start();
if (!isset($_SESSION['admin']) || $_SESSION['admin'] != true) {
    header("Location: login.php");
    exit();
}

// Database connection (db.php)
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "attendanceapp";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch the employee details
if (isset($_GET['id'])) {
    $employee_id = $_GET['id'];

    $sql = "SELECT * FROM signup WHERE employee_id = '$employee_id'";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        $employee = $result->fetch_assoc();
    } else {
        echo "Employee not found.";
        exit();
    }
}

// Handle form submission for editing employee details
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_employee_id = $_POST['employee_id'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $role = $_POST['role'];
    $new_password = $_POST['password'];

    // Hash the new password before storing it in the database
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

    // Update query
    $update_sql = "UPDATE signup SET employee_id = '$new_employee_id', username = '$username', email = '$email', role = '$role', password = '$hashed_password' WHERE employee_id = '$employee_id'";

    if ($conn->query($update_sql) === TRUE) {
        echo "<p>Employee details updated successfully!</p>";
    } else {
        echo "<p>Error: " . $conn->error . "</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Employee</title>
    <style>
        /* Global Styles */
        body {
            font-family: lucida;
            background-color: #f0f2f5;
            margin: 0;
            padding: 0;
        }

        .container {
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0.1);
            padding: 30px;
            width: 1400px;  /* Set container width to 1400px */
            margin: 50px auto;  /* Center align the container */
        }

        h2 {
            margin-bottom: 20px;
            font-size: 24px;
            font-weight: bold;
            color: #333;
            text-align: center; /* Center the h2 heading */
        }

        label {
            display: block;
            margin: 10px 0 5px;
            font-size: 16px;
            color: #555;
        }

        input, select {
            width: 100%;
            padding: 12px;
            margin: 8px 0 18px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 16px;
        }

        input[type="password"] {
            font-size: 14px;
        }

        button {
            width: 100%;
            padding: 12px;
            background-color: #4CAF50;
            color: #fff;
            font-size: 18px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #45a049;
        }

        .back-btn-container {
            text-align: center; /* Center the back button */
            margin-top: 20px;  /* Add space above the back button */
        }

        .back-btn {
            padding: 10px 20px;
            background-color: #f44336;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .back-btn:hover {
            background-color: #e53935;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Edit Employee Details</h2>
    <form method="POST">
        <!-- Employee ID Field (Editable) -->
        <label for="employee_id">Employee ID:</label>
        <input type="text" name="employee_id" value="<?php echo htmlspecialchars($employee['employee_id']); ?>" required>

        <label for="username">Username:</label>
        <input type="text" name="username" value="<?php echo htmlspecialchars($employee['username']); ?>" required>

        <label for="email">Email:</label>
        <input type="email" name="email" value="<?php echo htmlspecialchars($employee['email']); ?>" required>

        <label for="role">Role:</label>
        <select name="role">
            <option value="Employee" <?php if ($employee['role'] == 'Employee') echo 'selected'; ?>>Employee</option>
            <option value="Manager" <?php if ($employee['role'] == 'Manager') echo 'selected'; ?>>Manager</option>
            <option value="Admin" <?php if ($employee['role'] == 'Admin') echo 'selected'; ?>>Admin</option>
        </select>

        <!-- Password Field (Editable) -->
        <label for="password">New Password:</label>
        <input type="password" name="password" placeholder="Enter new password" required>

        <button type="submit">Update Details</button>
    </form>

    <!-- Centered Back Button -->
    <div class="back-btn-container">
        <a href="admin.php" class="back-btn">Back</a>
    </div>
</div>

</body>
</html>
