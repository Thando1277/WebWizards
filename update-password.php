<?php
session_start();
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];

    if ($new !== $confirm) {
        echo "Passwords do not match.";
        exit();
    }

    if (isset($_SESSION['reset_email'])) {
        $email = $_SESSION['reset_email'];
        $hashed = password_hash($new, PASSWORD_DEFAULT);
        $query = "UPDATE Users SET Password='$hashed' WHERE Email='$email'";

        if (mysqli_query($conn, $query)) {
            session_destroy();
            echo "Password updated successfully. <a href='log-in.html'>Login</a>";
        } else {
            echo "Failed to update password.";
        }
    } else {
        echo "Session expired.";
    }
}
?>
