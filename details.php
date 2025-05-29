<?php
// Database connection
$conn = new mysqli("localhost", "root", "LockIn_78", "WebWizards");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$report = null;
$reportID = null;

// Get report ID from POST or GET
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['report_id'])) {
    $reportID = $_POST['report_id'];
} elseif ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['report_id'])) {
    $reportID = $_GET['report_id'];
}

if ($reportID !== null) {
    // Select columns including CreatedAt (report time)
    $stmt = $conn->prepare("SELECT Image, Location, Description, CreatedAt FROM Reports WHERE ReportID = ?");
    $stmt->bind_param("i", $reportID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $report = $result->fetch_assoc();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Report Details</title>
    <link rel="stylesheet" href="details.css" />
    <style>
        /* Basic styling to ensure image scales nicely */
        img {
            max-width: 100%;
            height: auto;
            border: 1px solid #ccc;
            padding: 5px;
            background: #FFA333;
        }
        .container {
            max-width: 700px;
            margin: 20px auto;
            font-family: Arial, sans-serif;
        }
        .section {
            margin-bottom: 20px;
        }
        h3 {
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if ($report): ?>
            <div class="section">
                <h3>📷 Captured Image</h3>
                <?php 
                    // Use the Image path exactly as stored in the DB
                    $imagePath = htmlspecialchars($report['Image']);
                ?>
                <!-- Debug link to check image URL -->
                <p><small>Image URL: <a href="<?php echo $imagePath; ?>" target="_blank"><?php echo $imagePath; ?></a></small></p>
                <img 
                    src="<?php echo $imagePath; ?>" 
                    alt="Issue image" 
                    onerror="this.onerror=null;this.src='placeholder.png';" 
                />
            </div>

            <div class="section">
                <h3>📍 Location</h3>
                <div class="location" id="displayLocation">
                    <?php echo htmlspecialchars($report['Location']); ?>
                </div>
            </div>

            <div class="section">
                <h3>🛠️ Issue Description</h3>
                <div class="description" id="displayDescription">
                    <?php echo nl2br(htmlspecialchars($report['Description'])); ?>
                </div>
            </div>

            <div class="section">
                <h3>⏰ Reported Time</h3>
                <div class="report-time" id="displayTime">
                    <?php 
                    echo isset($report['CreatedAt']) ? date("F j, Y, g:i a", strtotime($report['CreatedAt'])) : "N/A"; 
                    ?>
                </div>
            </div>
        <?php else: ?>
            <div class="section">
                <p style="color: red;">⚠️ No report found. Please go back and select a valid report.</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
