<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <link rel="stylesheet" href="user.css">
</head>
<body>
<?php
session_start(); // Required to access $_SESSION

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "You must be logged in to view this page.";
    exit();
}

// Connect to MySQL database
$conn = new mysqli("localhost", "root", "LockIn_78", "WebWizards");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get user info based on session ID
$userID = $_SESSION['user_id'];
$sql = "SELECT * FROM Users WHERE UserID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    echo "<div class='user-info'>";
    echo "<p><strong>Name:</strong> " . htmlspecialchars($row["FullName"]) . "</p>";
    echo "<p><strong>Email:</strong> " . htmlspecialchars($row["Email"]) . "</p>";
    echo "<p><strong>Phone:</strong> " . htmlspecialchars($row["PhoneNumber"]) . "</p>";
    echo "</div>";
} else {
    echo "No user found.";
}

$conn->close();
?>
</body>
</html>
