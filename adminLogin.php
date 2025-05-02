<?php

$servername = "localhost";  
$db_username = "root";      
$db_password = "LockIn_78"; 
$dbname = "adminLogss";


$conn = new mysqli($servername, $db_username, $db_password, $dbname);


if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


$username = isset($_POST['username']) ? $_POST['username'] : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';


$username = $conn->real_escape_string($username);
$password = $conn->real_escape_string($password);


$sql = "SELECT * FROM adminsTable WHERE username = '$username' LIMIT 1";
$result = $conn->query($sql);

if ($result->num_rows > 0) {

    $user = $result->fetch_assoc();
    

    if (password_verify($password, $user['password'])) {

        echo "success";  
    } else {

        echo "Invalid username or password.";
    }
} else {

    echo "Invalid username or password.";
}

$conn->close();
?>
