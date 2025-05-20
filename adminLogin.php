<?php
session_start();

header('Content-Type: application/json'); // Important!
$servername = "localhost";
$db_username = "root";
$db_password = "#Thando#2006";
$dbname = "webwizards";

$conn = new mysqli($servername, $db_username, $db_password, $dbname);

if ($conn->connect_error) {
    echo json_encode(["error" => "Connection failed"]);
    exit;
}

$username = isset($_POST['username']) ? trim($_POST['username']) : '';
$password = isset($_POST['password']) ? trim($_POST['password']) : '';

if ($username === '' || $password === '') {
    echo json_encode(["error" => "All fields are required"]);
    exit;
}

$stmt = $conn->prepare("SELECT * FROM adminsTable WHERE username = ? LIMIT 1");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();

    if ($password === $user['password']) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];

        echo json_encode(["success" => true]);
        exit;
    } else {
        echo json_encode(["error" => "Incorrect password"]);
        exit;
    }
} else {
    echo json_encode(["error" => "Admin not found"]);
    exit;
}

$conn->close();
?>
