<?php
session_start();

function showStyledMessage($message, $redirectTo = null, $isSuccess = false) {
    $bgColor = $isSuccess ? '#28a745' : '#dc3545'; // Green or red
    $textColor = '#fff';

    echo "
    <div style='
        margin: 100px auto;
        max-width: 500px;
        background-color: $bgColor;
        color: $textColor;
        padding: 20px;
        border-radius: 8px;
        font-family: Arial, sans-serif;
        font-size: 1.1rem;
        text-align: center;
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    '>
        $message<br><br>";

    if ($redirectTo) {
        echo "<span style='font-size: 0.9rem;'>You will be redirected shortly...</span>
        <script>
            setTimeout(function() {
                window.location.href = '$redirectTo';
            }, 4000);
        </script>";
    }

    echo "</div>";
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $userOtp = $_POST['otp'];

    if (isset($_SESSION['otp']) && $_SESSION['otp'] == $userOtp && time() < $_SESSION['otp_expiry']) {
        header("Location: new-password.html");
        exit();
    } else {
        showStyledMessage("❌ Invalid or expired OTP.", "verify-otp.html", false);
    }
}
?>
