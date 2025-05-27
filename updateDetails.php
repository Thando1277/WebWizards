<?php
session_start();

// Database credentials
$servername = "localhost";
$db_username = "root";
$db_password = "";
$dbname = "WebWizards";

// Create connection
$conn = new mysqli($servername, $db_username, $db_password, $dbname);

//Check if inputs are empty

$currentPassword = $_POST['currentPassword'];
$newPassword = $_POST['newPassword'];
$confirmPassword = $_POST['confirmPassword'];

$userId = $_SESSION['user_id'];

if (empty($currentPassword || $newPassword || $confirmPassword)){
    echo ("All fields are requuired");
}

if ($newPassword != $confirmPassword){
    die("Password doesn't match");
}

$stmt = $conn->prepare("SELECT password FROM Users WHERE userID = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($hashedPassword);
$stmt->fetch();

if (!$stmt->num_rows) {
    die("User not found.");
}

$stmt->close();

//Verify if current password matches user password
if (!password_verify($currentPassword, $hashedPassword)) {
    die("Current password is incorrect.");
}

//Hash new password
$newHashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

$updateStmt = $conn->prepare("UPDATE Users SET password = ? WHERE userID = ?");
$updateStmt->bind_param("si", $newHashedPassword, $userId);

if ($updateStmt->execute()) {
    echo "Password updated successfully.";
} else {
    echo "Error updating password: " . $updateStmt->error;
}

$updateStmt->close();
$conn->close();

?>