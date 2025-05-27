<?php
session_start();
$isPremium = $_SESSION['isPremium'] ?? false;

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "You must be logged in to view this page.";
    exit();
}

// Connect to MySQL database
$conn = new mysqli("localhost", "root", "LockIn_78", "WebWizards");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get user info based on session ID
$userID = $_SESSION['user_id'];
$sql = "SELECT * FROM Users WHERE UserID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userID);
$stmt->execute();
$result = $stmt->get_result();

$userInfo = "";
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $userInfo = "
        <div class='user-info'>
            <p><strong>Name:</strong> " . htmlspecialchars($row["FullName"]) . "</p>
            <p><strong>Email:</strong> " . htmlspecialchars($row["Email"]) . "</p>
            <p><strong>Phone:</strong> " . htmlspecialchars($row["PhoneNumber"]) . "</p>
        </div>";
} else {
    $userInfo = "No user found.";
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <link rel="stylesheet" href="user.css">
</head>
<body>
<button class ="BackBtn" id = "BackBtn">
  <svg height="16" width="16" xmlns="http://www.w3.org/2000/svg" version="1.1" viewBox="0 0 1024 1024"><path d="M874.690416 495.52477c0 11.2973-9.168824 20.466124-20.466124 20.466124l-604.773963 0 188.083679 188.083679c7.992021 7.992021 7.992021 20.947078 0 28.939099-4.001127 3.990894-9.240455 5.996574-14.46955 5.996574-5.239328 0-10.478655-1.995447-14.479783-5.996574l-223.00912-223.00912c-3.837398-3.837398-5.996574-9.046027-5.996574-14.46955 0-5.433756 2.159176-10.632151 5.996574-14.46955l223.019353-223.029586c7.992021-7.992021 20.957311-7.992021 28.949332 0 7.992021 8.002254 7.992021 20.957311 0 28.949332l-188.073446 188.073446 604.753497 0C865.521592 475.058646 874.690416 484.217237 874.690416 495.52477z"></path></svg>
  <span>Back</span>
</button>


    <?= $userInfo ?>
      <script>
        const isPremium = <?php echo json_encode($isPremium); ?>;
        document.getElementById('BackBtn').addEventListener('click', () => {
          window.location.href = isPremium ? 'premium-dashboard.html' : 'userdashboard.html';
        });
      </script>
</body>
</html>
