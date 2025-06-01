<?php
session_start();
include 'db.php';

function showMessageAndRedirect($message, $redirectTo, $isSuccess = true) {
    $bgColor = $isSuccess ? '#28a745' : '#dc3545'; // green or red
    $textColor = '#fff';
    echo "
    <div style='
        padding: 20px;
        margin: 50px auto;
        max-width: 500px;
        text-align: center;
        background-color: $bgColor;
        color: $textColor;
        border-radius: 10px;
        font-family: Arial, sans-serif;
        font-size: 1.2rem;
        box-shadow: 0 4px 10px rgba(0,0,0,0.2);
    '>
        $message<br><br>
        <span style='font-size: 0.9rem;'>You will be redirected shortly...</span>
    </div>
    <script>
        setTimeout(function() {
            window.location.href = '$redirectTo';
        }, 1000);
    </script>
    ";
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];

    if ($new !== $confirm) {
        showMessageAndRedirect("❌ Passwords do not match.", "new-password.html", false);
        exit();
    }

    if (isset($_SESSION['reset_email'])) {
        $email = $_SESSION['reset_email'];
        $hashed = password_hash($new, PASSWORD_DEFAULT);
        $query = "UPDATE Users SET Password='$hashed' WHERE Email='$email'";

        if (mysqli_query($conn, $query)) {
            session_destroy();
            showMessageAndRedirect("✅ Password updated successfully!", "log-in.html", true);
        } else {
            showMessageAndRedirect("❌ Failed to update password.", "new-password.html", false);
        }
    } else {
        showMessageAndRedirect("⚠️ Session expired.", "new-password.html", false);
    }
}
?>
