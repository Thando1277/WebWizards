<?php
header('Content-Type: application/json');

$host = 'localhost';
$dbname = 'WebWizards';
$username = 'root';
$password = 'LockIn_78';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit();
}


$fullName = trim($_POST['full-name'] ?? '');
$email = trim($_POST['email'] ?? '');
$address = trim($_POST['address'] ?? '');
$cardNumber = preg_replace('/\D/', '', $_POST['card-number'] ?? '');
$expDate = trim($_POST['exp-date'] ?? '');
$cvv = preg_replace('/\D/', '', $_POST['cvv'] ?? '');

$amount = 99.99;

$stmt = $pdo->prepare("SELECT UserID FROM Users WHERE Email = :email");
$stmt->execute([':email' => $email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    http_response_code(400);
    echo json_encode(['error' => 'User not found.']);
    exit();
}

$userID = $user['UserID'];

// Validation (as before)
$errors = [];

if (empty($fullName)) $errors[] = "Full name is required.";
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email address.";
if (strlen($cardNumber) < 13 || strlen($cardNumber) > 19) $errors[] = "Invalid card number length.";
if (!preg_match('/^\d{4}-\d{2}$/', $expDate)) $errors[] = "Expiration date must be in YYYY-MM format.";
if (!preg_match('/^\d{3,4}$/', $cvv)) $errors[] = "Invalid CVV.";

if (!empty($errors)) {
    http_response_code(400);
    echo json_encode(['error' => implode(" ", $errors)]);
    exit();
}

// Check expiration
$expDateObj = DateTime::createFromFormat('Y-m', $expDate);
$expDateObj->modify('last day of this month');
$now = new DateTime();

if ($expDateObj < $now) {
    http_response_code(400);
    echo json_encode(['error' => "Card expiration date has passed."]);
    exit();
}

// Store expiration as YYYY-MM-01
$expirationDate = $expDate . "-01";
$cardLast4 = substr($cardNumber, -4);

try {
    $pdo->beginTransaction();

    // Insert payment
    $stmt = $pdo->prepare("INSERT INTO Payments (UserID, CardNumber, ExpirationDate, CVV, Amount) 
                           VALUES (:userID, :cardNumber, :expirationDate, :cvv, :amount)");
    $stmt->execute([
        ':userID' => $userID,
        ':cardNumber' => $cardLast4,
        ':expirationDate' => $expirationDate,
        ':cvv' => $cvv,
        ':amount' => $amount
    ]);

    // Promote to PremiumUser
    $stmt = $pdo->prepare("INSERT INTO PremiumUser (UserID) VALUES (:userID)");
    $stmt->execute([':userID' => $userID]);

    $pdo->commit();

    echo json_encode(['redirect' => 'log-in.html']);
} catch (PDOException $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
