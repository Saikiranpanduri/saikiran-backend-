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
    error_log("Signup: Invalid JSON format received");
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid JSON format']);
    exit;
}

error_log("Signup: Received JSON data");

$name = trim($input['name'] ?? '');
$email = trim($input['email'] ?? '');
$password = $input['password'] ?? '';

// Log the received data for debugging
error_log("Signup attempt - Name: '$name', Email: '$email', Password length: " . strlen($password));

// Validate input
if (empty($name) || empty($email) || empty($password)) {
    error_log("Signup validation failed - Empty fields");
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Name, email and password are required']);
    exit;
}

// Validate name length
if (strlen($name) < 2 || strlen($name) > 50) {
    error_log("Signup validation failed - Invalid name length: " . strlen($name));
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Name must be between 2 and 50 characters']);
    exit;
}

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    error_log("Signup validation failed - Invalid email format: $email");
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid email format']);
    exit;
}

// Validate password length
if (strlen($password) < 6) {
    error_log("Signup validation failed - Password too short: " . strlen($password));
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Password must be at least 6 characters']);
    exit;
}

// Check if user already exists (simulate database check)
$usersFile = 'users.json';
$existingUsers = [];

if (file_exists($usersFile)) {
    $content = file_get_contents($usersFile);
    if ($content !== false) {
        $existingUsers = json_decode($content, true) ?: [];
        error_log("Signup: Loaded " . count($existingUsers) . " existing users");
    } else {
        error_log("Signup: Failed to read existing users file");
        $existingUsers = [];
    }
} else {
    error_log("Signup: No existing users file, will create new one");
}

// Check if email already exists
foreach ($existingUsers as $user) {
    if ($user['email'] === $email) {
        error_log("Signup failed - Email already exists: $email");
        http_response_code(409);
        echo json_encode(['status' => 'error', 'message' => 'Email already exists. Please use a different email or try logging in.']);
        exit;
    }
}

// Generate user ID and hash password
$userId = uniqid('user_', true);
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);
$createdAt = date('Y-m-d H:i:s');

// Create new user
$newUser = [
    'id' => $userId,
    'name' => $name,
    'email' => $email,
    'password' => $hashedPassword,
    'created_at' => $createdAt,
    'status' => 'active'
];

// Add user to the list
$existingUsers[] = $newUser;

// Prepare JSON data
$jsonData = json_encode($existingUsers, JSON_PRETTY_PRINT);
if ($jsonData === false) {
    error_log("Signup failed - JSON encoding error: " . json_last_error_msg());
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Failed to process user data']);
    exit;
}

error_log("Signup: Attempting to save " . count($existingUsers) . " users to file");

// Save to file (simulate database)
$bytesWritten = file_put_contents($usersFile, $jsonData);
if ($bytesWritten !== false) {
    error_log("Signup: Successfully wrote $bytesWritten bytes to users.json");
    
    // Verify the file was written correctly
    if (file_exists($usersFile)) {
        $fileSize = filesize($usersFile);
        error_log("Signup: File size after write: $fileSize bytes");
        
        // Verify content
        $verifyContent = file_get_contents($usersFile);
        $verifyUsers = json_decode($verifyContent, true);
        if ($verifyUsers && count($verifyUsers) === count($existingUsers)) {
            error_log("Signup: File verification successful - " . count($verifyUsers) . " users stored");
        } else {
            error_log("Signup: File verification failed - content mismatch");
        }
    }
    
    // Success response
    $response = [
        'status' => 'success',
        'message' => 'Account created successfully! You can now log in.',
        'data' => [
            'user_id' => $userId,
            'name' => $name,
            'email' => $email,
            'created_at' => $createdAt
        ]
    ];
    
    error_log("User created successfully: $email");
    http_response_code(201);
    echo json_encode($response);
} else {
    // Error saving user
    $error = error_get_last();
    error_log("Signup failed - File write error: " . ($error['message'] ?? 'Unknown error'));
    error_log("Signup failed - File path: " . realpath($usersFile));
    error_log("Signup failed - Directory writable: " . (is_writable(dirname($usersFile)) ? 'YES' : 'NO'));
    
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Failed to create account. Please try again.']);
}
?>
