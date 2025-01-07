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

// Handle page-specific operations (Dashboard, View Attendance, Add Employee, etc.)
$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard'; // Default to dashboard

// Fetch stats for the dashboard
$userCountResult = $conn->query("SELECT COUNT(DISTINCT username) AS total FROM signup");
$userCount = $userCountResult->fetch_assoc()['total'];

$attendanceCountResult = $conn->query("SELECT COUNT(*) AS total FROM attendance");
$attendanceCount = $attendanceCountResult->fetch_assoc()['total'];

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <style>
        /* General Styles */
body {
    font-family: Arial, sans-serif;
    background-color: #f4f7fc;
    margin: 0;
    padding: 0;
}

/* Sidebar Styles */
.sidebar {
    width: 200px;
    background-color: #2c3e50;
    color: white;
    padding: 20px;
    position: fixed;
    height: 100%;
    top: 0;
    left: 0;
    transition: transform 0.3s ease-in-out;
}

.sidebar ul {
    list-style-type: none;
    padding: 0;
}

.sidebar ul li {
    padding: 10px 0;
}

.sidebar ul li a {
    color: white;
    text-decoration: none;
    display: block;
    padding: 10px;
}

.sidebar ul li a:hover {
    background-color: #34495e;
}

/* Mobile Sidebar Toggle */
.sidebar-toggle {
    display: none;
    background-color: #2c3e50;
    color: white;
    padding: 10px 20px;
    cursor: pointer;
    position: fixed;
    top: 10px;
    left: 10px;
    z-index: 1000;
    border-radius: 5px;
}

/* Main Content */
.main-content {
    margin-left: 220px;
    padding: 20px;
    text-align: center;
}

/* Dashboard Stats */
.stat-box-container {
    display: flex;
    justify-content: center;
    flex-wrap: wrap;
    gap: 20px;
}

.stat-box {
    background-color: #3498db;  /* Blue color */
    color: white;
    padding: 30px;
    width: 200px;
    text-align: center;
    border-radius: 20px;
    box-shadow: 0 4px 8px rgba(0, 0, 0.1);
}

.stat-box h3 {
    margin: 0;
}

.stat-box p {
    font-size: 24px;
}

/* Table */
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}

table th, table td {
    padding: 10px;
    text-align: center;
    border: 1px solid #ddd;
}

table th {
    background-color: #3498db;  /* Blue color */
    color: white;
}

table tr:nth-child(even) {
    background-color: #f9f9f9;
}

table tr:hover {
    background-color: #f1f1f1;
}

/* Forms */
.form-container {
    max-width: 600px;
    margin: 0 auto;
    background-color: white;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 4px 8px rgba(0, 0, 0.1);
}

.form-container form {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.form-container input,
.form-container select,
.form-container button {
    padding: 10px;
    font-size: 16px;
    border: 1px solid #ddd;
    border-radius: 5px;
}

.form-container button {
    background-color: #3498db;  /* Blue color */
    color: white;
    cursor: pointer;
}

.form-container button:hover {
    background-color: #2980b9;
}

/* Media Queries for Mobile */
@media (max-width: 768px) {
    .sidebar {
        transform: translateX(-100%);
    }

    .sidebar.open {
        transform: translateX(0);
    }

    .sidebar-toggle {
        display: block;
    }

    .main-content {
        margin-left: 0;
        padding: 10px;
    }

    .stat-box {
        width: 100%;
    }

    table {
        font-size: 12px;
    }

    table th, table td {
        padding: 5px;
    }

    table {
        display: block;
        overflow-x: auto;
        white-space: nowrap;
    }
	
}
/* Remove underline and link styles */
a {
    text-decoration: none; /* Remove underline */
    color: inherit; /* Inherit the color from the parent element */
    cursor: pointer; /* Change cursor to pointer when hovering over the link */
}

/* Add button-like styles for Edit links */
a.edit-btn {
    background-color: #3498db;  /* Blue color */
    color: white;
    padding: 10px 20px;
    border-radius: 5px;
    text-align: center;
    display: inline-block;
}

a.edit-btn:hover {
    background-color: #2980b9; /* Darker blue for hover effect */
}
/* Download Button Design */
.download-btn {
    text-decoration: none; /* Remove link underline */
}

.download-btn .btn {
    background-color: #28a745;  /* Green color */
    color: white;
    padding: 10px 20px;
    font-size: 16px;
    border-radius: 5px;
    text-align: center;
    border: none;  /* Remove border from button */
    cursor: pointer;  /* Show pointer on hover */
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);  /* Add shadow effect */
    transition: background-color 0.3s ease;  /* Smooth transition */
}

.download-btn .btn:hover {
    background-color: #218838;  /* Darker green for hover effect */
}

.download-btn .btn:active {
    transform: scale(0.98);  /* Slight shrink on click */
}

