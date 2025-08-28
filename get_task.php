<?php
include 'dbconn.php';
header('Content-Type: application/json');

$tasks = [];

// Only allow GET requests
if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid request method. Use GET."
    ]);
    exit;
}

// Read from query params
$s_no = $_GET['s-no'] ?? null;       // your actual column
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
    "status" => "success",
    "tasks" => $tasks
]);

$stmt->close();
$conn->close();
?>
