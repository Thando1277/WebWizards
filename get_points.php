<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Not logged in']);
    exit;
}

$conn = new mysqli('localhost', 'root', '', 'WebWizards');
if ($conn->connect_error) {
    echo json_encode(['error' => 'DB connection failed']);
    exit;
}

$user_id = $_SESSION['user_id'];
$response = [];

// Get points
$pointsSql = "SELECT TotalPoints, AvailablePoints FROM user_points WHERE UserID = ?";
$pointsStmt = $conn->prepare($pointsSql);
$pointsStmt->bind_param("i", $user_id);
$pointsStmt->execute();
$pointsResult = $pointsStmt->get_result();

if ($pointsResult->num_rows === 0) {
    $response['TotalPoints'] = 0;
    $response['AvailablePoints'] = 0;
} else {
    $response = $pointsResult->fetch_assoc();
}

// Get reward history - UPDATED QUERY
$historySql = "SELECT 
                RewardType, 
                PointsUsed, 
                DATE_FORMAT(RedeemedAt, '%Y-%m-%d %H:%i:%s') AS RedeemedAt 
               FROM reward_history 
               WHERE UserID = ? 
               ORDER BY RedeemedAt DESC";
$historyStmt = $conn->prepare($historySql);
$historyStmt->bind_param("i", $user_id);
$historyStmt->execute();
$historyResult = $historyStmt->get_result();

$response['history'] = [];
while ($row = $historyResult->fetch_assoc()) {
    $response['history'][] = $row;
}

echo json_encode($response);
?>