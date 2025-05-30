<?php
// Database connection
$servername = "localhost";
$username = "root";
<<<<<<< Updated upstream
<<<<<<< Updated upstream
$password = "LockIn_78"; // Updated to match your login code
=======
$password = "Makungu@0608";
>>>>>>> Stashed changes
=======
$password = ""; // Updated to match your login code
>>>>>>> Stashed changes
$dbname = "WebWizards";

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage() . "<br>Please check your database credentials.");
}

// Check if user is logged in - matching your login code session variables
session_start();
$isPremium = $_SESSION['isPremium'] ?? false;
if (!isset($_SESSION['user_id'])) {
    die("Please log in to view feedback.");
}

$currentUserID = $_SESSION['user_id']; // Changed from 'UserID' to 'user_id' to match your login code

// Query to fetch all feedback related to the current user's reports with admin information
// Added ROW_NUMBER() to create sequential report numbers for this user only
$sql = "SELECT 
    m.MessageID,
    m.Feedback,
    m.FeedbackImage,
    m.CreatedAt as FeedbackTime,
    r.ReportID,
    r.Image as ReportImage,
    r.Description as ReportDescription,
    r.Location,
    r.Status,
    r.CreatedAt as ReportTime,
    a.FullName as AdminName,
    a.Email as AdminEmail,
    ROW_NUMBER() OVER (ORDER BY r.CreatedAt ASC) as UserReportNumber
