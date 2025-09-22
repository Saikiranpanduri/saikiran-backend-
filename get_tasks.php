<?php
require_once 'dbconn.php';
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// Only allow GET requests
if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid request method. Use GET."
    ]);
    exit;
}

// Check database connection
if (!isset($conn) || $conn->connect_errno) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Database connection failed."
    ]);
    exit;
}

$tasks = [];

// Read from query params
$s_no = $_GET['s-no'] ?? null;
$title = $_GET['title'] ?? null;

$sql = "SELECT * FROM create_tasks";
$conditions = [];
$params = [];
$types = "";

// Dynamic filters
if ($s_no !== null) {
    $conditions[] = "`s-no` = ?";
    $params[] = $s_no;
    $types .= "i";
}

if ($title !== null) {
    $conditions[] = "title LIKE ?";
    $params[] = "%$title%";
    $types .= "s";
}

// Append conditions if any
if (count($conditions) > 0) {
    $sql .= " WHERE " . implode(" AND ", $conditions);
}

// Order by latest first
$sql .= " ORDER BY `s-no` DESC";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Database error: failed to prepare statement."
    ]);
    exit;
}

// Bind parameters if any
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $tasks[] = $row;
}

echo json_encode([
    "success" => true,
    "message" => "Tasks retrieved successfully.",
    "data" => $tasks,
    "count" => count($tasks)
]);

$stmt->close();
$conn->close();
?>