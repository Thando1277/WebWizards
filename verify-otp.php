<?php
session_start();
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $userOtp = $_POST['otp'];
    if (isset($_SESSION['otp']) && $_SESSION['otp'] == $userOtp && time() < $_SESSION['otp_expiry']) {
        header("Location: new-password.html");
    } else {
        echo "Invalid or expired OTP.";
    }
}
?>