FROM Messages m
INNER JOIN Reports r ON m.ReportID = r.ReportID
LEFT JOIN Admins a ON m.AdminID = a.AdminID
WHERE r.UserID = :userID
ORDER BY m.CreatedAt DESC";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':userID', $currentUserID, PDO::PARAM_INT);
    $stmt->execute();
    $feedbacks = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Query failed: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Feedback - WebWizards</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #0f0f0f 0%, #000000 50%, #33200a 100%);
            min-height: 100vh;
            padding: 20px;
        }

        /* Back Button Styling */
        .BackBtn {
            position: fixed;
            top: 20px;
            left: 20px;
            background: linear-gradient(135deg, #FFA333, #FF8C00);
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 25px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            z-index: 1000;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(255, 163, 51, 0.3);
            border: 2px solid transparent;
        }

        .BackBtn:hover {
            background: linear-gradient(135deg, #FF8C00, #FFA333);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 163, 51, 0.4);
            border-color: rgba(255, 255, 255, 0.2);
        }

        .BackBtn:active {
            transform: translateY(0);
            box-shadow: 0 2px 10px rgba(255, 163, 51, 0.3);
        }

        .BackBtn svg {
            width: 18px;
            height: 18px;
            fill: currentColor;
            transition: transform 0.3s ease;
        }

        .BackBtn:hover svg {
            transform: translateX(-2px);
        }

        .BackBtn span {
            font-family: inherit;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: rgba(26, 26, 26, 0.95);
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            border: 1px solid #333;
            margin-top: 80px; /* Add top margin to account for fixed back button */
        }

        .header {
            text-align: center;
            margin-bottom: 40px;
            color: #FFA333;
        }

        .header h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
            background: linear-gradient(45deg, #FFA333, #FFD700);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .header p {
            color: #ccc;
        }

        .feedback-card {
            background: #2d2d2d;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.4);
            border-left: 5px solid #FFA333;
            border: 1px solid #404040;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .feedback-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 35px rgba(255, 163, 51, 0.2);
            border-color: #FFA333;
        }

        .feedback-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .admin-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .admin-avatar {
            width: 40px;
            height: 40px;
            background: linear-gradient(45deg, #FFA333, #FF8C00);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 16px;
            border: 2px solid #fff;
            box-shadow: 0 2px 8px rgba(255, 163, 51, 0.3);
        }

        .admin-details h3 {
            color: #FFA333;
            font-size: 1.1em;
            margin-bottom: 2px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .admin-badge {
            background: #FFA333;
            color: #1a1a1a;
            font-size: 0.7em;
            padding: 2px 6px;
            border-radius: 10px;
            font-weight: bold;
        }

        .admin-email {
            color: #bbb;
            font-size: 0.9em;
        }

        .timestamp {
            color: #FFA333;
            font-size: 0.9em;
            background: #1a1a1a;
            padding: 5px 12px;
            border-radius: 20px;
            border: 1px solid #FFA333;
        }

        .report-info {
            background: linear-gradient(145deg, #1a1a1a 0%, #2d2d2d 100%);
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            border: 1px solid #FFA333;
        }

        .report-info h4 {
            color: #FFA333;
            margin-bottom: 10px;
        }

        .report-info p {
            color: #ccc;
        }

        .report-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .report-id {
            background: linear-gradient(45deg, #1a1a1a, #333);
            color: #FFA333;
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 0.9em;
            font-weight: bold;
            border: 1px solid #FFA333;
        }

        .status-badge {
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 0.8em;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-pending { background: #4D2800; color: #FFA333; border: 1px solid #FFA333; }
        .status-resolved { background: #1B4D1B; color: #4CAF50; border: 1px solid #4CAF50; }
        .status-in-progress { background: #2D1F00; color: #FFD700; border: 1px solid #FFD700; }

        .image-gallery {
            display: flex;
            gap: 15px;
            margin: 15px 0;
            flex-wrap: wrap;
        }

        .image-container {
            position: relative;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(255, 163, 51, 0.2);
            border: 2px solid transparent;
            transition: all 0.3s ease;
        }

        .image-container:hover {
            border-color: #FFA333;
            box-shadow: 0 6px 20px rgba(255, 163, 51, 0.3);
        }

        .report-image, .feedback-image {
            max-width: 200px;
            max-height: 150px;
            object-fit: cover;
            cursor: pointer;
            transition: transform 0.3s ease;
        }

        .report-image:hover, .feedback-image:hover {
            transform: scale(1.05);
        }

        .image-label {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(transparent, rgba(26, 26, 26, 0.8));
            color: #FFA333;
            padding: 8px;
            font-size: 0.8em;
            text-align: center;
            font-weight: bold;
        }

        .feedback-content {
            background: #1a1a1a;
            border-radius: 8px;
            padding: 20px;
            border-left: 3px solid #FFA333;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
            border: 1px solid #333;
        }

        .feedback-content h4 {
            color: #FFA333;
            margin-bottom: 10px;
            font-size: 1.1em;
        }

        .feedback-text {
            color: #ffffff;
            line-height: 1.6;
            font-size: 1em;
        }

        .location-info {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #bbb;
            margin-top: 10px;
        }

        .no-feedback {
            text-align: center;
            padding: 60px 20px;
            color: #666;
            background: linear-gradient(145deg, #2d2d2d, #1a1a1a);
            border-radius: 15px;
            border: 2px dashed #FFA333;
        }

        .no-feedback h2 {
            color: #FFA333;
            margin-bottom: 15px;
        }

        .no-feedback img {
            width: 100px;
            opacity: 0.5;
            margin-bottom: 20px;
        }

        .no-admin-info {
            color: #999;
            font-style: italic;
        }

        @media (max-width: 768px) {
            .BackBtn {
                position: relative;
                top: 0;
                left: 0;
                margin-bottom: 20px;
                width: fit-content;
            }

            .container {
                padding: 20px;
                margin: 10px;
                margin-top: 20px;
            }

            .feedback-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }

            .report-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .image-gallery {
                flex-direction: column;
                align-items: center;
            }
        }
    </style>
</head>
<body>
    <button class="BackBtn" id="BackBtn">
        <svg height="16" width="16" xmlns="http://www.w3.org/2000/svg" version="1.1" viewBox="0 0 1024 1024">
            <path d="M874.690416 495.52477c0 11.2973-9.168824 20.466124-20.466124 20.466124l-604.773963 0 188.083679 188.083679c7.992021 7.992021 7.992021 20.947078 0 28.939099-4.001127 3.990894-9.240455 5.996574-14.46955 5.996574-5.239328 0-10.478655-1.995447-14.479783-5.996574l-223.00912-223.00912c-3.837398-3.837398-5.996574-9.046027-5.996574-14.46955 0-5.433756 2.159176-10.632151 5.996574-14.46955l223.019353-223.029586c7.992021-7.992021 20.957311-7.992021 28.949332 0 7.992021 8.002254 7.992021 20.957311 0 28.949332l-188.073446 188.073446 604.753497 0C865.521592 475.058646 874.690416 484.217237 874.690416 495.52477z"></path>
        </svg>
        <span>Back</span>
    </button>

    <div class="container">
        <div class="header">
            <h1>📋 Report Feedback</h1>
            <p>View feedback and responses for your submitted reports</p>
        </div>

        <?php if (empty($feedbacks)): ?>
            <div class="no-feedback">
                <h2>No Feedback Available</h2>
                <p>You haven't received any feedback on your reports yet.</p>
            </div>
        <?php else: ?>
            <?php foreach ($feedbacks as $feedback): ?>
                <div class="feedback-card">
                    <!-- Feedback Header -->
                    <div class="feedback-header">
                        <div class="admin-info">
                            <div class="admin-avatar">
                                <?php 
                                if (!empty($feedback['AdminName'])) {
                                    echo strtoupper(substr($feedback['AdminName'], 0, 1)); 
                                } else {
                                    echo '?';
                                }
                                ?>
                            </div>
                            <div class="admin-details">
                                <?php if (!empty($feedback['AdminName'])): ?>
                                    <h3>
                                        <?php echo htmlspecialchars($feedback['AdminName']); ?>
                                        <span class="admin-badge">ADMIN</span>
                                    </h3>
                                    <div class="admin-email"><?php echo htmlspecialchars($feedback['AdminEmail']); ?></div>
                                <?php else: ?>
                                    <h3 class="no-admin-info">System Response</h3>
                                    <div class="admin-email no-admin-info">Automated feedback</div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="timestamp">
                            📅 <?php echo date('M d, Y • H:i', strtotime($feedback['FeedbackTime'])); ?>
                        </div>
                    </div>

                    <!-- Original Report Info -->
                    <div class="report-info">
                        <div class="report-header">
                            <div class="report-id">Report #<?php echo $feedback['UserReportNumber']; ?></div>
                            <div class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $feedback['Status'])); ?>">
                                <?php echo htmlspecialchars($feedback['Status']); ?>
                            </div>
                        </div>
                        
                        <h4>📝 Report Description:</h4>
                        <p><?php echo htmlspecialchars($feedback['ReportDescription']); ?></p>
                        
                        <div class="location-info">
                            <span>📍</span>
                            <span><?php echo htmlspecialchars($feedback['Location']); ?></span>
                            <span style="margin-left: auto; color: #999;">
                                Reported: <?php echo date('M d, Y', strtotime($feedback['ReportTime'])); ?>
                            </span>
                        </div>

                        <!-- Images -->
                        <div class="image-gallery">
                            <?php if (!empty($feedback['ReportImage'])): ?>
                                <div class="image-container">
                                    <img src="<?php echo htmlspecialchars($feedback['ReportImage']); ?>" 
                                         alt="Report Image" 
                                         class="report-image"
                                         onclick="openImageModal('<?php echo htmlspecialchars($feedback['ReportImage']); ?>')"
                                         onerror="this.style.display='none'; this.nextElementSibling.innerHTML='❌ Report Image Not Found';">
                                    <div class="image-label">Original Report</div>
                                </div>
                            <?php else: ?>
                                <div class="image-container" style="background: #2d2d2d; padding: 20px; text-align: center; color: #bbb; border: 1px dashed #555;">
                                    <p>📷 No Report Image</p>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($feedback['FeedbackImage'])): ?>
                                <div class="image-container">
                                    <?php 
                                    // Fix the feedback image path
                                    $feedbackImagePath = $feedback['FeedbackImage'];
                                    // Check if the path already contains a directory, if not add 'uploads/'
                                    if (!str_contains($feedbackImagePath, '/')) {
                                        $feedbackImagePath = 'uploads/' . $feedbackImagePath;
                                    }
                                    ?>
                                    <img src="<?php echo htmlspecialchars($feedbackImagePath); ?>" 
                                         alt="Feedback Image" 
                                         class="feedback-image"
                                         onclick="openImageModal('<?php echo htmlspecialchars($feedbackImagePath); ?>')"
                                         onerror="this.style.display='none'; this.nextElementSibling.innerHTML='❌ Feedback Image Not Found';">
                                    <div class="image-label">Feedback Image</div>
                                </div>
                            <?php else: ?>
                                <div class="image-container" style="background: #2d2d2d; padding: 20px; text-align: center; color: #bbb; border: 1px dashed #555;">
                                    <p>📷 No Feedback Image</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Feedback Content -->
                    <?php if (!empty($feedback['Feedback'])): ?>
                        <div class="feedback-content">
                            <h4>💬 Admin Feedback:</h4>
                            <div class="feedback-text">
                                <?php echo nl2br(htmlspecialchars($feedback['Feedback'])); ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Image Modal -->
    <div id="imageModal" style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.9); cursor: pointer;" onclick="closeImageModal()">
        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);">
            <img id="modalImage" style="max-width: 90vw; max-height: 90vh; border-radius: 8px;">
        </div>
        <span style="position: absolute; top: 20px; right: 30px; color: white; font-size: 40px; font-weight: bold; cursor: pointer;" onclick="closeImageModal()">&times;</span>
    </div>

    <script>
        const isPremium = <?php echo json_encode($isPremium); ?>;
        document.getElementById('BackBtn').addEventListener('click', () => {
          window.location.href = isPremium ? 'premium-dashboard.html' : 'userdashboard.html';
        });
        
        function openImageModal(src) {
            document.getElementById('imageModal').style.display = 'block';
            document.getElementById('modalImage').src = src;
        }

        function closeImageModal() {
            document.getElementById('imageModal').style.display = 'none';
        }

        // Close modal with Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeImageModal();
            }
        });
    </script>
</body>
</html>