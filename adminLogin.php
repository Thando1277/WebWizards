<?php
session_start();
header('Content-Type: application/json');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$servername = "localhost";
$db_username = "root";
$db_password = "LockIn_78";
$dbname = "WebWizards";

$conn = new mysqli($servername, $db_username, $db_password, $dbname);
if ($conn->connect_error) {
    echo json_encode(["error" => "Connection failed: " . $conn->connect_error]);
    exit;
}

$username = isset($_POST['username']) ? trim($_POST['username']) : '';
$password = isset($_POST['password']) ? trim($_POST['password']) : '';

if ($username === '' || $password === '') {
    echo json_encode(["error" => "All fields are required"]);
    exit;
}

$stmt = $conn->prepare("SELECT * FROM Admins WHERE FullName = ? LIMIT 1");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();

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
