<?php
// Test script for task creation endpoint
echo "Testing Task Creation Endpoint\n";
echo "==============================\n\n";

// Test data
$testData = [
    'title' => 'Test Task ' . date('Y-m-d H:i:s'),
    'description' => 'This is a test task created at ' . date('Y-m-d H:i:s'),
    'startDate' => date('Y-m-d', strtotime('+1 day')),
    'endDate' => date('Y-m-d', strtotime('+1 day')),
    'startTime' => '09:00',
    'endTime' => '17:00'
];

echo "Test Data:\n";
foreach ($testData as $key => $value) {
    echo "  $key: $value\n";
}

echo "\nSending POST request to create_task.php...\n";

// Create cURL request
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost:8000/create_task.php');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($testData));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "HTTP Response Code: $httpCode\n";

if ($error) {
    echo "cURL Error: $error\n";
} else {
    echo "Response:\n";
    $decoded = json_decode($response, true);
    if ($decoded) {
        echo json_encode($decoded, JSON_PRETTY_PRINT);
    } else {
        echo "Raw response: $response\n";
    }
}

echo "\n\nTest completed.\n";
?>
