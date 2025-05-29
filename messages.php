<?php 
session_start(); 
$isPremium = $_SESSION['isPremium'] ?? false;

// DB connection
$servername = "localhost";
$username = "root";
$password = "LockIn_78";
$dbname = "WebWizards";

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Only show admin feedback
$messageType = $_GET['type'] ?? '';
$reportID = $_GET['report_id'] ?? null;

$reportDetails = null;
$messages = [];

if ($messageType === 'feedback' && $reportID) {
    // First, get the report details including sender info
    $reportStmt = $pdo->prepare("SELECT r.*, u.FullName AS SenderName, u.Email AS SenderEmail 
                                FROM Reports r 
                                JOIN Users u ON r.UserID = u.UserID 
                                WHERE r.ReportID = ?");
    $reportStmt->execute([$reportID]);
    $reportDetails = $reportStmt->fetch(PDO::FETCH_ASSOC);
    
    // Then get all feedback messages for this report
    $messageStmt = $pdo->prepare("SELECT m.*, a.FullName AS AdminName 
                                 FROM Messages m 
                                 JOIN Admins a ON m.AdminID = a.AdminID 
                                 WHERE m.ReportID = ? AND m.AdminID IS NOT NULL
                                 ORDER BY m.CreatedAt DESC");
    $messageStmt->execute([$reportID]);
    $messages = $messageStmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Report Details & Admin Feedback</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: "Segoe UI", sans-serif;
            margin: 20px;
            background: #f5f5f5;
            color: #333;
        }
        .container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 20px;
        }
        .report-header {
            border-bottom: 2px solid #e9ecef;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .report-title {
            color: #007bff;
            margin-bottom: 10px;
            font-size: 1.5em;
        }
        .report-meta {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 10px;
            margin-bottom: 15px;
            font-size: 0.9em;
            color: #666;
        }
        .meta-item {
            background: #f8f9fa;
            padding: 8px 12px;
            border-radius: 6px;
        }
        .meta-label {
            font-weight: bold;
            display: inline-block;
            min-width: 80px;
        }
        .status {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8em;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status.pending { background: #fff3cd; color: #856404; }
        .status.approved { background: #d4edda; color: #155724; }
        .status.rejected { background: #f8d7da; color: #721c24; }
        .report-description {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
            line-height: 1.6;
        }
        .report-image {
            max-width: 100%;
            max-height: 400px;
            border-radius: 8px;
            cursor: pointer;
            margin: 15px 0;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .feedback-section {
            margin-top: 30px;
        }
        .section-title {
            color: #333;
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .feedback-container {
            background: #f8f9fa;
            border-left: 4px solid #007bff;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
        }
        .feedback-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-weight: bold;
            color: #555;
        }
        .admin-name {
            color: #007bff;
        }
        .feedback-time {
            color: #6c757d;
            font-size: 0.9em;
        }
        .feedback-content {
            margin-bottom: 10px;
            line-height: 1.5;
        }
        .feedback-image {
            max-width: 100%;
            max-height: 300px;
            border-radius: 6px;
            cursor: pointer;
            margin-top: 10px;
        }
        .no-feedback {
            text-align: center;
            margin-top: 40px;
            color: #777;
            font-style: italic;
        }
        .BackBtn {
            background: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            margin-bottom: 20px;
            font-size: 0.9em;
            transition: background-color 0.2s;
        }
        .BackBtn:hover {
            background: #0056b3;
        }
        .not-found {
            text-align: center;
            margin-top: 40px;
            color: #dc3545;
        }
    </style>
</head>
<body>

    <button class="BackBtn" onclick="window.location.href='<?= $isPremium ? 'premium-dashboard.html' : 'userdashboard.html' ?>'">← Back to Dashboard</button>

    <?php if (!$reportDetails): ?>
        <div class="not-found">
            <h3>Report Not Found</h3>
            <p>The requested report could not be found or you don't have permission to view it.</p>
        </div>
    <?php else: ?>
        <!-- Report Details Section -->
        <div class="container">
            <div class="report-header">
                <h2 class="report-title">Report #<?= htmlspecialchars($reportID) ?></h2>
                <div class="report-meta">
                    <div class="meta-item">
                        <span class="meta-label">Sender:</span>
                        <?= htmlspecialchars($reportDetails['SenderName']) ?>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">Email:</span>
                        <?= htmlspecialchars($reportDetails['SenderEmail']) ?>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">Location:</span>
                        <?= htmlspecialchars($reportDetails['Location']) ?>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">Status:</span>
                        <span class="status <?= strtolower($reportDetails['Status']) ?>">
                            <?= htmlspecialchars($reportDetails['Status']) ?>
                        </span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">Submitted:</span>
                        <?= date('M j, Y g:i A', strtotime($reportDetails['CreatedAt'])) ?>
                    </div>
                </div>
            </div>

            <h4>Report Description:</h4>
            <div class="report-description">
                <?= nl2br(htmlspecialchars($reportDetails['Description'])) ?>
            </div>

            <?php if (!empty($reportDetails['Image'])): ?>
                <h4>Report Image:</h4>
                <img src="<?= htmlspecialchars($reportDetails['Image']) ?>" 
                     class="report-image" 
                     onclick="openModal(this.src)"
                     alt="Report Image">
            <?php endif; ?>
        </div>

        <!-- Admin Feedback Section -->
        <div class="container feedback-section">
            <h3 class="section-title">Admin Feedback</h3>
            
            <?php if (empty($messages)): ?>
                <div class="no-feedback">
                    <p>No admin feedback has been provided for this report yet.</p>
                </div>
            <?php else: ?>
                <?php foreach ($messages as $message): ?>
                    <div class="feedback-container">
                        <div class="feedback-header">
                            <span class="admin-name"><?= htmlspecialchars($message['AdminName']) ?></span>
                            <span class="feedback-time"><?= date('M j, Y g:i A', strtotime($message['CreatedAt'])) ?></span>
                        </div>
                        <div class="feedback-content">
                            <?= nl2br(htmlspecialchars($message['Feedback'])) ?>
                        </div>
                        <?php if (!empty($message['FeedbackImage'])): ?>
                            <img src="<?= htmlspecialchars($message['FeedbackImage']) ?>" 
                                 class="feedback-image" 
                                 onclick="openModal(this.src)"
                                 alt="Feedback Image">
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- Image Modal -->
    <div id="imageModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.8); text-align:center; z-index:1000; padding-top:50px;">
        <div style="position:relative; display:inline-block;">
            <img id="modalImage" style="max-width:90%; max-height:80%; border-radius:8px;">
            <button onclick="closeModal()" style="position:absolute; top:10px; right:10px; background:rgba(0,0,0,0.7); color:white; border:none; padding:8px 12px; border-radius:4px; cursor:pointer;">✕</button>
        </div>
        <br><br>
        <button onclick="closeModal()" style="background:#fff; padding:10px 20px; border:none; border-radius:6px; cursor:pointer; margin-top:20px;">Close</button>
    </div>

    <script>
        function openModal(src) {
            document.getElementById("modalImage").src = src;
            document.getElementById("imageModal").style.display = "block";
            document.body.style.overflow = "hidden"; // Prevent background scrolling
        }

        function closeModal() {
            document.getElementById("imageModal").style.display = "none";
            document.body.style.overflow = "auto"; // Restore scrolling
        }

        // Close modal when clicking outside the image
        document.getElementById("imageModal").addEventListener("click", function(e) {
            if (e.target === this) {
                closeModal();
            }
        });

        // Close modal with Escape key
        document.addEventListener("keydown", function(e) {
            if (e.key === "Escape") {
                closeModal();
            }
        });
    </script>
</body>
</html>