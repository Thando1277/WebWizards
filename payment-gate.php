<?php
// Database configuration
$host = 'localhost';
$dbname = 'WebWizards';
$username = 'root';
$password = 'LockIn_78';

// Create PDO connection
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Sanitize and fetch POST data
$fullName = $_POST['full-name'] ?? '';
$email = $_POST['email'] ?? '';
$address = $_POST['address'] ?? '';
$cardNumber = $_POST['card-number'] ?? '';
$expDate = $_POST['exp-date'] ?? '';
$cvv = $_POST['cvv'] ?? '';
$amount = 99.99; // Set your fixed or dynamic amount here

// Simple mock user (replace with session or actual user login system)
$userID = 1; // Hardcoded for now

// Encrypt sensitive data (basic example – for production, use stronger methods + HTTPS + PCI compliance)
$encryptedCardNumber = base64_encode($cardNumber);
$encryptedCVV = base64_encode($cvv);

try {
    $stmt = $pdo->prepare("INSERT INTO Payments (UserID, CardNumber, ExpirationDate, CVV, Amount) 
                           VALUES (:userID, :cardNumber, :expirationDate, :cvv, :amount)");
    $stmt->execute([
        ':userID' => $userID,
        ':cardNumber' => $encryptedCardNumber,
        ':expirationDate' => $expDate . '-01',
        ':cvv' => $encryptedCVV,
        ':amount' => $amount
    ]);

    // Redirect to thank-you page
    header("Location: log-in.html");
    exit();
} catch (PDOException $e) {
    echo "Payment failed: " . $e->getMessage();
}
?>
