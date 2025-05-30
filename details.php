<?php
// Database connection
<<<<<<< Updated upstream
$conn = new mysqli("localhost", "root", "Makungu@0608", "WebWizards");
=======
$conn = new mysqli("localhost", "root", "", "WebWizards");
>>>>>>> Stashed changes

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
      <button class ="BackBtn" id = "BackBtn">
  <svg height="16" width="16" xmlns="http://www.w3.org/2000/svg" version="1.1" viewBox="0 0 1024 1024"><path d="M874.690416 495.52477c0 11.2973-9.168824 20.466124-20.466124 20.466124l-604.773963 0 188.083679 188.083679c7.992021 7.992021 7.992021 20.947078 0 28.939099-4.001127 3.990894-9.240455 5.996574-14.46955 5.996574-5.239328 0-10.478655-1.995447-14.479783-5.996574l-223.00912-223.00912c-3.837398-3.837398-5.996574-9.046027-5.996574-14.46955 0-5.433756 2.159176-10.632151 5.996574-14.46955l223.019353-223.029586c7.992021-7.992021 20.957311-7.992021 28.949332 0 7.992021 8.002254 7.992021 20.957311 0 28.949332l-188.073446 188.073446 604.753497 0C865.521592 475.058646 874.690416 484.217237 874.690416 495.52477z"></path></svg>
  <span>Back</span>
  </button>
      <script>
            document.getElementById('BackBtn').addEventListener('click', () => {
      window.location.href = 'dashboard.html';
    });
    </script>
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
