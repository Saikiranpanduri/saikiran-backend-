<?php
// Script to view registered users (for debugging purposes)
header('Content-Type: text/plain');

echo "Registered Users\n";
echo "================\n\n";

$usersFile = 'users.json';

if (!file_exists($usersFile)) {
    echo "No users file found. No users have registered yet.\n";
    exit;
}

$users = json_decode(file_get_contents($usersFile), true);

if (empty($users)) {
    echo "No users found in the users file.\n";
    exit;
}

foreach ($users as $index => $user) {
    echo "User " . ($index + 1) . ":\n";
    echo "  ID: " . ($user['id'] ?? 'N/A') . "\n";
    echo "  Name: " . ($user['name'] ?? 'N/A') . "\n";
    echo "  Email: " . ($user['email'] ?? 'N/A') . "\n";
    echo "  Status: " . ($user['status'] ?? 'N/A') . "\n";
    echo "  Created: " . ($user['created_at'] ?? 'N/A') . "\n";
    if (isset($user['last_login'])) {
        echo "  Last Login: " . $user['last_login'] . "\n";
    }
    echo "  Password Hash: " . substr($user['password'] ?? 'N/A', 0, 20) . "...\n";
    echo "\n";
}

echo "Total Users: " . count($users) . "\n";
?>
