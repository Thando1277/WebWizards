<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "#Thando#2006";
$dbname = "adminLogss";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("<script>alert('Database connection failed.'); window.history.back();</script>");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);

    if (empty($username) || empty($password) || empty($email) || empty($phone)) {
        echo "<script>alert('All fields are required.'); window.history.back();</script>";
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<script>alert('Invalid email format.'); window.history.back();</script>";
        exit;
    }

    $stmt = $conn->prepare("SELECT * FROM UserTable WHERE Email = ? OR CellPhone = ?");
    $stmt->bind_param("ss", $email, $phone); 
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "<script>alert('Email or phone already exists.'); window.history.back();</script>";
        $stmt->close();
        exit;
    }
    $stmt->close();

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO UserTable (Name, Email, Password, CellPhone, Points) VALUES (?, ?, ?, ?, 0)");
    $stmt->bind_param("ssss", $username, $email, $hashedPassword, $phone); 

    if ($stmt->execute()) {
        echo "<script>
            alert('User registered successfully.');
            window.location.href = 'log-in.html';
        </script>";
    } else {
        echo "<script>
            alert('Error: " . addslashes($stmt->error) . "');
            window.history.back();
        </script>";
    }

    $stmt->close();
}

$conn->close();
?>