.download-btn .btn:focus {
    outline: none;  /* Remove focus outline */
}

    /* Modal styles */
    .modal {
        display: none;
        position: fixed;
        z-index: 1;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgb(0, 0, 0);
        background-color: rgba(0, 0, 0, 0.9);
        padding-top: 60px;
    }

    /* Modal content (the image) */
    .modal-content {
        margin: auto;
        display: block;
        max-width: 80%;
        max-height: 80%;
        animation-name: zoomIn;
        animation-duration: 0.5s;
    }

    /* Caption text */
    #caption {
        text-align: center;
        color: #ccc;
        font-size: 20px;
        padding: 10px 0;
    }

    /* Close button */
    .close {
        position: absolute;
        top: 15px;
        right: 35px;
        color: #fff;
        font-size: 40px;
        font-weight: bold;
        cursor: pointer;
    }

    .close:hover,
    .close:focus {
        color: #bbb;
        text-decoration: none;
        cursor: pointer;
    }

    /* Animation for zoom effect */
    @keyframes zoomIn {
        from {
            transform: scale(0);
        }
        to {
            transform: scale(1);
        }
    }


    </style>
</head>
<body>

<div class="sidebar-toggle" onclick="toggleSidebar()">☰ Menu</div>

<div class="sidebar">
    <ul>
        <li><a href="?page=dashboard">Dashboard</a></li>
        <li><a href="?page=view_attendance">View Attendance</a></li>
        <li><a href="?page=add_employee">Add Employee</a></li>
        <li><a href="?page=employee_details">Employee Details</a></li>
        <li><a href="login.php" class="logout-btn">Logout</a></li>
    </ul>
</div>
<br>
<br>
<div class="main-content">
    <!-- Page Content Here -->
    <?php if ($page == 'dashboard') { ?>
        <h2>Admin Dashboard</h2>

        <div class="stat-box-container">
            <div class="stat-box">
			<a href="admin.php?page=employee_details" style="text-decoration: none; color: inherit;">
                <h3>Total Employees</h3>
                <p><?php echo $userCount; ?></p>
            </div>

            <div class="stat-box">
			<a href="admin.php?page=view_attendance" style="text-decoration: none; color: inherit;">
                <h3>Total Attendance Records</h3>
                <p><?php echo $attendanceCount; ?></p>
            </div>
        </div>

    <?php } elseif ($page == 'view_attendance') { ?>
        
       <h2>View Attendance</h2>

<?php
// Handle search and filter queries
$search = isset($_GET['search']) ? $_GET['search'] : '';
$dateFilter = isset($_GET['date']) ? $_GET['date'] : '';

// Modify SQL query for search and date filters
$loginSql = "SELECT * FROM attendance WHERE action = 'login' AND username LIKE '%$search%'";
$logoutSql = "SELECT * FROM attendance WHERE action = 'logout' AND username LIKE '%$search%'";

// Apply date filter
if (!empty($dateFilter)) {
    $loginSql .= " AND DATE(time) = '$dateFilter'";
    $logoutSql .= " AND DATE(time) = '$dateFilter'";
}

// Fetch login and logout data
$loginResult = $conn->query($loginSql);
$logoutResult = $conn->query($logoutSql);
?>

<form method="GET">
    <input type="hidden" name="page" value="view_attendance">
    <input type="text" name="search" placeholder="Search by username..." value="<?php echo htmlspecialchars($search); ?>">
    <input type="date" name="date" value="<?php echo htmlspecialchars($dateFilter); ?>">
    <button type="submit">Search</button>
</form>

<!-- Download Button with Date Range Picker -->
<a href="#" class="download-btn" onclick="showDateRangePicker()">
    <button class="btn download-btn">Download Data</button>
</a>

<!-- Date Range Picker Modal -->
<div id="date-range-picker-modal" style="display:none;">
    <form method="GET" action="download.php">
        <label for="from-date">Select Date Range:</label>
        <input type="date" id="from-date" name="from-date">
        <input type="date" id="to-date" name="to-date">
        <button type="submit">Download</button>
        <button type="button" onclick="closeDateRangePicker()">Cancel</button>
    </form>
</div>
<!-- Modal for viewing image -->
<div id="imageModal" class="modal">
    <span class="close" onclick="closeModal()">&times;</span>
    <img class="modal-content" id="modalImage">
    <div id="caption"></div>
</div>


<!-- Login Attendance Table -->
<h3>Login Details</h3>
<table>
    <thead>
        <tr>
            <th>Username</th>
            <th>Action</th>
            <th>Time</th>
            <th>Photo</th>
        </tr>
    </thead>
    <tbody>
        <?php
        if ($loginResult->num_rows > 0) {
            while ($row = $loginResult->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['username']) . "</td>";
                echo "<td>" . htmlspecialchars($row['action']) . "</td>";
                echo "<td>" . htmlspecialchars($row['time']) . "</td>";
                echo "<td>
    <img src='" . htmlspecialchars($row['photo_url']) . "' alt='User Photo' width='50' onclick='openModal(this)'>
</td>";

                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='4'>No login records found.</td></tr>";
        }
        ?>
    </tbody>
</table>

<!-- Logout Attendance Table -->
<h3>Logout Details</h3>
<table>
    <thead>
        <tr>
            <th>Username</th>
            <th>Action</th>
            <th>Time</th>
            <th>Photo</th>
        </tr>
    </thead>
    <tbody>
        <?php
        if ($logoutResult->num_rows > 0) {
            while ($row = $logoutResult->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['username']) . "</td>";
                echo "<td>" . htmlspecialchars($row['action']) . "</td>";
                echo "<td>" . htmlspecialchars($row['time']) . "</td>";
                echo "<!-- Image inside the table -->
<td>
    <img src='" . htmlspecialchars($row['photo_url']) . "' alt='User Photo' width='50' onclick='openModal(this)'>
</td>";

                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='4'>No logout records found.</td></tr>";
        }
        ?>
    </tbody>
</table>

<!-- JavaScript for Date Range Picker -->
<script>
    function checkNewDay() {
        // Get the current date
        const currentDate = new Date().toDateString();
        
        // Get the stored date from localStorage
        const storedDate = localStorage.getItem('lastVisitDate');

        // If the current date is different from the stored date, reload the page
        if (storedDate !== currentDate) {
            // Store the new date
            localStorage.setItem('lastVisitDate', currentDate);

            // Calculate the time difference to midnight
            const now = new Date();
            const nextMidnight = new Date(now.getFullYear(), now.getMonth(), now.getDate() + 1, 0, 0, 0);
            const timeUntilMidnight = nextMidnight - now;

            // Reload the page at midnight
            setTimeout(() => {
                location.reload();
            }, timeUntilMidnight);
        }
    }

    // Call the function when the page loads
    checkNewDay();

function showDateRangePicker() {
    var modal = document.getElementById("date-range-picker-modal");
    modal.style.display = "block";
}

function closeDateRangePicker() {
    var modal = document.getElementById("date-range-picker-modal");
    modal.style.display = "none";
}

    // Function to open the modal with the clicked image
    function openModal(img) {
        var modal = document.getElementById("imageModal");
        var modalImg = document.getElementById("modalImage");
        var caption = document.getElementById("caption");

        // Set the source of the modal image to the clicked image's source
        modal.style.display = "block";
        modalImg.src = img.src;
        caption.innerHTML = img.alt;
    }

    // Function to close the modal
    function closeModal() {
        var modal = document.getElementById("imageModal");
        modal.style.display = "none";
    }

</script>


        <?php } elseif ($page == 'employee_details') { ?>
    <h2>Employee Details</h2>

<?php
// Fetch all employee details
$sql = "SELECT employee_id, username, email, role FROM signup";
$result = $conn->query($sql);
?>

<table>
    <thead>
        <tr>
            <th>Employee ID</th> <!-- New column for Employee ID -->
            <th>Username</th>
            <th>Email</th>
            <th>Role</th>
            <!-- New column for edit actions -->
        </tr>
    </thead>
    <tbody>
        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['employee_id']) . "</td>"; // Display Employee ID
                echo "<td>" . htmlspecialchars($row['username']) . "</td>";
                echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                echo "<td>" . htmlspecialchars($row['role']) . "</td>";
                
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='5'>No employees found.</td></tr>";
        }
        ?>
    </tbody>
