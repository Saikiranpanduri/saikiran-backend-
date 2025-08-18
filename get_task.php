<?php
include 'dbconn.php';

header('Content-Type: application/json');

$tasks = [];

// Optional query params: id or title
$id = $_GET['id'] ?? null;
$title = $_GET['title'] ?? null;

$sql = "SELECT * FROM create_tasks";
$conditions = [];
$params = [];
$types = "";

// Dynamic filters
if ($id !== null) {
    $conditions[] = "id = ?";
    $params[] = $id;
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
