<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'User not logged in.']);
    exit;
}

$pointsToAdd = 20; // Points awarded per report
$user_id = $_SESSION['user_id'];

// Database connection
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'WebWizards';

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'error' => 'Database connection failed.']);
    exit;
}

// Check current points before update
$sql = "SELECT TotalPoints, AvailablePoints FROM user_points WHERE UserID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$before_total = 0;
$before_available = 0;

if ($result->num_rows === 0) {
    // Insert new record
    $insert_sql = "INSERT INTO user_points (UserID, TotalPoints, AvailablePoints) VALUES (?, ?, ?)";
    $insert_stmt = $conn->prepare($insert_sql);
    $insert_stmt->bind_param("iii", $user_id, $pointsToAdd, $pointsToAdd);
    $success = $insert_stmt->execute();
    $action = "inserted";
} else {
    // Get current values
    $row = $result->fetch_assoc();
    $before_total = $row['TotalPoints'];
    $before_available = $row['AvailablePoints'];
    
    // Update existing record
    $update_sql = "UPDATE user_points SET TotalPoints = TotalPoints + ?, AvailablePoints = AvailablePoints + ? WHERE UserID = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("iii", $pointsToAdd, $pointsToAdd, $user_id);
    $success = $update_stmt->execute();
    $action = "updated";
}

if ($success) {
    $sync_sql = "
        UPDATE PremiumUser 
        SET PointsBalance = (
            SELECT AvailablePoints 
            FROM user_points 
            WHERE UserID = ?
        )
        WHERE UserID = ?
    ";

    $sync_stmt = $conn->prepare($sync_sql);
    $sync_stmt->bind_param("ii", $user_id, $user_id);
    $sync_stmt->execute();
}


// Check points after update
$after_sql = "SELECT TotalPoints, AvailablePoints FROM user_points WHERE UserID = ?";
$after_stmt = $conn->prepare($after_sql);
$after_stmt->bind_param("i", $user_id);
$after_stmt->execute();
$after_result = $after_stmt->get_result();
$after_row = $after_result->fetch_assoc();

echo json_encode([
    'success' => true, 
    'pointsAdded' => $pointsToAdd,
    'action' => $action,
    'user_id' => $user_id,
    'before' => ['total' => $before_total, 'available' => $before_available],
    'after' => ['total' => $after_row['TotalPoints'], 'available' => $after_row['AvailablePoints']],
    'sql_success' => $success
]);

$conn->close();
?>