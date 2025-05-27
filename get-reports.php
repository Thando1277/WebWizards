<?php
session_start();
header('Content-Type: application/json');

// Database credentials
$servername = "localhost";
$db_username = "root";
$db_password = "";
$dbname = "WebWizards";

// Create connection
$conn = new mysqli($servername, $db_username, $db_password, $dbname);

if ($conn->connect_error) {
    echo json_encode(['error' => 'Connection failed: ' . $conn->connect_error]);
    exit();
}

// Query to get all reports with location data
$sql = "SELECT Latitude, Longitude, Description, Status FROM Reports";
$result = $conn->query($sql);

// Check if there are results
$reports = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $reports[] = $row;
    }
}

// Return JSON
echo json_encode($reports);

// Close connection
$conn->close();
?>