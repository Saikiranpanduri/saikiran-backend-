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
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Get POST data
$input = json_decode(file_get_contents('php://input'), true);

// If JSON parsing failed, try form data
if (!$input) {
    $input = $_POST;
}

// Log the received data for debugging
error_log("Received data: " . print_r($input, true));

$title = $input['title'] ?? '';
$description = $input['description'] ?? '';
$startDate = $input['startDate'] ?? date('Y-m-d');
$endDate = $input['endDate'] ?? date('Y-m-d');
$startTime = $input['startTime'] ?? '09:00';
$endTime = $input['endTime'] ?? '10:00';

// Validate input
if (empty($title)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Title is required']);
    exit;
}

if (empty($description)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Description is required']);
    exit;
}

// Validate dates and times
$startDateTime = $startDate . ' ' . $startTime;
$endDateTime = $endDate . ' ' . $endTime;

if (!strtotime($startDateTime)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid start date/time format']);
    exit;
}

if (!strtotime($endDateTime)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid end date/time format']);
    exit;
}

// Check if start date/time is in the future
$startTimestamp = strtotime($startDateTime);
$currentTimestamp = time();

if ($startTimestamp <= $currentTimestamp) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Start date and time must be in the future']);
    exit;
}

// Check if end date/time is after start date/time
if (strtotime($endDateTime) <= $startTimestamp) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'End date and time must be after start date and time']);
    exit;
}

// Simulate database storage (in production, you would use a real database)
$taskId = uniqid('task_');
$currentTime = date('Y-m-d H:i:s');

$task = [
    'id' => $taskId,
    'title' => $title,
    'description' => $description,
    'startDate' => $startDate,
    'endDate' => $endDate,
    'startTime' => $startTime,
    'endTime' => $endTime,
    'completed' => false,
    'status' => 'PENDING',
    'createdAt' => $currentTime,
    'updatedAt' => $currentTime
];

// Store in a simple file-based storage (for demo purposes)
$tasksFile = 'tasks_data.json';
$tasks = [];

if (file_exists($tasksFile)) {
    $tasks = json_decode(file_get_contents($tasksFile), true) ?: [];
}

$tasks[] = $task;
file_put_contents($tasksFile, json_encode($tasks, JSON_PRETTY_PRINT));

// Log successful creation
error_log("Task created successfully: " . $taskId);

$response = [
    'success' => true,
    'message' => 'Task created successfully',
    'data' => $task
];

http_response_code(201);
echo json_encode($response);
?>
