<?php
session_start();

if(!isset($_SESSION['user_id'])) {
    die("Access denied. You must be logged in.");
}

//Database connection
$host = 'localhost';
$username = 'root';
$password = 'LockIn_78';
$databse = 'WebWizards';

$conn = new mysqli($host, $username, $password, $databse);


if($conn->connect_error) {
    die("Connection failed");
}

$user_id = $_SESSION['user_id'];

$input_password = $_POST['password'] ?? '';

//Fetching user from database
$sql = "SELECT Password FROM Users WHERE UserID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    die("User not found.");
}

$user = $result->fetch_assoc();
$hashed_password = $user['Password'];

//Now we verify the password
if (!password_verify($input_password, $hashed_password)) {
    die("Incorrect password.");
}

//Delete the user's account from Database
$delete_sql = "DELETE FROM Users WHERE UserID = ?";
$delete_stmt = $conn->prepare($delete_sql);
$delete_stmt->bind_param("i", $user_id);

if($delete_stmt->execute()){
    //Here we destroy the user's session
    session_unset();
    session_destroy();
    //Redirect user to homepage
    header("Location: index.html");
    exit();

}else{
    echo "Error deleting account.";
}
$conn->close();
?>