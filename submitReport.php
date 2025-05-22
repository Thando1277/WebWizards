<?php
session_start();
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["error" => "Unauthorized. User not logged in."]);
    exit;
}

$userID = $_SESSION['user_id'];

// Database connection
$mysqli = new mysqli("localhost", "root", "LockIn_78", "WebWizards");
if ($mysqli->connect_errno) {
    http_response_code(500);
    echo json_encode(["error" => "Database connection failed"]);
    exit;
}

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => "Method not allowed"]);
    exit;
}

// Get and validate inputs
$description = trim($_POST['description'] ?? '');
$location = trim($_POST['location'] ?? 'Unknown');

if ($description === '' || $location === '') {
    http_response_code(400);
    echo json_encode(["error" => "Description and location are required"]);
    exit;
}

// Handle image upload
$imagePath = '';
if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = 'uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $originalName = basename($_FILES['image']['name']);
    $uniqueName = time() . '_' . $originalName;
    $targetFile = $uploadDir . $uniqueName;

    if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
        $imagePath = $targetFile;
    } else {
        http_response_code(500);
        echo json_encode(["error" => "Failed to save uploaded image"]);
        exit;
    }
} else {
    http_response_code(400);
    echo json_encode(["error" => "Image is required"]);
    exit;
}

// Insert report into database with status 'Pending'
$stmt = $mysqli->prepare("INSERT INTO Reports (UserID, Image, Location, Description, Status) VALUES (?, ?, ?, ?, 'Pending')");
if (!$stmt) {
    http_response_code(500);
    echo json_encode(["error" => "Failed to prepare SQL statement"]);
    exit;
}

$stmt->bind_param("isss", $userID, $imagePath, $location, $description);

if ($stmt->execute()) {
    // Fetch updated report count
    $countResult = $mysqli->query("SELECT COUNT(*) AS report_count FROM Reports WHERE UserID = $userID");
    $reportCount = $countResult->fetch_assoc()['report_count'] ?? 0;

    echo json_encode([
        "success" => true,
        "message" => "Report submitted successfully",
        "reportedCount" => $reportCount
    ]);
} else {
    http_response_code(500);
    echo json_encode(["error" => "Failed to submit report"]);
}

$stmt->close();
$mysqli->close();
?>
