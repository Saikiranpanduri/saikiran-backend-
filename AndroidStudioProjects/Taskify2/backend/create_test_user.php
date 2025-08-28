<?php
// Script to create a test user for testing
header('Content-Type: text/plain');

echo "Creating Test User\n";
echo "==================\n\n";

// Test user data
$testUser = [
    'name' => 'Test User',
    'email' => 'test@example.com',
    'password' => 'TestPass123'
];

// Check if user already exists
$usersFile = 'users.json';
$existingUsers = [];

if (file_exists($usersFile)) {
    $existingUsers = json_decode(file_get_contents($usersFile), true) ?: [];
    
    // Check if test user already exists
    foreach ($existingUsers as $user) {
        if ($user['email'] === $testUser['email']) {
            echo "Test user already exists!\n";
            echo "Email: " . $user['email'] . "\n";
            echo "Name: " . $user['name'] . "\n";
            echo "Password: TestPass123\n";
            echo "\nYou can now test login with these credentials.\n";
            exit;
        }
    }
}

// Create test user
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
if (file_put_contents($usersFile, json_encode($existingUsers, JSON_PRETTY_PRINT))) {
    echo "Test user created successfully!\n\n";
    echo "Test Credentials:\n";
    echo "Email: " . $testUser['email'] . "\n";
    echo "Password: " . $testUser['password'] . "\n";
    echo "Name: " . $testUser['name'] . "\n\n";
    echo "You can now test both signup and login functionality.\n";
} else {
    echo "Failed to create test user. Check file permissions.\n";
}
?>
