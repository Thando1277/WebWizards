<?php
session_start();
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    
    $stmt = $conn->prepare("SELECT * FROM Users WHERE Email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $otp = rand(100000, 999999);
        $_SESSION['reset_email'] = $email;
        $_SESSION['otp'] = $otp;
        $_SESSION['otp_expiry'] = time() + 300;

      
        echo "<!DOCTYPE html>
        <html>
        <head>
            <title>Sending OTP...</title>
            <script src='https://cdn.jsdelivr.net/npm/@emailjs/browser@3/dist/email.min.js'></script>
        </head>
        <body>
            <div style='text-align: center; margin-top: 100px; font-family: Arial;'>
                <h2>Sending OTP to your email...</h2>
                <div id='status'>Please wait...</div>
            </div>

            <script>
                emailjs.init('2EaWihhiL8j7FN-fz');
                
                emailjs.send('service_9t8787l', 'template_frme8oz', {
                    to_email: '$email',
                    otp_code: '$otp',
                    to_name: 'User'
                }).then(function(response) {
                    document.getElementById('status').innerHTML = 
                        '<h3 style=\"color: green\">✅ OTP sent successfully!</h3>' +
                        '<p>Check your email: $email</p>' +
                        '<a href=\"verify-otp.html\" style=\"background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;\">Enter OTP</a>';
                }).catch(function(error) {
                    document.getElementById('status').innerHTML = 
                        '<h3 style=\"color: red\">❌ Failed to send OTP</h3>' +
                        '<p>Error: ' + error + '</p>' +
                        '<a href=\"javascript:history.back()\">Try Again</a>';
                });
            </script>
        </body>
        </html>";
        
    } else {
        echo "Email not found!";
    }
    
    $stmt->close();
}
?>