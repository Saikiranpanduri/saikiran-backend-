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
require_once __DIR__ . '/services/SimpleEmailService.php';

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['email'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Email is required']);
    exit();
}

$email = filter_var($input['email'], FILTER_SANITIZE_EMAIL);

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid email format']);
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

    // Check if user exists
    $stmt = $db->prepare("SELECT id, name, email, status FROM users WHERE email = ? AND status = 'active'");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'User with this email not found or account is inactive']);
        exit();
    }

    // Check if there's a recent OTP request (within 1 minute)
    $stmt = $db->prepare("SELECT id, created_at FROM password_reset_otps WHERE email = ? AND created_at > DATE_SUB(NOW(), INTERVAL 1 MINUTE) AND is_used = FALSE");
    $stmt->execute([$email]);
    $recent_otp = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($recent_otp) {
        http_response_code(429);
        echo json_encode(['status' => 'error', 'message' => 'Please wait at least 1 minute before requesting another OTP']);
        exit();
    }

    // Generate 6-digit OTP
    $otp = sprintf('%06d', mt_rand(0, 999999));
    $otp_expiry = date('Y-m-d H:i:s', strtotime('+10 minutes')); // OTP valid for 10 minutes

    // Store OTP in database
    $stmt = $db->prepare("INSERT INTO password_reset_otps (user_id, email, otp, expiry) VALUES (?, ?, ?, ?)");
    $stmt->execute([$user['id'], $email, $otp, $otp_expiry]);

    // Log OTP request
    $stmt = $db->prepare("INSERT INTO password_reset_history (user_id, email, reset_type, ip_address, user_agent) VALUES (?, ?, 'otp_request', ?, ?)");
    $stmt->execute([
        $user['id'], 
        $email, 
        $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
    ]);

    // Send OTP via email using SimpleEmailService
    $emailService = new SimpleEmailService();
    $email_result = $emailService->sendOtpEmail($email, $user['name'], $otp, 10);

    if ($email_result['success']) {
        $response = [
            'status' => 'success',
            'message' => 'OTP sent successfully to your email',
            'data' => [
                'user_id' => $user['id'],
                'email' => $email,
                'expiry_minutes' => 10,
                'resend_available_after' => 60 // seconds
            ]
        ];
    } else {
        // If email fails, still return success but with warning
        $response = [
            'status' => 'success',
            'message' => 'OTP generated but email delivery failed. Please check your email or try again.',
            'data' => [
                'user_id' => $user['id'],
                'email' => $email,
                'expiry_minutes' => 10,
                'resend_available_after' => 60,
                'warning' => 'Email delivery failed: ' . $email_result['message'],
                'otp' => $otp // Include OTP for testing purposes
            ]
        ];
    }

    echo json_encode($response);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Server error: ' . $e->getMessage()]);
}
?>
