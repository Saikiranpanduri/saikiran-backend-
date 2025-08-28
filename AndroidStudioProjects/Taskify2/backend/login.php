<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit;
}

// Get JSON data only
$jsonInput = file_get_contents('php://input');
$input = json_decode($jsonInput, true);

// Check if JSON is valid
if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid JSON format']);
    exit;
}

$email = trim($input['email'] ?? '');
$password = $input['password'] ?? '';

// Log the received data for debugging
error_log("Login attempt - Email: $email, Password length: " . strlen($password));

// Validate input
if (empty($email) || empty($password)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Email and password are required']);
    exit;
}

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid email format']);
    exit;
}

// Check if user exists and verify password
$usersFile = 'users.json';
$existingUsers = [];

if (file_exists($usersFile)) {
    $existingUsers = json_decode(file_get_contents($usersFile), true) ?: [];
}

$user = null;
foreach ($existingUsers as $existingUser) {
    if ($existingUser['email'] === $email) {
        $user = $existingUser;
        break;
    }
}

if (!$user) {
    error_log("Login failed - User not found: $email");
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Invalid email or password']);
    exit;
}

// Verify password
if (!password_verify($password, $user['password'])) {
    error_log("Login failed - Invalid password for: $email");
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Invalid email or password']);
    exit;
}

// Check if user is active
if ($user['status'] !== 'active') {
    error_log("Login failed - Inactive account: $email");
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Account is deactivated. Please contact support.']);
    exit;
}

// Generate session token (in production, use JWT or proper session management)
$token = 'token_' . uniqid() . '_' . time();

// Update last login time
$user['last_login'] = date('Y-m-d H:i:s');

// Save updated user data
foreach ($existingUsers as $key => $existingUser) {
    if ($existingUser['id'] === $user['id']) {
        $existingUsers[$key] = $user;
        break;
    }
}

file_put_contents($usersFile, json_encode($existingUsers, JSON_PRETTY_PRINT));

// Success response
$response = [
    'status' => 'success',
    'message' => 'Login successful',
    'data' => [
        'user_id' => $user['id'],
        'email' => $user['email'],
        'name' => $user['name'],
        'token' => $token,
        'last_login' => $user['last_login']
    ]
];

error_log("Login successful: $email");
http_response_code(200);
echo json_encode($response);
?>
