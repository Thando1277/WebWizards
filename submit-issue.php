<?php
session_start();
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["error" => "Unauthorized. User not logged in."]);
    exit;
}

// DB config
$servername = "localhost";
$db_username = "root";
$db_password = "Makungu@0608";
$dbname = "WebWizards";

// Connect to DB
$conn = new mysqli($servername, $db_username, $db_password, $dbname);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["error" => "Database connection failed"]);
    exit;
}

// Ensure POST method
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userID = $_SESSION['user_id'];
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $location = isset($_POST['location']) ? trim($_POST['location']) : '';

    // Validate input
    if ($description === '' || $location === '') {
        http_response_code(400);
        echo json_encode(["error" => "Description and location are required"]);
        exit;
    }

    // Handle image upload
    $imagePath = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $uploadDir = 'uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        $imageName = uniqid() . '_' . basename($_FILES['image']['name']);
        $imagePath = $uploadDir . $imageName;

        if (!move_uploaded_file($_FILES['image']['tmp_name'], $imagePath)) {
            http_response_code(500);
            echo json_encode(["error" => "Failed to save uploaded image"]);
            exit;
        }
    } else {
        http_response_code(400);
        echo json_encode(["error" => "Image is required"]);
        exit;
    }

    // Insert report
    $stmt = $conn->prepare("INSERT INTO Reports (UserID, Image, Location, Description) VALUES (?, ?, ?, ?)");
    if (!$stmt) {
        http_response_code(500);
        echo json_encode(["error" => "Database error"]);
        exit;
    }

    $stmt->bind_param("isss", $userID, $imagePath, $location, $description);
    if ($stmt->execute()) {
        // If user is premium, update TotalReports
        $updatePremium = $conn->prepare("UPDATE PremiumUser SET TotalReports = TotalReports + 1 WHERE UserID = ?");
        $updatePremium->bind_param("i", $userID);
        $updatePremium->execute(); // Will only affect row if user is premium
        $updatePremium->close();

        echo json_encode(["success" => true, "message" => "Report submitted"]);
    } else {
        http_response_code(500);
        echo json_encode(["error" => "Failed to submit report"]);
    }

    $stmt->close();
    $conn->close();
    exit;
}

http_response_code(405);
echo json_encode(["error" => "Method not allowed"]);
?>
