<?php
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Access denied. You must be logged in.']);
    exit;
}

$host = 'localhost';
$username = 'root';
$password = 'LockIn_78';
$database = 'WebWizards';

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'error' => 'Connection failed.']);
    exit;
}

$user_id = $_SESSION['user_id'];
$input_password = $_POST['password'] ?? '';

$sql = "SELECT Password FROM Users WHERE UserID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    echo json_encode(['success' => false, 'error' => 'User not found.']);
    exit;
}

$user = $result->fetch_assoc();
$hashed_password = $user['Password'];

if (!password_verify($input_password, $hashed_password)) {
    echo json_encode(['success' => false, 'error' => 'Incorrect password.']);
    exit;
}

$delete_msgs = $conn->prepare("DELETE FROM messages WHERE UserID = ?");
$delete_msgs->bind_param("i", $user_id);
$delete_msgs->execute();
$delete_msgs->close();

$delete_sql = "DELETE FROM Users WHERE UserID = ?";
$delete_stmt = $conn->prepare($delete_sql);
$delete_stmt->bind_param("i", $user_id);

if ($delete_stmt->execute()) {
    session_unset();
    session_destroy();
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Error deleting account.']);
}

$delete_stmt->close();
$conn->close();
exit;
?>
