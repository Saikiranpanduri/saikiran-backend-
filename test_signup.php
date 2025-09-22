<?php
require 'dbconn.php';

echo "Testing signup functionality...\n";

// Test data
$testData = [
    'name' => 'Test User',
    'email' => 'test@example.com',
    'password' => 'testpass123'
];

// Simulate the signup process
$name = $testData['name'];
$email = $testData['email'];
$password = $testData['password'];

echo "Testing with: $name, $email\n";

// Check if user already exists
$checkStmt = $conn->prepare("SELECT `s-no` FROM auth WHERE email = ?");
$checkStmt->bind_param("s", $email);
$checkStmt->execute();
$result = $checkStmt->get_result();

if ($result->num_rows > 0) {
    echo "User already exists!\n";
} else {
    echo "User does not exist, creating new user...\n";
    
    // Hash the password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert new user
    $stmt = $conn->prepare("INSERT INTO auth (name, email, password) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $name, $email, $hashedPassword);

    if ($stmt->execute()) {
        $userId = $conn->insert_id;
        echo "User created successfully with ID: $userId\n";
    } else {
        echo "Error creating user: " . $stmt->error . "\n";
    }
    $stmt->close();
}

// Show all users in database
echo "\nAll users in database:\n";
$result = $conn->query("SELECT * FROM auth ORDER BY `s-no` DESC LIMIT 5");
while($row = $result->fetch_assoc()) {
    echo "ID: " . $row['s-no'] . " - Name: " . $row['name'] . " - Email: " . $row['email'] . "\n";
}

$conn->close();
?>
