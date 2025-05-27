<?php
// update_report.php - Handles report status updates and feedback

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Database configuration
$servername = "localhost";
$username = "root";
$password = "LockIn_78";
$dbname = "WebWizards";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        throw new Exception('Invalid JSON input');
    }

    $action = $input['action'] ?? '';

    switch ($action) {
        case 'updateStatus':
            $reportId = $input['reportId'] ?? null;
            $newStatus = $input['status'] ?? null;
            $feedback = $input['feedback'] ?? '';

            if (!$reportId || !$newStatus) {
                throw new Exception('Report ID and status are required');
            }

            // Validate status
            $validStatuses = ['Pending', 'In Progress', 'Completed', 'Rejected'];
            if (!in_array($newStatus, $validStatuses)) {
                throw new Exception('Invalid status value');
            }

            // Update report status
            $updateQuery = "UPDATE Reports SET Status = ? WHERE ReportID = ?";
            $stmt = $pdo->prepare($updateQuery);
            $result = $stmt->execute([$newStatus, $reportId]);

            if (!$result) {
                throw new Exception('Failed to update report status');
            }

            // If feedback provided, you might want to store it
            // This assumes you have a feedback table or column
            if (!empty($feedback)) {
                // Add feedback storage logic here
                // For example, if you have a Feedback table:
                /*
                $feedbackQuery = "INSERT INTO Feedback (ReportID, FeedbackText, CreatedAt) VALUES (?, ?, NOW())";
                $feedbackStmt = $pdo->prepare($feedbackQuery);
                $feedbackStmt->execute([$reportId, $feedback]);
                */
            }

            // If status is completed, award points to user
            if ($newStatus === 'Completed') {
                $pointsQuery = "
                    INSERT INTO Points (UserID, ReportID, PointsEarned) 
                    SELECT r.UserID, r.ReportID, 20 
                    FROM Reports r 
                    WHERE r.ReportID = ? 
                    ON DUPLICATE KEY UPDATE PointsEarned = PointsEarned
                ";
                $pointsStmt = $pdo->prepare($pointsQuery);
                $pointsStmt->execute([$reportId]);

                // Update premium user points balance
                $updatePointsQuery = "
                    UPDATE PremiumUser p 
                    JOIN Reports r ON p.UserID = r.UserID 
                    SET p.PointsBalance = p.PointsBalance + 20,
                        p.TotalReports = p.TotalReports + 1
                    WHERE r.ReportID = ? AND p.UserID = r.UserID
                ";
                $updatePointsStmt = $pdo->prepare($updatePointsQuery);
                $updatePointsStmt->execute([$reportId]);
            }

            echo json_encode([
                'success' => true,
                'message' => 'Report status updated successfully',
                'reportId' => $reportId,
                'newStatus' => $newStatus
            ]);
            break;

        case 'saveFeedback':
            $reportId = $input['reportId'] ?? null;
            $feedback = $input['feedback'] ?? '';

            if (!$reportId || empty($feedback)) {
                throw new Exception('Report ID and feedback are required');
            }

            // Store feedback (assuming you create a Feedback table)
            // For now, we'll update the Reports table with a feedback column
            // You might want to alter your Reports table to add a Feedback column:
            // ALTER TABLE Reports ADD COLUMN Feedback TEXT;
            
            $feedbackQuery = "UPDATE Reports SET Description = CONCAT(Description, '\n\nAdmin Feedback: ', ?) WHERE ReportID = ?";
            $stmt = $pdo->prepare($feedbackQuery);
            $result = $stmt->execute([$feedback, $reportId]);

            if (!$result) {
                throw new Exception('Failed to save feedback');
            }

            echo json_encode([
                'success' => true,
                'message' => 'Feedback saved successfully'
            ]);
            break;

        case 'getReportDetails':
            $reportId = $input['reportId'] ?? null;

            if (!$reportId) {
                throw new Exception('Report ID is required');
            }

            $query = "
                SELECT r.*, u.FullName, u.Email, u.PhoneNumber, u.Username
                FROM Reports r
                JOIN Users u ON r.UserID = u.UserID
                WHERE r.ReportID = ?
            ";
            
            $stmt = $pdo->prepare($query);
            $stmt->execute([$reportId]);
            $report = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$report) {
                throw new Exception('Report not found');
            }

            echo json_encode([
                'success' => true,
                'data' => $report
            ]);
            break;

        default:
            throw new Exception('Invalid action');
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>