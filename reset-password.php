<?php
session_start();
$servername = "localhost";
$username = "root";
$password = "LockIn_78";
$dbname = "WebWizards";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (!isset($_SESSION['verified_username'])) {
    echo "<script>alert('Unauthorized access.'); window.location.href='verify-phone.html';</script>";
    exit();
}

$usernameInput = $_SESSION['verified_username'];
$newPassword = $_POST['new_password'];
$confirmPassword = $_POST['confirm_password'];

if ($newPassword !== $confirmPassword) {
    echo "<script>alert('Passwords do not match!'); window.location.href='reset-password.html';</script>";
    exit();
}

$hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
$update = $conn->prepare("UPDATE Users SET Password = ? WHERE Username = ?");
$update->bind_param("ss", $hashedPassword, $usernameInput);

if ($update->execute()) {
    session_destroy();
    echo "<script>alert('Password updated successfully. Please log in.'); window.location.href='log-in.html';</script>";
} else {
    echo "<script>alert('Error updating password.'); window.location.href='reset-password.html';</script>";
}

$update->close();
$conn->close();
?>
