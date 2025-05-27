<?php
// dashboard_data.php - Fetches real-time data from WebWizards database

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

// Database configuration
$servername = "localhost";
$username = "root"; 
$password = "LockIn_78"; 
$dbname = "WebWizards";

try {
    // Create PDO connection
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get current date for "Last 24 hours" filter
    $yesterday = date('Y-m-d H:i:s', strtotime('-24 hours'));

    // Query for dashboard statistics
    $queries = [
        // Total reports in last 24 hours
        'totalReports' => "SELECT COUNT(*) as count FROM Reports WHERE CreatedAt >= ?",
        
        // Total progress (reports that are not pending)
        'totalProgress' => "SELECT COUNT(*) as count FROM Reports WHERE Status != 'Pending' AND CreatedAt >= ?",
        
        // Total completed reports
        'totalCompleted' => "SELECT COUNT(*) as count FROM Reports WHERE Status = 'Completed' AND CreatedAt >= ?",
        
        // All time totals for comparison
        'allTimeReports' => "SELECT COUNT(*) as count FROM Reports",
        'allTimeProgress' => "SELECT COUNT(*) as count FROM Reports WHERE Status != 'Pending'",
        'allTimeCompleted' => "SELECT COUNT(*) as count FROM Reports WHERE Status = 'Completed'"
    ];

    $results = [];
    
    // Execute queries with 24-hour filter
    foreach (['totalReports', 'totalProgress', 'totalCompleted'] as $key) {
        $stmt = $pdo->prepare($queries[$key]);
        $stmt->execute([$yesterday]);
        $results[$key] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    }
    
    // Execute all-time queries (no parameters needed)
    foreach (['allTimeReports', 'allTimeProgress', 'allTimeCompleted'] as $key) {
        $stmt = $pdo->prepare($queries[$key]);
        $stmt->execute();
        $results[$key] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    }

    // Calculate percentages for progress circles
    $totalReports = max($results['allTimeReports'], 1); // Avoid division by zero
    
    $percentages = [
        'reportsPercent' => round(($results['totalReports'] / $totalReports) * 100),
        'progressPercent' => round(($results['totalProgress'] / $totalReports) * 100), 
        'completedPercent' => round(($results['totalCompleted'] / $totalReports) * 100)
    ];

    // Get recent reports for the table
    $recentReportsQuery = "
        SELECT r.ReportID, u.Username, u.Email, u.PhoneNumber, r.Status, r.CreatedAt, r.Description
        FROM Reports r 
        JOIN Users u ON r.UserID = u.UserID 
        ORDER BY r.CreatedAt DESC 
        LIMIT 10
    ";
    
    $stmt = $pdo->prepare($recentReportsQuery);
    $stmt->execute();
    $recentReports = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get status distribution for pie chart
    $statusQuery = "
        SELECT Status, COUNT(*) as count 
        FROM Reports 
        WHERE CreatedAt >= ? 
        GROUP BY Status
    ";
    
    $stmt = $pdo->prepare($statusQuery);
    $stmt->execute([$yesterday]);
    $statusDistribution = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Prepare response
    $response = [
        'success' => true,
        'data' => [
            'totalReports' => $results['totalReports'],
            'totalProgress' => $results['totalProgress'], 
            'totalCompleted' => $results['totalCompleted'],
            'percentages' => $percentages,
            'recentReports' => $recentReports,
            'statusDistribution' => $statusDistribution,
            'chartData' => [
                'labels' => ['Reports', 'Progress', 'Completed'],
                'values' => [
                    $results['totalReports'],
                    $results['totalProgress'],
                    $results['totalCompleted']
                ]
            ]
        ],
        'timestamp' => date('Y-m-d H:i:s')
    ];

    echo json_encode($response);

} catch(PDOException $e) {
    // Error handling
    $error_response = [
        'success' => false,
        'error' => 'Database connection failed: ' . $e->getMessage(),
        'data' => [
            'totalReports' => 0,
            'totalProgress' => 0,
            'totalCompleted' => 0,
            'percentages' => [
                'reportsPercent' => 0,
                'progressPercent' => 0,
                'completedPercent' => 0
            ],
            'recentReports' => [],
            'statusDistribution' => [],
            'chartData' => [
                'labels' => ['Reports', 'Progress', 'Completed'],
                'values' => [0, 0, 0]
            ]
        ]
    ];
    
    http_response_code(500);
    echo json_encode($error_response);
}

// Additional endpoint for updating specific report status
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (isset($input['action']) && $input['action'] === 'updateStatus') {
        try {
            $reportId = $input['reportId'];
            $newStatus = $input['status'];
            $feedback = $input['feedback'] ?? '';
            
            $updateQuery = "UPDATE Reports SET Status = ? WHERE ReportID = ?";
            $stmt = $pdo->prepare($updateQuery);
            $stmt->execute([$newStatus, $reportId]);
            
            // You can also store feedback in a separate table if needed
            if (!empty($feedback)) {
                // Add feedback storage logic here if you have a feedback table
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'Report status updated successfully'
            ]);
            
        } catch(PDOException $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Failed to update report: ' . $e->getMessage()
            ]);
        }
    }
}
?>