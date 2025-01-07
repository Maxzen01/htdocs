<?php
// Start session and check admin
session_start();
if (!isset($_SESSION['admin']) || $_SESSION['admin'] != true) {
    header("Location: login.php");
    exit();
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "attendanceapp";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get filters from query parameters
$search = isset($_GET['search']) ? $_GET['search'] : '';
$fromDate = isset($_GET['from-date']) ? $_GET['from-date'] : '';
$toDate = isset($_GET['to-date']) ? $_GET['to-date'] : '';

// Build the SQL query with filters
$sql = "SELECT username, action, time FROM attendance WHERE username LIKE '%$search%'";

// Apply date range filter if both from and to dates are provided
if (!empty($fromDate) && !empty($toDate)) {
    $sql .= " AND DATE(time) BETWEEN '$fromDate' AND '$toDate'";
}

// Execute the query
$result = $conn->query($sql);

// Prepare the CSV file for download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="attendance_data.csv"');

$output = fopen('php://output', 'w');
fputcsv($output, ['Username', 'Action', 'Time']); // Column headers

// Fetch and write data to CSV
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, [$row['username'], $row['action'], $row['time']]);
    }
} else {
    fputcsv($output, ["No records found for the selected date range."]);
}

fclose($output);
$conn->close();
exit();
?>
