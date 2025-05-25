<?php
$servername = "localhost";
$username = "root";
$password = "Makungu@0608";
$dbname = "WebWizards";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$usernameInput = $_POST['username'];
$newPassword = $_POST['new_password'];
$confirmPassword = $_POST['confirm_password'];

if ($newPassword !== $confirmPassword) {
    echo "<script>alert('Passwords do not match!'); window.location.href='forgot-password.html';</script>";
    exit();
}

$hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

$stmt = $conn->prepare("SELECT * FROM Users WHERE Username = ?");
$stmt->bind_param("s", $usernameInput);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<script>alert('User not found.'); window.location.href='forgot-password.html';</script>";
    exit();
}

$update = $conn->prepare("UPDATE Users SET Password = ? WHERE Username = ?");
$update->bind_param("ss", $hashedPassword, $usernameInput);

if ($update->execute()) {
    echo "<script>alert('Password updated successfully. Please log in.'); window.location.href='log-in.html';</script>";
} else {
    echo "<script>alert('Error updating password.'); window.location.href='forgot-password.html';</script>";
}

$stmt->close();
$update->close();
$conn->close();
?>
