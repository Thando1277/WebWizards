<?php
// Database connection settings
$servername = "localhost";  // Your database server
$db_username = "root";      // Your database username
$db_password = "LockIn_78";          // Your database password
$dbname = "adminLogss";    // Your database name

// Create a connection to the database
$conn = new mysqli($servername, $db_username, $db_password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get form data (username and password)
$username = isset($_POST['username']) ? $_POST['username'] : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';

// Sanitize inputs (optional but recommended)
$username = $conn->real_escape_string($username);
$password = $conn->real_escape_string($password);

// Check if the username exists in the database
$sql = "SELECT * FROM adminsTable WHERE username = '$username' LIMIT 1";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // User found, fetch data
    $user = $result->fetch_assoc();
    
    // Check if the password matches
    if (password_verify($password, $user['password'])) {
        // Success: login is valid
        echo "success";  // Return success response to frontend
    } else {
        // Failure: invalid password
        echo "Invalid username or password.";
    }
} else {
    // Failure: user not found
    echo "Invalid username or password.";
}

$conn->close();
?>
