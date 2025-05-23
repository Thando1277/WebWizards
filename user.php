<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="user.css">
</head>
<body>
    <?php
    // Connect to MySQL database
    $conn = new mysqli("localhost", "root", "Makungu@0608", "adminlogss");

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Get user info (for demo, we use a static ID of 1)
    $sql = "SELECT * FROM UserTable WHERE UserID = 1";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo "<div class='user-info'>";
        echo "<p><strong>Name:</strong> " . $row["Name"] . "</p>";
        echo "<p><strong>Pssword:</strong> " . $row["Password"] . "</p>";
        echo "<p><strong>Email:</strong> " . $row["Email"] . "</p>";
        echo "<p><strong>Phone:</strong> " . $row["CellPhone"] . "</p>";
        echo "</div>";
    } else {
        echo "No user found.";
    }

    $conn->close();
?>
</body>
</html>