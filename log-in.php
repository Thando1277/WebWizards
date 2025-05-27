<?php
session_start();
header('Content-Type: application/json');

// Database credentials
$servername = "localhost";
$db_username = "root";
$db_password = "LockIn_78";
$dbname = "WebWizards";

// Create connection
$conn = new mysqli($servername, $db_username, $db_password, $dbname);

// Check connection
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["error" => "Database connection failed"]);
    exit;
}

// Handle POST request
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';

    if ($username === '' || $password === '') {
        http_response_code(400);
        echo json_encode(["error" => "All fields are required"]);
        exit;
    }

    // Get user info from Users table
    $stmt = $conn->prepare("SELECT UserID, Username, Password FROM Users WHERE Username = ? LIMIT 1");
    if (!$stmt) {
        http_response_code(500);
        echo json_encode(["error" => "Database error (prepare failed)"]);
        exit;
    }

    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        $stmt->close();

        // Verify password
        if (password_verify($password, $user['Password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['UserID'];
            $_SESSION['username'] = $user['Username'];

            // Check if user is in PremiumUser table
            $stmt2 = $conn->prepare("SELECT PremiumID FROM PremiumUser WHERE UserID = ? LIMIT 1");
            if (!$stmt2) {
                http_response_code(500);
                echo json_encode(["error" => "Database error (premium check)"]);
                exit;
            }

            $stmt2->bind_param("i", $user['UserID']);
            $stmt2->execute();
            $stmt2->store_result();

            $isPremium = $stmt2->num_rows > 0;
            $_SESSION['isPremium'] = $isPremium; // ✅ Store it in session for use later

            $stmt2->close();

            $redirectPage = $isPremium ? "premium-dashboard.html" : "userdashboard.html";

            http_response_code(200);
            echo json_encode([
                "success" => true,
                "redirect" => $redirectPage
            ]);
            exit;
        } else {
            http_response_code(401);
            echo json_encode(["error" => "Incorrect password"]);
            exit;
        }
    } else {
        $stmt->close();
        http_response_code(404);
        echo json_encode(["error" => "User not found"]);
        exit;
    }
}

// Close DB connection
$conn->close();
?>
