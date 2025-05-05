<?php
session_start();
header('Content-Type: application/json');

$servername = "localhost";
$db_username = "root";
$db_password = "LockIn_78";
$dbname = "adminlogss";

$conn = new mysqli($servername, $db_username, $db_password, $dbname);

if ($conn->connect_error) {
    echo json_encode(["error" => "Database connection failed"]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';

    if ($username === '' || $password === '') {
        echo json_encode(["error" => "All fields are required"]);
        exit;
    }

    $stmt = $conn->prepare("SELECT UserID, Name, Password FROM User WHERE Name = ? LIMIT 1");
    if (!$stmt) {
        echo json_encode(["error" => "Database error"]);
        exit;
    }

    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['Password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['UserID'];
            $_SESSION['username'] = $user['Name'];

            echo json_encode(["success" => true]);
            exit;
        } else {
            echo json_encode(["error" => "Incorrect password"]);
            exit;
        }
    } else {
        echo json_encode(["error" => "User not found"]);
        exit;
    }

    $stmt->close();
}

$conn->close();
?>
