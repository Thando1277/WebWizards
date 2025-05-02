<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "LockIn_78";
$dbname = "adminLogss";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);


    if (empty($username) || empty($password) || empty($email) || empty($phone)) {
        echo json_encode(["error" => "All fields are required."]);
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(["error" => "Invalid email format."]);
        exit;
    }


    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("SELECT * FROM User WHERE Email = ? OR CellPhone = ?");
    $stmt->bind_param("ss", $email, $phone); 
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo json_encode(["error" => "Email or phone already exists."]);
        $stmt->close();
        exit;
    }


    $stmt = $conn->prepare("INSERT INTO User (Name, Email, Password, CellPhone, Points) VALUES (?, ?, ?, ?, 0)");
    $stmt->bind_param("ssss", $username, $email, $hashedPassword, $phone); 

    if ($stmt->execute()) {
        echo json_encode(["success" => "User registered successfully."]);
    } else {
        echo json_encode(["error" => "Error: " . $stmt->error]);
    }

    $stmt->close();
}

$conn->close();
?>

