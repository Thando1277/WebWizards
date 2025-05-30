<?php
session_start();
header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    $response['message'] = "Unauthorized access.";
    echo json_encode($response);
    exit;
}


$servername = "localhost";
$db_username = "root";
$db_password = "";
$dbname = "WebWizards";


$conn = new mysqli($servername, $db_username, $db_password, $dbname);


$currentPassword = trim($_POST['currentPassword'] ?? '');
$newPassword     = trim($_POST['newPassword'] ?? '');
$confirmPassword = trim($_POST['confirmPassword'] ?? '');

// Validate inputs
if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
    $response['message'] = "All fields are required.";
    echo json_encode($response);
    exit;
}

if (strlen($newPassword) < 6) {
    $response['message'] = "New Password must be at least 6 characters.";
    echo json_encode($response);
    exit;
}

if ($newPassword !== $confirmPassword) {
    $response['message'] = "New passwords do not match.";
    echo json_encode($response);
    exit;
}

// Fetch current hashed password
$userId = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT password FROM Users WHERE userID = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    $response['message'] = "User not found.";
    echo json_encode($response);
    $stmt->close();
    $conn->close();
    exit;
}

$stmt->bind_result($hashedPassword);
$stmt->fetch();
$stmt->close();

// Verify current password
if (!password_verify($currentPassword, $hashedPassword)) {
    $response['message'] = "Current password is incorrect.";
    echo json_encode($response);
    $conn->close();
    exit;
}

// Hash new password and update
$newHashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
$updateStmt = $conn->prepare("UPDATE Users SET password = ? WHERE userID = ?");
$updateStmt->bind_param("si", $newHashedPassword, $userId);

if ($updateStmt->execute()) {
    $response['success'] = true;
    $response['message'] = "Password updated successfully!";
} else {
    $response['message'] = "Error updating password: " . $updateStmt->error;
}

$updateStmt->close();
$conn->close();

echo json_encode($response);
?>
