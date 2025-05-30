<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["error" => "Unauthorized"]);
    exit;
}

$servername = "localhost";
$db_username = "root";
$db_password = "LockIn_78";
$dbname = "WebWizards";

$conn = new mysqli($servername, $db_username, $db_password, $dbname);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["error" => "Database connection failed"]);
    exit;
}

$userID = $_SESSION['user_id'];

// Get total reports count for user
$sql_total = "SELECT COUNT(*) AS total FROM Reports WHERE UserID = ?";
$stmt_total = $conn->prepare($sql_total);
$stmt_total->bind_param("i", $userID);
$stmt_total->execute();
$stmt_total->bind_result($total);
$stmt_total->fetch();
$stmt_total->close();

// Get completed reports count for user
$sql_completed = "SELECT COUNT(*) AS completed FROM Reports WHERE UserID = ? AND Status = 'Completed'";
$stmt_completed = $conn->prepare($sql_completed);
$stmt_completed->bind_param("i", $userID);
$stmt_completed->execute();
$stmt_completed->bind_result($completed);
$stmt_completed->fetch();
$stmt_completed->close();

$conn->close();

echo json_encode([
    "total" => $total,
    "completed" => $completed
]);
