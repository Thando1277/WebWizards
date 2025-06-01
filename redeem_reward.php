<?php
session_start();
header('Content-Type: application/json');

file_put_contents('log_reward.txt', "----- Redeem attempt at " . date('c') . " -----\n", FILE_APPEND);

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$host = 'localhost';
$username = 'root';
$password = 'LockIn_78';
$database = 'WebWizards';

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

$rawInput = file_get_contents('php://input');
file_put_contents('log_reward.txt', "Raw input: " . $rawInput . PHP_EOL, FILE_APPEND);

$input = json_decode($rawInput, true);
file_put_contents('log_reward.txt', "Decoded input: " . print_r($input, true) . PHP_EOL, FILE_APPEND);

$reward = $input['reward'] ?? '';

if (!is_string($reward)) {
    echo json_encode(['success' => false, 'message' => 'Reward must be a string']);
    file_put_contents('log_reward.txt', "Error: Reward is not a string\n", FILE_APPEND);
    exit;
}

$reward = trim($reward);
file_put_contents('log_reward.txt', "Reward received: [" . $reward . "]" . PHP_EOL, FILE_APPEND);

$userId = $_SESSION['user_id'];
$requiredPoints = 150;

$validRewards = ['shoprite', 'airtime'];
if (!in_array($reward, $validRewards)) {
    echo json_encode(['success' => false, 'message' => 'Invalid reward type']);
    file_put_contents('log_reward.txt', "Error: Invalid reward type\n", FILE_APPEND);
    exit;
}

$conn->begin_transaction();

try {
    // Check available points
    $stmt = $conn->prepare("SELECT AvailablePoints FROM user_points WHERE UserID = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception('User points record not found');
    }
    
    $row = $result->fetch_assoc();
    if ($row['AvailablePoints'] < $requiredPoints) {
        throw new Exception('Not enough points');
    }

    $stmt = $conn->prepare("UPDATE user_points SET AvailablePoints = AvailablePoints - ? WHERE UserID = ?");
    $stmt->bind_param("ii", $requiredPoints, $userId);
    $stmt->execute();
    
    if ($stmt->affected_rows === 0) {
        throw new Exception('Failed to deduct points');
    }

    $rewardCode = '';
    $expiryDate = null;

    if ($reward === 'airtime') {
        $pin = '';
        for ($i = 0; $i < 16; $i++) {
            $pin .= random_int(0, 9);
        }
        $rewardCode = implode('-', str_split($pin, 4));
        $expiryDate = date('Y-m-d', strtotime('+30 days'));
    } elseif ($reward === 'shoprite') {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ23456789';
        $code = '';
        for ($i = 0; $i < 12; $i++) {
            $code .= $chars[random_int(0, strlen($chars) - 1)];
        }
        $rewardCode = implode('-', str_split($code, 4));
        $expiryDate = date('Y-m-d', strtotime('+90 days'));
    }

    $stmt = $conn->prepare("INSERT INTO reward_history (UserID, RewardType, PointsUsed, RewardCode, ExpiryDate) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("isiss", $userId, $reward, $requiredPoints, $rewardCode, $expiryDate);
    $stmt->execute();

    $conn->commit();

    file_put_contents('log_reward.txt', "Success: Reward redeemed with code [$rewardCode] expiring on [$expiryDate]\n", FILE_APPEND);

    echo json_encode([
        'success' => true,
        'message' => 'Reward redeemed successfully',
        'rewardCode' => $rewardCode,
        'expiryDate' => $expiryDate
    ]);
} catch (Exception $e) {
    $conn->rollback();
    file_put_contents('log_reward.txt', "Error: " . $e->getMessage() . "\n", FILE_APPEND);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
