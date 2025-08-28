<?php
// Debug script to test signup functionality
header('Content-Type: text/plain');

echo "Debug Signup Functionality\n";
echo "==========================\n\n";

// Test 1: Check if we can write to the directory
echo "Test 1: Directory Write Permissions\n";
$currentDir = getcwd();
echo "Current directory: $currentDir\n";
echo "Writable: " . (is_writable($currentDir) ? "YES" : "NO") . "\n\n";

// Test 2: Check if users.json exists and is readable
echo "Test 2: Users File Status\n";
$usersFile = 'users.json';
if (file_exists($usersFile)) {
    echo "users.json exists: YES\n";
    echo "Readable: " . (is_readable($usersFile) ? "YES" : "NO") . "\n";
    echo "Writable: " . (is_writable($usersFile) ? "YES" : "NO") . "\n";
    echo "File size: " . filesize($usersFile) . " bytes\n";
    
    $content = file_get_contents($usersFile);
    if ($content !== false) {
        $users = json_decode($content, true);
        echo "JSON valid: " . (json_last_error() === JSON_ERROR_NONE ? "YES" : "NO") . "\n";
        echo "Current users: " . count($users ?: []) . "\n";
    } else {
        echo "Cannot read file content\n";
    }
} else {
    echo "users.json exists: NO\n";
    echo "Will be created when first user signs up\n";
}
echo "\n";

// Test 3: Test file creation
echo "Test 3: File Creation Test\n";
$testFile = 'test_write.tmp';
if (file_put_contents($testFile, 'test')) {
    echo "Test file created: YES\n";
    echo "Test file content: " . file_get_contents($testFile) . "\n";
    unlink($testFile); // Clean up
    echo "Test file cleaned up\n";
} else {
    echo "Test file created: NO - Permission issue!\n";
}
echo "\n";

// Test 4: Simulate signup process
echo "Test 4: Simulate Signup Process\n";
$testUser = [
    'name' => 'Debug Test User',
    'email' => 'debug@test.com',
    'password' => 'TestPass123'
];

// Check if user already exists
$existingUsers = [];
if (file_exists($usersFile)) {
    $content = file_get_contents($usersFile);
    if ($content !== false) {
        $existingUsers = json_decode($content, true) ?: [];
    }
}

// Check for duplicate
$userExists = false;
foreach ($existingUsers as $user) {
    if ($user['email'] === $testUser['email']) {
        $userExists = true;
        break;
    }
}

if ($userExists) {
    echo "Test user already exists\n";
} else {
    echo "Test user does not exist, creating...\n";
    
    // Create user
    $userId = uniqid('user_', true);
    $hashedPassword = password_hash($testUser['password'], PASSWORD_DEFAULT);
    $createdAt = date('Y-m-d H:i:s');
    
    $newUser = [
        'id' => $userId,
        'name' => $testUser['name'],
        'email' => $testUser['email'],
        'password' => $hashedPassword,
        'created_at' => $createdAt,
        'status' => 'active'
    ];
    
    // Add to users array
    $existingUsers[] = $newUser;
    
    // Save to file
    $jsonData = json_encode($existingUsers, JSON_PRETTY_PRINT);
    echo "JSON data to save: " . strlen($jsonData) . " characters\n";
    
    if (file_put_contents($usersFile, $jsonData)) {
        echo "User saved successfully!\n";
        echo "File size after save: " . filesize($usersFile) . " bytes\n";
        
        // Verify the save
        $verifyContent = file_get_contents($usersFile);
        $verifyUsers = json_decode($verifyContent, true);
        echo "Verification - Users in file: " . count($verifyUsers ?: []) . "\n";
    } else {
        echo "Failed to save user!\n";
        echo "Error: " . error_get_last()['message'] ?? 'Unknown error' . "\n";
    }
}
echo "\n";

// Test 5: Check PHP error log
echo "Test 5: PHP Error Log\n";
$errorLog = ini_get('error_log');
if ($errorLog && file_exists($errorLog)) {
    echo "Error log file: $errorLog\n";
    echo "Error log readable: " . (is_readable($errorLog) ? "YES" : "NO") . "\n";
} else {
    echo "Error log: Not configured or not accessible\n";
}
echo "\n";

echo "Debug completed!\n";
echo "If signup is still not working, check the error messages above.\n";
?>
