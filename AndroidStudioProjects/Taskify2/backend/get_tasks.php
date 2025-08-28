<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if it's a GET request
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Read tasks from file storage
$tasksFile = 'tasks_data.json';
$tasks = [];

if (file_exists($tasksFile)) {
    $tasks = json_decode(file_get_contents($tasksFile), true) ?: [];
}

$response = [
    'success' => true,
    'message' => 'Tasks retrieved successfully',
    'data' => $tasks
];

http_response_code(200);
echo json_encode($response);
?>
