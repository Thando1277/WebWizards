<?php
session_start();
header('Content-Type: application/json');

// Database credentials
$servername = "localhost";
$db_username = "root";
$db_password = "LockIn_78";
$dbname = "WebWizards";

// Create connection
$conn = new mysqli($servername, $db_username, $db_password, $dbname);

if ($conn->connect_error) {
    echo json_encode(['error' => 'Connection failed: ' . $conn->connect_error]);
    exit();
}

// Query to get all reports with address-based location
$sql = "SELECT Location, Description, Status FROM Reports";
$result = $conn->query($sql);

$reports = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $reports[] = $row;
    }
}

echo json_encode($reports);
$conn->close();
?>