</table>




        <a href="download.php" class="logout-btn" style="background-color: #28a745; width: 200px;"></a><a href="download.php" class="download-btn">
    <button class="btn download-btn">Download Data</button>
</a>
        
        <?php } elseif ($page == 'add_employee') { ?>
    <h2>Add New Employee</h2>

<div class="form-container">
    <?php
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $employee_id = $_POST['employee_id'];  // Getting the employee_id from the form
        $username = $_POST['username'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $email = $_POST['email'];
        $role = $_POST['role'];

        // Insert query with employee_id
        $sql = "INSERT INTO signup (employee_id, username, password, email, role) 
                VALUES ('$employee_id', '$username', '$password', '$email', '$role')";

        if ($conn->query($sql) === TRUE) {
            echo "<p>New employee added successfully!</p>";
        } else {
            echo "<p>Error: " . $conn->error . "</p>";
        }
    }
    ?>

    <form method="POST">
        <label for="employee_id">Employee ID:</label>
        <input type="text" name="employee_id" required>

        <label for="username">Username:</label>
        <input type="text" name="username" required>

        <label for="password">Password:</label>
        <input type="password" name="password" required>

        <label for="email">Email:</label>
        <input type="email" name="email" required>

        <label for="role">Role:</label>
        <select name="role">
            <option value="Employee">Employee</option>
            <option value="Manager">Manager</option>
            <option value="Admin">Admin</option>
        </select>

        <button type="submit">Add Employee</button>
    </form>
</div>


    <?php } ?>
</div>

<script>
    function toggleSidebar() {
        document.querySelector('.sidebar').classList.toggle('open');
    }
</script>

</body>
</html>