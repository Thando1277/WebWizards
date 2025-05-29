<?php
session_start();

header('Content-Type: application/json'); // IMPORTANT: tell client it's JSON

if(!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Access denied. You must be logged in.']);
    exit;
}

// Database connection
$host = 'localhost';
$username = 'root';
<<<<<<< Updated upstream
$password = 'LockIn_78';
=======
$password = 'Makungu@0608';
>>>>>>> Stashed changes
$database = 'WebWizards';

$conn = new mysqli($host, $username, $password, $database);

if($conn->connect_error) {
    echo json_encode(['success' => false, 'error' => 'Connection failed.']);
    exit;
}

$user_id = $_SESSION['user_id'];
$input_password = $_POST['password'] ?? '';

// Fetch user password hash
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

// Verify password
if (!password_verify($input_password, $hashed_password)) {
    echo json_encode(['success' => false, 'error' => 'Incorrect password.']);
    exit;
}

// Delete the user's account
$delete_sql = "DELETE FROM Users WHERE UserID = ?";
$delete_stmt = $conn->prepare($delete_sql);
$delete_stmt->bind_param("i", $user_id);

if($delete_stmt->execute()){
    // Destroy session on success
    session_unset();
    session_destroy();

    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Error deleting account.']);
}

$conn->close();
exit;
?>
