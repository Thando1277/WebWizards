<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["error" => "Unauthorized. User not logged in."]);
    exit;
}

$servername = "localhost";
$db_username = "root";
$db_password = "LockIn_78";
$dbname = "WebWizards";

$conn = new mysqli($servername, $db_username, $db_password, $dbname);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["error" => "Database connection failed"]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userID = $_SESSION['user_id'];
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $location = isset($_POST['location']) ? trim($_POST['location']) : '';

    if ($description === '' || $location === '') {
        http_response_code(400);
        echo json_encode(["error" => "Description and location are required"]);
        exit;
    }

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

    $stmt = $conn->prepare("INSERT INTO Reports (UserID, Image, Location, Description) VALUES (?, ?, ?, ?)");
    if (!$stmt) {
        http_response_code(500);
        echo json_encode(["error" => "Database error"]);
        exit;
    }

    $stmt->bind_param("isss", $userID, $imagePath, $location, $description);
    if ($stmt->execute()) {
        $premiumCheck = $conn->prepare("SELECT UserID FROM PremiumUser WHERE UserID = ?");
        $premiumCheck->bind_param("i", $userID);
        $premiumCheck->execute();
        $premiumResult = $premiumCheck->get_result();
        $isPremium = $premiumResult->num_rows > 0;
        $premiumCheck->close();

        if ($isPremium) {
            $updatePremium = $conn->prepare("UPDATE PremiumUser SET TotalReports = TotalReports + 1 WHERE UserID = ?");
            $updatePremium->bind_param("i", $userID);
            $updatePremium->execute();
            $updatePremium->close();

            $pointsToAdd = 20;
            $pointsCheck = $conn->prepare("SELECT TotalPoints, AvailablePoints FROM user_points WHERE UserID = ?");
            $pointsCheck->bind_param("i", $userID);
            $pointsCheck->execute();
            $pointsResult = $pointsCheck->get_result();

            if ($pointsResult->num_rows === 0) {
                $insertPoints = $conn->prepare("INSERT INTO user_points (UserID, TotalPoints, AvailablePoints) VALUES (?, ?, ?)");
                $insertPoints->bind_param("iii", $userID, $pointsToAdd, $pointsToAdd);
                $insertPoints->execute();
                $insertPoints->close();
            } else {
                $updatePoints = $conn->prepare("UPDATE user_points SET TotalPoints = TotalPoints + ?, AvailablePoints = AvailablePoints + ? WHERE UserID = ?");
                $updatePoints->bind_param("iii", $pointsToAdd, $pointsToAdd, $userID);
                $updatePoints->execute();
                $updatePoints->close();
            }
            $pointsCheck->close();

            $syncPoints = $conn->prepare("UPDATE PremiumUser SET PointsBalance = (SELECT AvailablePoints FROM user_points WHERE UserID = ?) WHERE UserID = ?");
            $syncPoints->bind_param("ii", $userID, $userID);
            $syncPoints->execute();
            $syncPoints->close();

            echo json_encode([
                "success" => true, 
                "message" => "Report submitted successfully! +20 points awarded!",
                "isPremium" => true,
                "pointsAwarded" => $pointsToAdd
            ]);
        } else {
            echo json_encode([
                "success" => true, 
                "message" => "Report submitted successfully!",
                "isPremium" => false
            ]);
        }
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