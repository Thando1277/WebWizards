<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "LockIn_78";
$dbname = "WebWizards";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("<script>alert('Database connection failed.'); window.history.back();</script>");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullname = trim($_POST['fullname']);
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);

    // Validate required fields
    if (empty($fullname) || empty($username) || empty($password) || empty($email) || empty($phone)) {
        echo "<script>alert('All fields are required.'); window.history.back();</script>";
        exit;
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<script>alert('Invalid email format.'); window.history.back();</script>";
        exit;
    }

    // Check for duplicates
    $stmt = $conn->prepare("SELECT * FROM Users WHERE Email = ? OR PhoneNumber = ? OR Username = ?");
    $stmt->bind_param("sss", $email, $phone, $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "<script>alert('Email, phone number, or username already exists.'); window.history.back();</script>";
        $stmt->close();
        exit;
    }
    $stmt->close();

    // Hash the password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Insert user
    $stmt = $conn->prepare("INSERT INTO Users (FullName, Username, Email, Password, PhoneNumber) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $fullname, $username, $email, $hashedPassword, $phone);
    $success = $stmt->execute();


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

$conn->close();
?>
