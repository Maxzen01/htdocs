<?php
// Start the session
session_start();

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    $_SESSION['error'] = "Please log in to access your dashboard.";
    header("Location: login.php"); // Redirect to login page
    exit();
}

// Connect to the database
$conn = new mysqli("localhost", "root", "", "attendanceapp");

if ($conn->connect_error) {
    $_SESSION['error'] = "Database connection failed. Please try again later.";
    header("Location: login.php"); // Redirect with an error message
    exit();
}

// Get attendance data for the logged-in user
$username = $_SESSION['username'];

// Handle search query
$search = isset($_GET['search']) ? $_GET['search'] : '';  // Search action (login/logout)

$sql = "SELECT * FROM attendance WHERE username = ?";

if (!empty($search)) {
    $sql .= " AND action LIKE ?";
}

$stmt = $conn->prepare($sql);
if (!empty($search)) {
    $searchTerm = "%" . $search . "%";
    $stmt->bind_param("ss", $username, $searchTerm);
} else {
    $stmt->bind_param("s", $username);
}
$stmt->execute();
$result = $stmt->get_result();

// Fetch leave request status and response
$leaveSql = "SELECT * FROM leave_requests WHERE username = ? ORDER BY request_date DESC LIMIT 1"; // Get the latest leave request
$leaveStmt = $conn->prepare($leaveSql);
$leaveStmt->bind_param("s", $username);
$leaveStmt->execute();
$leaveResult = $leaveStmt->get_result();
$leaveRequest = $leaveResult->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    <style>
        /* General Styles */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f7fc;
            margin: 0;
            padding: 0;
            display: flex;
        }

        /* Sidebar */
        .sidebar {
            width: 250px; /* Increased sidebar width */
            background-color: #333;
            color: white;
            padding-top: 20px;
            position: fixed;
            height: 100%;
        }

        .sidebar a {
            color: white;
            padding: 20px; /* Increased padding */
            text-decoration: none;
            display: block;
            font-size: 18px;
        }

        .sidebar a:hover {
            background-color: #575757;
        }

        /* Main Content Area */
        .content {
            margin-left: 250px; /* Offset the content to the right */
            flex: 1; /* Allow content to take remaining space */
            padding: 20px;
            background-color: #fff;
            border-radius: 10px;
            margin-top: 20px;
        }

        h2, h3 {
            text-align: center;
            color: #333;
        }

        .message {
            text-align: center;
            margin: 20px 0;
            padding: 10px;
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
            padding: 10px;
            border: 1px solid #ddd;
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

        /* Button Styles */
        .logout-btn, .back-btn, .leave-btn {
            display: inline-block;
            margin-top: 20px;
            text-decoration: none;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            color: #fff;
            background-color: #4c9ff5;
            text-align: center;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .logout-btn:hover, .back-btn:hover, .leave-btn:hover {
            background-color: #357ab7;
        }

        .back-btn-container {
            text-align: center; /* Centering the back button */
        }

        /* Photo Column */
        .photo img {
            max-width: 100px;
            border-radius: 5px;
        }

        .leave-status {
            margin-top: 20px;
            padding: 10px;
            border: 1px solid #ddd;
            background-color: #e7f5e7;
            text-align: center;
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
        <div class="container">
            <h2>Welcome, <?php echo htmlspecialchars($username); ?></h2>
            <h3>Your Login/Logout Activities</h3>

            <!-- Search Form -->
            <form method="GET" style="text-align: center;">
                <input type="text" name="search" placeholder="Search by action (login/logout)" value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit">Search</button>
            </form>

            <!-- Display the user's attendance data in a table -->
            <table>
                <thead>
                    <tr>
                        <th>Action</th>
                        <th>Time</th>
                        <th>Photo</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row['action']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['time']) . "</td>";
                            echo "<td class='photo'>";
                            if (!empty($row['photo_url'])) {
                                echo "<img src='" . htmlspecialchars($row['photo_url']) . "' alt='Photo'>";
                            } else {
                                echo "No photo";
                            }
                            echo "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='3'>No attendance records found.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>

            


            <div class="back-btn-container">
                <a href="attendance.php" class="back-btn">Back</a>
            </div>

        </div>
    </div>

</body>

</html>

<?php
$stmt->close();
$leaveStmt->close();
$conn->close();
?>
