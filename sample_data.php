<?php
// sample_data.php - Populates database with sample data for testing

// Database configuration
$servername = "localhost";
$username = "root";
$password = "Makungu@0608";
$dbname = "WebWizards";

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "<h2>Populating WebWizards Database with Sample Data...</h2>";

    // Sample Users
    $users = [
        ['John Doe', 'john@example.com', '0712345678', 'johndoe', 'password123'],
        ['Jane Smith', 'jane@example.com', '0723456789', 'janesmith', 'password123'],
        ['Mike Johnson', 'mike@example.com', '0734567890', 'mikejohnson', 'password123'],
        ['Sarah Wilson', 'sarah@example.com', '0745678901', 'sarahwilson', 'password123'],
        ['David Brown', 'david@example.com', '0756789012', 'davidbrown', 'password123'],
        ['Lisa Davis', 'lisa@example.com', '0767890123', 'lisadavis', 'password123'],
        ['Tom Anderson', 'tom@example.com', '0778901234', 'tomanderson', 'password123'],
        ['Emma Taylor', 'emma@example.com', '0789012345', 'emmataylor', 'password123']
    ];

    // Insert Users
    $userQuery = "INSERT INTO Users (FullName, Email, PhoneNumber, Username, Password) VALUES (?, ?, ?, ?, ?)";
    $userStmt = $pdo->prepare($userQuery);

    foreach ($users as $user) {
        try {
            $hashedPassword = password_hash($user[4], PASSWORD_DEFAULT);
            $userStmt->execute([$user[0], $user[1], $user[2], $user[3], $hashedPassword]);
        } catch (PDOException $e) {
            if ($e->getCode() != 23000) { // Ignore duplicate entry errors
                throw $e;
            }
        }
    }
    echo "<p>✓ Sample users created</p>";

    // Get user IDs for reports
    $userIds = [];
    $getUsersQuery = "SELECT UserID FROM Users LIMIT 8";
    $result = $pdo->query($getUsersQuery);
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        $userIds[] = $row['UserID'];
    }

    // Sample Reports with different statuses and timestamps
    $reports = [
        // Recent reports (last 24 hours)
        [$userIds[0] ?? 1, 'pothole_main_street.jpg', 'Main Street, City Center', 'Large pothole causing traffic issues', 'Pending'],
        [$userIds[1] ?? 2, 'broken_streetlight.jpg', 'Oak Avenue, Residential Area', 'Street light not working for 3 days', 'In Progress'],
        [$userIds[2] ?? 3, 'water_leak.jpg', 'Park Road, Near School', 'Water pipe leak creating puddles', 'Completed'],
        [$userIds[3] ?? 4, 'graffiti_wall.jpg', 'Shopping Mall, West Side', 'Vandalism on public wall', 'Pending'],
        [$userIds[4] ?? 5, 'traffic_light_broken.jpg', 'Highway Intersection', 'Traffic light stuck on red', 'In Progress'],
        
        // Older reports (for total counts)
        [$userIds[5] ?? 6, 'sidewalk_crack.jpg', 'Elm Street, Downtown', 'Dangerous crack in sidewalk', 'Completed'],
        [$userIds[6] ?? 7, 'garbage_overflow.jpg', 'Central Park', 'Overflowing garbage bins', 'Completed'],
        [$userIds[7] ?? 8, 'road_sign_damaged.jpg', 'First Avenue', 'Stop sign bent and hard to read', 'Rejected'],
        [$userIds[0] ?? 1, 'fence_damage.jpg', 'Community Center', 'Damaged fence around playground', 'Completed'],
        [$userIds[1] ?? 2, 'drain_blocked.jpg', 'Market Square', 'Storm drain completely blocked', 'Pending']
    ];

    // Insert Reports
    $reportQuery = "INSERT INTO Reports (UserID, Image, Location, Description, Status, CreatedAt) VALUES (?, ?, ?, ?, ?, ?)";
    $reportStmt = $pdo->prepare($reportQuery);

    for ($i = 0; $i < count($reports); $i++) {
        $report = $reports[$i];
        
        // Create different timestamps
        if ($i < 5) {
            // Recent reports (last 24 hours)
            $createdAt = date('Y-m-d H:i:s', strtotime('-' . rand(1, 23) . ' hours'));
        } else {
            // Older reports
            $createdAt = date('Y-m-d H:i:s', strtotime('-' . rand(2, 30) . ' days'));
        }
        
        $reportStmt->execute([
            $report[0], $report[1], $report[2], $report[3], $report[4], $createdAt
        ]);
    }
    echo "<p>✓ Sample reports created</p>";

    // Create some premium users
    $premiumQuery = "INSERT INTO PremiumUser (UserID, PointsBalance, TotalReports) VALUES (?, ?, ?)";
    $premiumStmt = $pdo->prepare($premiumQuery);

    for ($i = 0; $i < 3; $i++) {
        if (isset($userIds[$i])) {
            try {
                $premiumStmt->execute([$userIds[$i], rand(50, 200), rand(5, 15)]);
            } catch (PDOException $e) {
                if ($e->getCode() != 23000) { // Ignore duplicate entry errors
                    throw $e;
                }
            }
        }
    }
    echo "<p>✓ Premium users created</p>";

    // Create points for completed reports
    $completedReportsQuery = "SELECT ReportID, UserID FROM Reports WHERE Status = 'Completed'";
    $completedResult = $pdo->query($completedReportsQuery);
    
    $pointsQuery = "INSERT INTO Points (UserID, ReportID, PointsEarned) VALUES (?, ?, ?)";
    $pointsStmt = $pdo->prepare($pointsQuery);
    
    while ($completed = $completedResult->fetch(PDO::FETCH_ASSOC)) {
        try {
            $pointsStmt->execute([$completed['UserID'], $completed['ReportID'], 20]);
        } catch (PDOException $e) {
            if ($e->getCode() != 23000) { // Ignore duplicate entry errors
                throw $e;
            }
        }
    }
    echo "<p>✓ Points awarded for completed reports</p>";

    // Sample contact messages
    $contactMessages = [
        ['Alice Cooper', 'alice@email.com', 'Great service! The pothole on my street was fixed within 2 days.'],
        ['Bob Martin', 'bob@email.com', 'How do I report multiple issues in the same area?'],
        ['Carol Johnson', 'carol@email.com', 'The mobile app is very user-friendly. Thanks!'],
        ['Dan Wilson', 'dan@email.com', 'When will the premium features be available?']
    ];

    $contactQuery = "INSERT INTO ContactMessages (SenderName, SenderEmail, Message, SentAt) VALUES (?, ?, ?, ?)";
    $contactStmt = $pdo->prepare($contactQuery);

    foreach ($contactMessages as $message) {
        $sentAt = date('Y-m-d H:i:s', strtotime('-' . rand(1, 7) . ' days'));
        $contactStmt->execute([$message[0], $message[1], $message[2], $sentAt]);
    }
    echo "<p>✓ Sample contact messages created</p>";

    // Sample chatbot messages
    $chatbotQuery = "INSERT INTO ChatbotMessages (UserID, MessageContent, IsUser, CreatedAt) VALUES (?, ?, ?, ?)";
    $chatbotStmt = $pdo->prepare($chatbotQuery);

    $chatMessages = [
        [$userIds[0] ?? 1, 'How do I report a pothole?', true],
        [$userIds[0] ?? 1, 'You can report a pothole by clicking the "Report Issue" button and selecting the pothole category. Make sure to include a photo and exact location.', false],
        [$userIds[1] ?? 2, 'What happens after I submit a report?', true],
        [$userIds[1] ?? 2, 'After submission, your report will be reviewed by our team within 24 hours. You\'ll receive updates on the progress via email.', false],
        [$userIds[2] ?? 3, 'How can I earn points?', true],
        [$userIds[2] ?? 3, 'You earn 20 points for each verified report that gets completed. Points can be redeemed for vouchers once you reach 100 points.', false]
    ];

    foreach ($chatMessages as $chat) {
        $createdAt = date('Y-m-d H:i:s', strtotime('-' . rand(1, 5) . ' days'));
        $chatbotStmt->execute([$chat[0], $chat[1], $chat[2], $createdAt]);
    }
    echo "<p>✓ Sample chatbot messages created</p>";

    // Display summary statistics
    echo "<br><h3>Database Summary:</h3>";
    
    $summaryQueries = [
        'Total Users' => "SELECT COUNT(*) as count FROM Users",
        'Total Reports' => "SELECT COUNT(*) as count FROM Reports", 
        'Pending Reports' => "SELECT COUNT(*) as count FROM Reports WHERE Status = 'Pending'",
        'In Progress Reports' => "SELECT COUNT(*) as count FROM Reports WHERE Status = 'In Progress'",
        'Completed Reports' => "SELECT COUNT(*) as count FROM Reports WHERE Status = 'Completed'",
        'Reports (Last 24h)' => "SELECT COUNT(*) as count FROM Reports WHERE CreatedAt >= DATE_SUB(NOW(), INTERVAL 24 HOUR)",
        'Premium Users' => "SELECT COUNT(*) as count FROM PremiumUser",
        'Total Points Awarded' => "SELECT SUM(PointsEarned) as total FROM Points"
    ];

    foreach ($summaryQueries as $label => $query) {
        $result = $pdo->query($query)->fetch(PDO::FETCH_ASSOC);
        $count = $result['count'] ?? $result['total'] ?? 0;
        echo "<p><strong>$label:</strong> $count</p>";
    }

    echo "<br><p style='color: green; font-weight: bold;'>✅ Sample data population completed successfully!</p>";
    echo "<p>You can now test your dashboard with real data from the database.</p>";
    echo "<p><a href='dashboard.html'>Go to Dashboard</a></p>";

} catch (PDOException $e) {
    echo "<p style='color: red;'><strong>Database Error:</strong> " . $e->getMessage() . "</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'><strong>Error:</strong> " . $e->getMessage() . "</p>";
}
?>