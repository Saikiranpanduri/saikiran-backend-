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

$id = $input['id'] ?? '';
$title = $input['title'] ?? '';
$description = $input['description'] ?? '';
$startDate = $input['startDate'] ?? '';
$endDate = $input['endDate'] ?? '';
$startTime = $input['startTime'] ?? '';
$endTime = $input['endTime'] ?? '';
$completed = $input['completed'] ?? false;

// Validate input
if (empty($id)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Task ID is required']);
    exit;
}

if (empty($title)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Title is required']);
    exit;
}

// Read existing tasks
$tasksFile = 'tasks_data.json';
$tasks = [];

if (file_exists($tasksFile)) {
    $tasks = json_decode(file_get_contents($tasksFile), true) ?: [];
}

// Find and update the task
$taskFound = false;
$updatedTask = null;

foreach ($tasks as &$task) {
    if ($task['id'] === $id) {
        $task['title'] = $title;
        $task['description'] = $description;
        $task['startDate'] = $startDate;
        $task['endDate'] = $endDate;
        $task['startTime'] = $startTime;
        $task['endTime'] = $endTime;
        $task['completed'] = $completed;
        $task['updatedAt'] = date('Y-m-d H:i:s');
        
        $updatedTask = $task;
        $taskFound = true;
        break;
    }
}

if (!$taskFound) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Task not found']);
    exit;
}

// Save updated tasks
file_put_contents($tasksFile, json_encode($tasks, JSON_PRETTY_PRINT));

$response = [
    'success' => true,
    'message' => 'Task updated successfully',
    'data' => $updatedTask
];

http_response_code(200);
echo json_encode($response);
?>
