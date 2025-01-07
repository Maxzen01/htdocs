<?php
session_start();

// Admin validation


// Database connection
$conn = new mysqli("localhost", "root", "", "attendanceapp");

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $status = $_POST['status'];

    // Update leave status
    $stmt = $conn->prepare("UPDATE leave_requests SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $id);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Leave request updated successfully.";
    } else {
        $_SESSION['error'] = "Failed to update leave request.";
    }

    $stmt->close();
}

$conn->close();
header("Location: view_leave_requests.php");
exit();
?>
