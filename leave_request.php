<?php
// Start the session
session_start();

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    $_SESSION['error'] = "Please log in to access the leave request page.";
    header("Location: login.php");
    exit();
}

// Connect to the database
$conn = new mysqli("localhost", "root", "", "attendanceapp");

if ($conn->connect_error) {
    $_SESSION['error'] = "Database connection failed. Please try again later.";
    header("Location: login.php"); // Redirect with an error message
    exit();
}

// Get the logged-in user's username
$username = $_SESSION['username'];

// Handle form submission for leave request
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $leave_reason = $_POST['leave_reason'] ?? '';  // Set to empty string if not set
    $start_date = $_POST['leave_start_date'] ?? ''; 
    $end_date = $_POST['leave_end_date'] ?? ''; 
    
    if (empty($leave_reason) || empty($start_date) || empty($end_date)) {
        $_SESSION['error'] = "All fields are required.";
    } else {
        // Prepare and execute insert query
        $status = 'Pending';  // Default status
        $stmt = $conn->prepare("INSERT INTO leave_requests (username, leave_reason, leave_start_date, leave_end_date, status) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $username, $leave_reason, $start_date, $end_date, $status);

        if ($stmt->execute()) {
            $_SESSION['success'] = "Your leave request has been submitted.";
        } else {
            $_SESSION['error'] = "Failed to submit your leave request. Please try again.";
        }
        $stmt->close();
    }
}


// Retrieve the user's leave requests
$sql = "SELECT * FROM leave_requests WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave Request</title>
	<link rel="icon" type="image/jpeg" href="logo.jpg">
    <style>
    body {
        font-family: 'Arial', sans-serif;
        background-color: #f4f7fc;
        margin: 0;
        padding: 0;
        display: flex;
    }

    .sidebar {
        width: 250px;
        background-color: #333;
        color: white;
        padding-top: 20px;
        position: fixed;
        height: 100%;
        box-shadow: 2px 0 5px rgba(0, 0, 0.1);
    }

    .sidebar a {
        color: white;
        padding: 20px;
        text-decoration: none;
        display: block;
        font-size: 18px;
        transition: background-color 0.3s ease;
    }

    .sidebar a:hover {
        background-color: #575757;
    }

    .content {
        margin-left: 250px;
        flex: 1;
        padding: 20px;
        background-color: #fff;
        border-radius: 10px;
        margin-top: 20px;
    }

    .card {
        background-color: #fff;
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0, 0, 0.1);
        padding: 25px;
        margin-bottom: 20px;
        transition: box-shadow 0.3s ease;
    }

    .card:hover {
        box-shadow: 0 8px 16px rgba(0, 0, 225);
    }

    h2, h3 {
        color: #333;
        font-size: 28px;
        font-weight: 600;
    }

    form input, form textarea, form button {
        width: 100%;
        padding: 15px;
        margin-bottom: 15px;
        border-radius: 10px;
        border: 1px solid #ddd;
        font-size: 16px;
        box-sizing: border-box;
    }

    form textarea {
        height: 150px;
    }

    form input:focus, form textarea:focus {
        outline: none;
        border-color: #4c9ff5;
    }

    button {
        background-color: #4c9ff5;
        color: white;
        border: none;
        cursor: pointer;
        font-size: 16px;
        padding: 15px;
        border-radius: 10px;
        transition: background-color 0.3s ease;
    }

    button:hover {
        background-color: #357ab7;
    }

    .message {
        text-align: center;
        margin: 20px 0;
        padding: 15px;
        border-radius: 5px;
        font-size: 16px;
    }

    .error {
        color: #721c24;
        background-color: #f8d7da;
        border: 1px solid #f5c6cb;
    }

    .success {
        color: #155724;
        background-color: #d4edda;
        border: 1px solid #c3e6cb;
    }

    table {
        width: 100%;
        margin-top: 20px;
        border-collapse: collapse;
        text-align: center;
        font-size: 16px;
    }

    th, td {
        padding: 15px;
        border: 1px solid #ddd;
        text-align: center;
        font-weight: 500;
    }

    th {
        background-color: #4c9ff5;
        color: white;
        text-transform: uppercase;
    }

    tr:nth-child(even) {
        background-color: #f9f9f9;
    }

    tr:hover {
        background-color: #f1f1f1;
    }

    .status {
        font-weight: bold;
    }

    .pending {
        color: orange;
    }

    .approved {
        color: green;
    }

    .rejected {
        color: red;
    }

    .back-btn-container {
        text-align: center;
        margin-top: 30px;
    }

    .back-btn {
        display: inline-block;
        text-decoration: none;
        padding: 15px 25px;
        border: none;
        border-radius: 10px;
        font-size: 18px;
        color: #fff;
        background-color: #4c9ff5;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }

    .back-btn:hover {
        background-color: #357ab7;
    }
/* Flex container for date inputs */
.date-container {
    display: flex;
    justify-content: space-between;
    gap: 20px; /* Adds space between the two inputs */
}

/* Make sure the date inputs take equal width and increase their size */
.date-container input {
    width: 100%;
    padding: 12px; /* Increase padding to make the box larger */
    font-size: 16px; /* Increase font size for better visibility */
    border-radius: 8px; /* Optional: To maintain the rounded corners */
    border: 1px solid #ddd; /* Optional: To style the border */
}

/* Optional: Increase the size of the input text */
input[type="date"] {
    font-size: 18px; /* Adjust this to increase the text size inside the box */
}


</style>

</head>

<body>

    <!-- Sidebar -->
    <div class="sidebar">
        <a href="user_dashboard.php">Dashboard</a>
        <a href="leave_request.php">Leave Request</a>
        <a href="login.php">Logout</a>
    </div>

    <!-- Main Content Area -->
    <div class="content">
        <div class="card">
            <h2>Leave Request</h2>

            <!-- Success/Error Message -->
            <?php if (isset($_SESSION['success'])) { ?>
                <div class="message success">
                    <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                </div>
            <?php } elseif (isset($_SESSION['error'])) { ?>
                <div class="message error">
                    <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                </div>
            <?php } ?>

            <!-- Leave Request Form -->
            <form method="POST" action="leave_request.php">
    <label for="leave_reason">Reason for Leave:</label>
    <textarea name="leave_reason" id="leave_reason" required></textarea><br>

    <div class="date-container">
        <div>
            <label for="leave_start_date">Leave Start Date (From):</label><br>
            <input type="date" name="leave_start_date" id="leave_start_date" required><br>
        </div>
        <div>
            <label for="leave_end_date">Leave End Date (To):</label><br>
            <input type="date" name="leave_end_date" id="leave_end_date" required><br>
        </div>
    </div>

    <button type="submit">Submit Leave Request</button>
</form>

        </div>

        <div class="card">
            <h3>Your Leave Requests</h3>

            <!-- Leave Request History -->
            <table>
                <thead>
                    <tr>
                        <th>Leave Start Date (From)</th>
                        <th>Leave End Date (To)</th>
                        <th>Reason</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $statusClass = '';
                            switch ($row['status']) {
                                case 'Pending':
                                    $statusClass = 'pending';
                                    break;
                                case 'Approved':
                                    $statusClass = 'approved';
                                    break;
                                case 'Rejected':
                                    $statusClass = 'rejected';
                                    break;
                            }
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row['leave_start_date']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['leave_end_date']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['leave_reason']) . "</td>";
                            echo "<td class='status " . $statusClass . "'>" . htmlspecialchars($row['status']) . "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='4'>No leave requests found.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <div class="back-btn-container">
            <a href="user_dashboard.php" class="back-btn">Back to Dashboard</a>
        </div>

    </div>

</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
