<?php
session_start();
header('Content-Type: application/json');

if(!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$servername = "localhost";
$db_username = "root";
<<<<<<< Updated upstream
<<<<<<< Updated upstream
$db_password = "LockIn_78";
=======
$db_password = "Makungu@0608";
>>>>>>> Stashed changes
=======
$db_password = "";
>>>>>>> Stashed changes
$dbname = "WebWizards";

$conn = new mysqli($servername, $db_username, $db_password, $dbname);
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'DB connection failed']);
    exit;
}

$userId = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT FullName FROM users WHERE userID = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$stmt->bind_result($username);
$stmt->fetch();

if ($username) {
    echo json_encode(['success' => true, 'username' => $username]);
} else {
    echo json_encode(['success' => false, 'message' => 'User not found']);
}

$stmt->close();
$conn->close();

?>


