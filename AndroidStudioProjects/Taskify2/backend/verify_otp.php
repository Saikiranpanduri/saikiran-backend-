<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit();
}

// Include required files with correct paths
require_once __DIR__ . '/config/database.php';

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['email']) || !isset($input['otp'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Email and OTP are required']);
    exit();
}

$email = filter_var($input['email'], FILTER_SANITIZE_EMAIL);
$otp = filter_var($input['otp'], FILTER_SANITIZE_STRING);

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid email format']);
    exit();
}

if (strlen($otp) !== 6 || !is_numeric($otp)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid OTP format']);
    exit();
}

try {
    // Initialize database connection
    $database = new Database();
    $db = $database->getConnection();
    
    if (!$db) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
        exit();
    }

    // Check OTP from database
    $stmt = $db->prepare("
        SELECT o.id, o.user_id, o.email, o.otp, o.expiry, o.attempts, u.name 
        FROM password_reset_otps o 
        JOIN users u ON o.user_id = u.id 
        WHERE o.email = ? AND o.otp = ? AND o.is_used = FALSE AND o.expiry > NOW()
    ");
    $stmt->execute([$email, $otp]);
    $otp_entry = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$otp_entry) {
        // Check if OTP exists but is expired or used
        $stmt = $db->prepare("
            SELECT o.id, o.expiry, o.is_used, o.attempts 
            FROM password_reset_otps o 
            WHERE o.email = ? AND o.otp = ?
        ");
        $stmt->execute([$email, $otp]);
        $existing_otp = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existing_otp) {
            if ($existing_otp['is_used']) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'This OTP has already been used']);
                exit();
            } elseif ($existing_otp['expiry'] <= date('Y-m-d H:i:s')) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'OTP has expired. Please request a new one']);
                exit();
            }
        } else {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Invalid OTP']);
            exit();
        }
    }

    // Check if too many attempts
    if ($otp_entry['attempts'] >= 3) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Too many failed attempts. Please request a new OTP']);
        exit();
    }

    // Mark OTP as used
    $stmt = $db->prepare("UPDATE password_reset_otps SET is_used = TRUE WHERE id = ?");
    $stmt->execute([$otp_entry['id']]);

    // Generate reset token (secure random token)
    $reset_token = bin2hex(random_bytes(32));
    $token_expiry = date('Y-m-d H:i:s', strtotime('+30 minutes')); // Token valid for 30 minutes

    // Store reset token in database
    $stmt = $db->prepare("INSERT INTO password_reset_tokens (user_id, email, token, expiry) VALUES (?, ?, ?, ?)");
    $stmt->execute([$otp_entry['user_id'], $email, $reset_token, $token_expiry]);

    // Log OTP verification
    $stmt = $db->prepare("INSERT INTO password_reset_history (user_id, email, reset_type, ip_address, user_agent) VALUES (?, ?, 'otp_verified', ?, ?)");
    $stmt->execute([
        $otp_entry['user_id'], 
        $email, 
        $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
    ]);

    $response = [
        'status' => 'success',
        'message' => 'OTP verified successfully',
        'data' => [
            'user_id' => $otp_entry['user_id'],
            'email' => $email,
            'reset_token' => $reset_token,
            'expiry' => $token_expiry,
            'user_name' => $otp_entry['name']
        ]
    ];

    echo json_encode($response);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Server error: ' . $e->getMessage()]);
}
?>
