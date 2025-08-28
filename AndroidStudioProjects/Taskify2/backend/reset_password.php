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
require_once __DIR__ . '/services/EmailService.php';

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['reset_token']) || !isset($input['new_password'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Reset token and new password are required']);
    exit();
}

$reset_token = filter_var($input['reset_token'], FILTER_SANITIZE_STRING);
$new_password = $input['new_password'];

// Enhanced password validation matching frontend requirements
if (strlen($new_password) < 8) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Password must be at least 8 characters long']);
    exit();
}

if (!preg_match('/[A-Z]/', $new_password)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Password must include at least one uppercase letter']);
    exit();
}

if (!preg_match('/[0-9]/', $new_password)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Password must include at least one number']);
    exit();
}

if (!preg_match('/[^A-Za-z0-9]/', $new_password)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Password must include at least one special character']);
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

    // Validate reset token
    $stmt = $db->prepare("
        SELECT t.id, t.user_id, t.email, t.expiry, u.name 
        FROM password_reset_tokens t 
        JOIN users u ON t.user_id = u.id 
        WHERE t.token = ? AND t.expiry > NOW() AND t.is_used = FALSE
    ");
    $stmt->execute([$reset_token]);
    $token_entry = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$token_entry) {
        // Check if token exists but is expired or used
        $stmt = $db->prepare("SELECT id, expiry, is_used FROM password_reset_tokens WHERE token = ?");
        $stmt->execute([$reset_token]);
        $existing_token = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existing_token) {
            if ($existing_token['is_used']) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'This reset token has already been used']);
                exit();
            } elseif ($existing_token['expiry'] <= date('Y-m-d H:i:s')) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Reset token has expired. Please request a new one']);
                exit();
            }
        } else {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Invalid reset token']);
            exit();
        }
    }

    // Update user password
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    $stmt = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
    $result = $stmt->execute([$hashed_password, $token_entry['user_id']]);

    if (!$result) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Failed to update password']);
        exit();
    }

    // Mark reset token as used
    $stmt = $db->prepare("UPDATE password_reset_tokens SET is_used = TRUE WHERE id = ?");
    $stmt->execute([$token_entry['id']]);

    // Log password reset
    $stmt = $db->prepare("INSERT INTO password_reset_history (user_id, email, reset_type, ip_address, user_agent) VALUES (?, ?, 'password_reset', ?, ?)");
    $stmt->execute([
        $token_entry['user_id'], 
        $token_entry['email'], 
        $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
    ]);

    // Send success email
    $emailService = new EmailService();
    $email_result = $emailService->sendPasswordResetSuccessEmail($token_entry['email'], $token_entry['name']);

    $response = [
        'status' => 'success',
        'message' => 'Password reset successfully',
        'data' => [
            'user_id' => $token_entry['user_id'],
            'email' => $token_entry['email'],
            'user_name' => $token_entry['name'],
            'email_sent' => $email_result['success']
        ]
    ];

    if (!$email_result['success']) {
        $response['data']['email_warning'] = 'Password reset successful but confirmation email failed to send';
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
