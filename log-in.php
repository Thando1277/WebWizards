<?php
session_start();
header('Content-Type: application/json');

// Database credentials
$servername = "localhost";
$db_username = "root";
$db_password = "#Thando#2006";
$dbname = "adminlogss";

// Create connection
$conn = new mysqli($servername, $db_username, $db_password, $dbname);

// Check connection
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["error" => "Database connection failed"]);
    exit;
}

// Handle POST request
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';

    if ($username === '' || $password === '') {
        http_response_code(400);
        echo json_encode(["error" => "All fields are required"]);
        exit;
    }

    $stmt = $conn->prepare("SELECT UserID, Name, Password FROM UserTable WHERE Name = ? LIMIT 1");
    if (!$stmt) {
        http_response_code(500);
        echo json_encode(["error" => "Database error"]);
        exit;
    }

    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        $stmt->close();

        // Verify password (assumes password is hashed using password_hash())
        if (password_verify($password, $user['Password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['UserID'];
            $_SESSION['username'] = $user['Name'];

            http_response_code(200);
            echo json_encode(["success" => true]);
            exit;
        } else {
            http_response_code(401);
            echo json_encode(["error" => "Incorrect password"]);
            exit;
        }
    } else {
        $stmt->close();
        http_response_code(404);
        echo json_encode(["error" => "User not found"]);
        exit;
    }
}

// Close DB connection
$conn->close();
?>
