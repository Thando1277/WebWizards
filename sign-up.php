<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Database connection
$servername = "localhost";
$username = "root";
$password = "#Thando#2006";
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

    if ($success) {
        // Email section
        require 'PHPMailer/src/Exception.php';
        require 'PHPMailer/src/PHPMailer.php';
        require 'PHPMailer/src/SMTP.php';

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'webwizards0011@gmail.com';
            $mail->Password   = 'uvlfiurqacgfeite'; // ✅ Move this to env variable
            $mail->SMTPSecure = 'tls';
            $mail->Port       = 587;

            $mail->setFrom('webwizards0011@gmail.com', 'WebWizards');
            $mail->addAddress($email, $fullname);
            $mail->isHTML(true);
            $mail->Subject = 'Welcome to WebWizards!';
            $mail->Body    = "Hi <strong>$fullname</strong>,<br>Thanks for signing up!";

            $mail->send();
        } catch (Exception $e) {
            echo "<script>alert('Email could not be sent: {$mail->ErrorInfo}');</script>";
        }

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
