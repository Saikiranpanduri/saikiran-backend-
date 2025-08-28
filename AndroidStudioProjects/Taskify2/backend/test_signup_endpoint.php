<?php
// Test the signup endpoint directly
header('Content-Type: text/plain');

echo "Testing Signup Endpoint\n";
echo "======================\n\n";

// Test data
$testData = [
    'name' => 'Test User',
    'email' => 'testuser@example.com',
    'password' => 'TestPass123'
];

echo "Test Data:\n";
echo "Name: " . $testData['name'] . "\n";
echo "Email: " . $testData['email'] . "\n";
echo "Password: " . $testData['password'] . "\n\n";

// Test the signup endpoint
echo "Testing signup endpoint...\n";

// Create POST data
$postData = http_build_query($testData);

// Set up cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost:8000/signup.php');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);

// Execute request
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

// Display results
echo "HTTP Status Code: $httpCode\n";
if ($error) {
    echo "cURL Error: $error\n";
}

// Parse response
$headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
$body = substr($response, $headerSize);

echo "Response Body:\n$body\n\n";

// Check if users.json was created
if (file_exists('users.json')) {
    echo "users.json file status:\n";
    echo "- Exists: YES\n";
    echo "- Size: " . filesize('users.json') . " bytes\n";
    echo "- Readable: " . (is_readable('users.json') ? "YES" : "NO") . "\n";
    
    $content = file_get_contents('users.json');
    if ($content !== false) {
        $users = json_decode($content, true);
        if ($users) {
            echo "- Users stored: " . count($users) . "\n";
            foreach ($users as $index => $user) {
                echo "  User " . ($index + 1) . ": " . $user['email'] . " (" . $user['name'] . ")\n";
            }
        } else {
            echo "- JSON decode error: " . json_last_error_msg() . "\n";
        }
    } else {
        echo "- Cannot read file content\n";
    }
} else {
    echo "users.json file was NOT created!\n";
}

echo "\nTest completed!\n";
?>
