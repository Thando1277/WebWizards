<?php
// dashboard_data.php - Fixed version with proper Messages table image storage

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', 'upload_debug.log');

// Set headers first
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

// Database configuration
$servername = "localhost";
$username = "root";
$password = "LockIn_78";
$dbname = "WebWizards";

function debugLog($message) {
    error_log("[DEBUG] " . date('Y-m-d H:i:s') . " - " . $message);
}

function sendJsonResponse($data, $httpCode = 200) {
    http_response_code($httpCode);
    echo json_encode($data, JSON_PRETTY_PRINT);
    exit;
}

try {
    // Database connection
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    debugLog("Request Method: " . $_SERVER['REQUEST_METHOD']);
    debugLog("Content Type: " . ($_SERVER['CONTENT_TYPE'] ?? 'Not set'));
    debugLog("FILES array: " . print_r($_FILES, true));
    debugLog("POST array: " . print_r($_POST, true));

    // Handle POST requests
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        debugLog("Processing POST request with content type: " . $contentType);
        
        // Handle file uploads FIRST (multipart/form-data)
        if (isset($_FILES['feedbackImage']) && isset($_POST['action']) && $_POST['action'] === 'uploadImage') {
            debugLog("Image upload detected");
            
            $reportId = $_POST['reportId'] ?? null;
            
            if (!$reportId) {
                throw new Exception('Report ID is required for image upload');
            }
            
            $file = $_FILES['feedbackImage'];
            debugLog("File details: " . print_r($file, true));

            // Check for upload errors
            if ($file['error'] !== UPLOAD_ERR_OK) {
                $errorMessages = [
                    UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize',
                    UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE',
                    UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
                    UPLOAD_ERR_NO_FILE => 'No file was uploaded',
                    UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
                    UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
                    UPLOAD_ERR_EXTENSION => 'File upload stopped by extension'
                ];
                
                $errorMsg = $errorMessages[$file['error']] ?? 'Unknown upload error';
                throw new Exception('Upload error: ' . $errorMsg);
            }

            // Validate file type
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (!in_array($file['type'], $allowedTypes)) {
                throw new Exception('Invalid file type. Only JPEG, PNG, GIF, and WebP are allowed.');
            }

            // Validate file size (2MB limit)
            if ($file['size'] > 2 * 1024 * 1024) {
                throw new Exception('File size must be less than 2MB.');
            }

            // Create upload directory if it doesn't exist
            $uploadDir = 'uploads/';
            if (!is_dir($uploadDir)) {
                if (!mkdir($uploadDir, 0755, true)) {
                    throw new Exception('Failed to create upload directory');
                }
            }

            // Check if directory is writable
            if (!is_writable($uploadDir)) {
                throw new Exception('Upload directory is not writable. Please check permissions.');
            }

            // Generate safe filename
            $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $safeFileName = 'report_' . $reportId . '_' . time() . '_' . uniqid() . '.' . $fileExtension;
            $destPath = $uploadDir . $safeFileName;

            debugLog("Attempting to move file from " . $file['tmp_name'] . " to " . $destPath);

            // Move uploaded file
            if (!move_uploaded_file($file['tmp_name'], $destPath)) {
                throw new Exception('Failed to save uploaded file. Check directory permissions.');
            }

            debugLog("File moved successfully to: " . $destPath);

            // FIXED: Store image in Messages table, try to update existing recent message first
            $pdo->beginTransaction();
            
            try {
                // Get UserID from Reports table
                $userQuery = "SELECT UserID FROM Reports WHERE ReportID = ?";
                $userStmt = $pdo->prepare($userQuery);
                $userStmt->execute([$reportId]);
                $userId = $userStmt->fetchColumn();
                
                if (!$userId) {
                    throw new Exception('Report not found');
                }
                
                // Check if there's a recent message (within last 5 minutes) that can be updated with the image
                $recentMessageQuery = "SELECT MessageID FROM Messages 
                                     WHERE ReportID = ? AND CreatedAt >= DATE_SUB(NOW(), INTERVAL 5 MINUTE) 
                                     ORDER BY CreatedAt DESC LIMIT 1";
                $recentStmt = $pdo->prepare($recentMessageQuery);
                $recentStmt->execute([$reportId]);
                $recentMessageId = $recentStmt->fetchColumn();
                
                if ($recentMessageId) {
                    // Update existing recent message with the image
                    $updateMsgQuery = "UPDATE Messages SET FeedbackImage = ? WHERE MessageID = ?";
                    $messageStmt = $pdo->prepare($updateMsgQuery);
                    $result = $messageStmt->execute([$safeFileName, $recentMessageId]);
                    debugLog("Updated existing message ID $recentMessageId with image: " . $safeFileName);
                } else {
                    // Create new message entry with just the image
                    $insertMsgQuery = "INSERT INTO Messages (ReportID, UserID, AdminID, FeedbackImage, CreatedAt) 
                                      VALUES (?, ?, ?, ?, NOW())";
                    $messageStmt = $pdo->prepare($insertMsgQuery);
                    $result = $messageStmt->execute([$reportId, $userId, 1, $safeFileName]);
                    debugLog("Created new message with image: " . $safeFileName);
                }
                
                if (!$result) {
                    throw new Exception('Failed to store image reference in Messages table');
                }
                
                $pdo->commit();
                debugLog("Image stored successfully in Messages table with filename: " . $safeFileName);

                sendJsonResponse([
                    'success' => true, 
                    'message' => 'Image uploaded and stored in Messages table successfully',
                    'fileName' => $safeFileName,
                    'filePath' => $uploadDir . $safeFileName
                ]);
                
            } catch (Exception $e) {
                $pdo->rollback();
                // Clean up uploaded file if database operation fails
                if (file_exists($destPath)) {
                    unlink($destPath);
                }
                throw $e;
            }
        }
        
        // Handle JSON requests
        if (strpos($contentType, 'application/json') !== false) {
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('Invalid JSON data');
            }
            
            $action = $input['action'] ?? '';
            debugLog("JSON request with action: " . $action);

            if ($action === 'updateStatus') {
                $reportId = $input['reportId'] ?? null;
                $newStatus = $input['status'] ?? null;
                $feedback = $input['feedback'] ?? '';

                debugLog("Update request - ReportID: $reportId, Status: $newStatus, Feedback: $feedback");

                // Validate required fields
                if (!$reportId) {
                    throw new Exception('Missing reportId');
                }
                
                if (!$newStatus) {
                    throw new Exception('Missing status');
                }

                $validStatuses = ['Pending', 'In Progress', 'Completed', 'Rejected'];
                if (!in_array($newStatus, $validStatuses)) {
                    throw new Exception('Invalid status value: ' . $newStatus);
                }

                $pdo->beginTransaction();

                try {
                    // Update report status
                    $stmt = $pdo->prepare("UPDATE Reports SET Status = ? WHERE ReportID = ?");
                    $stmt->execute([$newStatus, $reportId]);
                    
                    if ($stmt->rowCount() === 0) {
                        throw new Exception('Report not found or no changes made');
                    }

                    // FIXED: Check if there's a pending message for this report and update it
                    // Otherwise create a new message entry only if feedback is provided
                    if (!empty($feedback)) {
                        $userQuery = "SELECT UserID FROM Reports WHERE ReportID = ?";
                        $stmt = $pdo->prepare($userQuery);
                        $stmt->execute([$reportId]);
                        $userId = $stmt->fetchColumn();

                        if ($userId) {
                            // Check if there's a recent message (within last 5 minutes) that can be updated
                            $recentMessageQuery = "SELECT MessageID FROM Messages 
                                                 WHERE ReportID = ? AND CreatedAt >= DATE_SUB(NOW(), INTERVAL 5 MINUTE) 
                                                 ORDER BY CreatedAt DESC LIMIT 1";
                            $stmt = $pdo->prepare($recentMessageQuery);
                            $stmt->execute([$reportId]);
                            $recentMessageId = $stmt->fetchColumn();
                            
                            if ($recentMessageId) {
                                // Update existing recent message with feedback
                                $updateMsgQuery = "UPDATE Messages SET Feedback = ? WHERE MessageID = ?";
                                $stmt = $pdo->prepare($updateMsgQuery);
                                $stmt->execute([$feedback, $recentMessageId]);
                            } else {
                                // Create new message entry
                                $insertMsgQuery = "INSERT INTO Messages (ReportID, UserID, AdminID, Feedback, CreatedAt) VALUES (?, ?, ?, ?, NOW())";
                                $stmt = $pdo->prepare($insertMsgQuery);
                                $stmt->execute([$reportId, $userId, 1, $feedback]);
                            }
                        }
                    }

                    $pdo->commit();
                    sendJsonResponse(['success' => true, 'message' => 'Report updated successfully']);
                    
                } catch (Exception $e) {
                    $pdo->rollback();
                    throw $e;
                }
            }

            if ($action === 'clearImage') {
                $reportId = $input['reportId'] ?? null;
                
                if (!$reportId) {
                    throw new Exception('Report ID is required');
                }

                $pdo->beginTransaction();
                
                try {
                    // Get the most recent image from Messages table for this report
                    $stmt = $pdo->prepare("SELECT FeedbackImage FROM Messages WHERE ReportID = ? AND FeedbackImage IS NOT NULL ORDER BY CreatedAt DESC LIMIT 1");
                    $stmt->execute([$reportId]);
                    $currentImage = $stmt->fetchColumn();

                    if ($currentImage && file_exists('uploads/' . $currentImage)) {
                        unlink('uploads/' . $currentImage);
                    }

                    // Clear the most recent image entry from Messages table
                    $stmt = $pdo->prepare("UPDATE Messages SET FeedbackImage = NULL WHERE ReportID = ? AND FeedbackImage IS NOT NULL ORDER BY CreatedAt DESC LIMIT 1");
                    $stmt->execute([$reportId]);

                    $pdo->commit();
                    sendJsonResponse(['success' => true, 'message' => 'Image cleared successfully from Messages table']);
                    
                } catch (Exception $e) {
                    $pdo->rollback();
                    throw $e;
                }
            }
        }
        
        // If we get here, it's an unhandled POST request
        throw new Exception('Invalid POST request or missing parameters');
    }

    // Handle GET request for dashboard data
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        debugLog("Processing GET request for dashboard data");
        
        // Calculate date range (last 24 hours)
        $yesterday = date('Y-m-d H:i:s', strtotime('-24 hours'));

        // Get counts for last 24 hours
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM Reports WHERE CreatedAt >= ?");
        $stmt->execute([$yesterday]);
        $totalReports = (int)$stmt->fetchColumn();

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM Reports WHERE Status != 'Pending' AND CreatedAt >= ?");
        $stmt->execute([$yesterday]);
        $totalProgress = (int)$stmt->fetchColumn();

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM Reports WHERE Status = 'Completed' AND CreatedAt >= ?");
        $stmt->execute([$yesterday]);
        $totalCompleted = (int)$stmt->fetchColumn();

        // Get all-time totals for percentage calculation
        $stmt = $pdo->query("SELECT COUNT(*) FROM Reports");
        $allTimeReports = max((int)$stmt->fetchColumn(), 1);

        // Calculate percentages
        $percentages = [
            'reportsPercent' => round(($totalReports / $allTimeReports) * 100),
            'progressPercent' => round(($totalProgress / $allTimeReports) * 100),
            'completedPercent' => round(($totalCompleted / $allTimeReports) * 100),
        ];

        // FIXED: Get recent reports with latest feedback image from Messages table
        $recentReportsQuery = "
            SELECT 
                r.ReportID, 
                u.Username, 
                u.Email, 
                u.PhoneNumber, 
                r.Status, 
                DATE_FORMAT(r.CreatedAt, '%Y-%m-%d %H:%i:%s') as CreatedAt,
                r.Description,
                COALESCE(
                    (SELECT m.Feedback 
                     FROM Messages m 
                     WHERE m.ReportID = r.ReportID AND m.Feedback IS NOT NULL
                     ORDER BY m.CreatedAt DESC 
                     LIMIT 1), 
                    ''
                ) as Feedback,
                COALESCE(
                    (SELECT m.FeedbackImage 
                     FROM Messages m 
                     WHERE m.ReportID = r.ReportID AND m.FeedbackImage IS NOT NULL
                     ORDER BY m.CreatedAt DESC 
                     LIMIT 1), 
                    NULL
                ) as FeedbackImage
            FROM Reports r 
            JOIN Users u ON r.UserID = u.UserID 
            ORDER BY r.CreatedAt DESC 
            LIMIT 10
        ";
        
        $stmt = $pdo->query($recentReportsQuery);
        $recentReports = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $response = [
            'success' => true,
            'data' => [
                'totalReports' => $totalReports,
                'totalProgress' => $totalProgress,
                'totalCompleted' => $totalCompleted,
                'percentages' => $percentages,
                'recentReports' => $recentReports,
                'chartData' => [
                    'labels' => ['Reports', 'Progress', 'Completed'],
                    'values' => [$totalReports, $totalProgress, $totalCompleted]
                ]
            ],
            'timestamp' => date('Y-m-d H:i:s')
        ];

        sendJsonResponse($response);
    }

    // Invalid request method
    throw new Exception('Invalid request method: ' . $_SERVER['REQUEST_METHOD']);

} catch (Exception $e) {
    debugLog("Error: " . $e->getMessage());
    debugLog("Stack trace: " . $e->getTraceAsString());
    
    sendJsonResponse([
        'success' => false,
        'error' => $e->getMessage(),
        'data' => []
    ], 500);
}
?>