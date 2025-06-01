<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

session_start();

$servername = "localhost";
$username = "root";
$password = "LockIn_78";
$dbname = "WebWizards";

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $requestToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_POST['csrf_token'] ?? '';
        if (!hash_equals($_SESSION['csrf_token'] ?? '', $requestToken)) {
            http_response_code(403);
            die(json_encode(['success' => false, 'error' => 'Invalid CSRF token']));
        }
    }

    if (!empty($_FILES['image'])) {
        $input = $_POST;
        $action = $input['action'] ?? '';
    } else {
        $input = json_decode(file_get_contents('php://input'), true);
        $action = $input['action'] ?? '';
    }

    switch ($action) {
            case 'updateStatus':
        $reportId = $input['reportId'] ?? null;
        $newStatus = $input['status'] ?? null;
        $feedback = $input['feedback'] ?? '';

        $adminId = $_SESSION['admin_id'] ?? null;
        if (!$adminId) {
            throw new Exception('Admin not authenticated.');
        }

        if (!$reportId || !$newStatus) {
            throw new Exception('Missing reportId or status.');
        }

        $validStatuses = ['Pending', 'In Progress', 'Completed', 'Rejected'];
        if (!in_array($newStatus, $validStatuses)) {
            throw new Exception('Invalid status value: ' . $newStatus);
        }

        $pdo->beginTransaction();

        $updateQuery = "UPDATE Reports SET Status = ?, UpdatedAt = NOW() WHERE ReportID = ?";
        $updateStmt = $pdo->prepare($updateQuery);
        $updateStmt->execute([$newStatus, $reportId]);

        if ($updateStmt->rowCount() === 0) {
            throw new Exception('No rows updated - report may not exist.');
        }
        if (!empty($feedback)) {
            $userQuery = "SELECT UserID FROM Reports WHERE ReportID = ?";
            $userStmt = $pdo->prepare($userQuery);
            $userStmt->execute([$reportId]);
            $userId = $userStmt->fetchColumn();

            $insertMsgQuery = "INSERT INTO Messages (ReportID, UserID, AdminID, Feedback, CreatedAt) 
                            VALUES (?, ?, ?, ?, NOW())";
            $messageStmt = $pdo->prepare($insertMsgQuery);
            $messageStmt->execute([$reportId, $userId, $adminId, $feedback]);
        }

        $pdo->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Report status updated successfully',
            'reportId' => $reportId,
            'newStatus' => $newStatus
        ]);
        break;


        case 'uploadImage':
            $reportId = $_POST['reportId'] ?? null;
            
            if (!$reportId) {
                throw new Exception('Report ID is required');
            }

            if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
                throw new Exception('No file uploaded or upload error occurred');
            }

            // Validate file type
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $fileType = $_FILES['image']['type'];
            if (!in_array($fileType, $allowedTypes)) {
                throw new Exception('Invalid file type. Only JPEG, PNG, GIF, and WebP are allowed.');
            }

            // Set upload directory
            $uploadDir = __DIR__ . '/uploads/';
            if (!is_dir($uploadDir)) {
                if (!mkdir($uploadDir, 0755, true)) {
                    throw new Exception('Failed to create upload directory');
                }
            }

            // Generate unique filename
            $fileExtension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $filename = 'report_' . $reportId . '_' . uniqid() . '.' . $fileExtension;
            $destination = $uploadDir . $filename;

            // Move uploaded file
            if (!move_uploaded_file($_FILES['image']['tmp_name'], $destination)) {
                throw new Exception('Failed to save uploaded file');
            }

            // Update database with image reference
            $stmt = $pdo->prepare("UPDATE Reports SET FeedbackImage = ? WHERE ReportID = ?");
            $stmt->execute([$filename, $reportId]);

            echo json_encode([
                'success' => true,
                'message' => 'Image uploaded successfully',
                'fileName' => $filename,
                'filePath' => 'uploads/' . $filename
            ]);
            break;

        case 'clearImage':
            $reportId = $input['reportId'] ?? null;
            
            if (!$reportId) {
                throw new Exception('Report ID is required');
            }

            $stmt = $pdo->prepare("SELECT FeedbackImage FROM Reports WHERE ReportID = ?");
            $stmt->execute([$reportId]);
            $currentImage = $stmt->fetchColumn();

            if ($currentImage) {
                $filePath = __DIR__ . '/uploads/' . $currentImage;
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }

            $stmt = $pdo->prepare("UPDATE Reports SET FeedbackImage = NULL WHERE ReportID = ?");
            $stmt->execute([$reportId]);

            echo json_encode([
                'success' => true,
                'message' => 'Image cleared successfully'
            ]);
            break;

        default:
            throw new Exception('Invalid action specified.');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'trace' => $e->getTrace()
    ]);
}
?>