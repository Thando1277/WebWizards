<?php
session_start();
header('Content-Type: application/json');

// Enable error reporting during development
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// DB credentials
$servername = "localhost";
$db_username = "root";
$db_password = "LockIn_78";
$dbname = "WebWizards";

// Connect to MySQL
$conn = new mysqli($servername, $db_username, $db_password, $dbname);
if ($conn->connect_error) {
    echo json_encode(["error" => "Connection failed: " . $conn->connect_error]);
    exit;
}

// Get POST data
$username = isset($_POST['username']) ? trim($_POST['username']) : '';
$password = isset($_POST['password']) ? trim($_POST['password']) : '';

if ($username === '' || $password === '') {
    echo json_encode(["error" => "All fields are required"]);
    exit;
}

// Prepare query
$stmt = $conn->prepare("SELECT * FROM Admins WHERE FullName = ? LIMIT 1");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();

    // Plain-text password comparison (use password_verify() if using hashing)
    if ($password === $user['Password']) {
        $_SESSION['user_id'] = $user['AdminID'];
        $_SESSION['username'] = $user['FullName'];
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["error" => "Incorrect password"]);
        exit;
    }
} else {
    echo json_encode(["error" => "Admin not found"]);
}

$stmt->close();
$conn->close();
?>
