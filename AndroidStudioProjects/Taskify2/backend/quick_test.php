<?php
echo "=== Taskify Backend Quick Test ===\n\n";

// Test 1: Check if test endpoint works
echo "1. Testing test endpoint...\n";
$testResponse = file_get_contents('http://localhost:8000/test_endpoint.php');
if ($testResponse) {
    echo "   ✓ Test endpoint working\n";
    $data = json_decode($testResponse, true);
    echo "   Response: " . ($data['message'] ?? 'Unknown') . "\n";
} else {
    echo "   ✗ Test endpoint failed\n";
}

echo "\n";

// Test 2: Check if create_task.php exists and is accessible
echo "2. Testing create_task.php accessibility...\n";
$createTaskResponse = file_get_contents('http://localhost:8000/create_task.php');
if ($createTaskResponse === false) {
    echo "   ✗ create_task.php not accessible\n";
} else {
    echo "   ✓ create_task.php accessible\n";
}

echo "\n";

// Test 3: Test task creation with sample data
echo "3. Testing task creation...\n";
$testData = [
    'title' => 'Test Task ' . date('Y-m-d H:i:s'),
    'description' => 'This is a test task',
    'startDate' => date('Y-m-d', strtotime('+1 day')),
    'endDate' => date('Y-m-d', strtotime('+1 day')),
    'startTime' => '09:00',
    'endTime' => '17:00'
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost:8000/create_task.php');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($testData));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    echo "   ✗ cURL Error: $error\n";
} else {
    echo "   ✓ HTTP Response Code: $httpCode\n";
    if ($response) {
        $decoded = json_decode($response, true);
        if ($decoded && isset($decoded['success'])) {
            if ($decoded['success']) {
                echo "   ✓ Task created successfully!\n";
                echo "   Task ID: " . ($decoded['data']['id'] ?? 'Unknown') . "\n";
            } else {
                echo "   ✗ Task creation failed: " . ($decoded['message'] ?? 'Unknown error') . "\n";
            }
        } else {
            echo "   ✗ Invalid response format\n";
            echo "   Raw response: $response\n";
        }
    } else {
        echo "   ✗ No response received\n";
    }
}

echo "\n=== Test Complete ===\n";
?>
