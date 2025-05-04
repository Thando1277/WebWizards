<?php
session_start();

$servername = "localhost";
$db_username = "root";
$db_password = "LockIn_78";
$dbname = "adminlogss";

// Create connection
$conn = new mysqli($servername, $db_username, $db_password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle POST request
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';

    // Validate inputs
    if (empty($username) || empty($password)) {
        // Redirect back with error
        header("Location: log-in.html?error=emptyfields");
        exit;
    }

    // Use prepared statement to fetch user
    $stmt = $conn->prepare("SELECT UserID, Name, Password FROM User WHERE Name = ? LIMIT 1");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if user exists
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // Verify password
        if (password_verify($password, $user['Password'])) {
            $_SESSION['user_id'] = $user['UserID'];
            $_SESSION['username'] = $user['Name'];

            // Redirect to dashboard
            header("Location: userdashboard.html");
            exit;
        } else {
            echo "Incorrect password";
            
        }
    } else {
        header("User not found");
        
    }

    $stmt->close();
}

$conn->close();
?>

