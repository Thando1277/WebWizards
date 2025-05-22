<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['UserID'])) {
    echo json_encode(['error' => 'User not logged in']);
    exit;
}

$userId = $_SESSION['UserID'];

$mysqli = new mysqli("localhost", "username", "password", "your_db");
if ($mysqli->connect_errno) {
    echo json_encode(['error' => 'Failed to connect to DB']);
    exit;
}

$result = $mysqli->query("SELECT COUNT(*) as cnt FROM Reports WHERE UserID = $userId");
$count = 0;
if ($row = $result->fetch_assoc()) {
    $count = (int)$row['cnt'];
}

echo json_encode(['count' => $count]);

$mysqli->close();
?>
