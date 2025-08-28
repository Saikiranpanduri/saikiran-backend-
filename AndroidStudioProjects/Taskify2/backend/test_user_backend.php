<?php
echo "=== Testing Your Existing Backend ===\n\n";

// Test 1: Check if your backend files are accessible
echo "1. Testing backend file accessibility...\n";

$files = [
    'create_task.php',
    'get_task.php', 
    'update_todo.php',
    'delete_task.php'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        echo "   ✓ $file exists\n";
    } else {
        echo "   ✗ $file missing\n";
    }
}

echo "\n";

// Test 2: Test create_task.php with your existing format
echo "2. Testing create_task.php...\n";
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
        echo "   Raw Response: $response\n";
        $decoded = json_decode($response, true);
        if ($decoded) {
            echo "   Decoded Response: " . print_r($decoded, true) . "\n";
        }
    } else {
        echo "   ✗ No response received\n";
    }
}

echo "\n";

// Test 3: Test get_task.php
echo "3. Testing get_task.php...\n";
$getTasksResponse = file_get_contents('http://localhost:8000/get_task.php');
if ($getTasksResponse) {
    echo "   ✓ get_task.php accessible\n";
    echo "   Response length: " . strlen($getTasksResponse) . " characters\n";
    $data = json_decode($getTasksResponse, true);
    if ($data) {
        echo "   Decoded data: " . print_r($data, true) . "\n";
    } else {
        echo "   ✗ Failed to decode JSON\n";
    }
} else {
    echo "   ✗ get_task.php failed\n";
}

echo "\n=== Test Complete ===\n";
?>
