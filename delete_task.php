<?php
include 'dbconn.php';
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// Only allow DELETE
if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid request method. Use DELETE."
    ]);
    exit;
}

// Get raw input (for DELETE, body must be read from php://input)
$raw = file_get_contents("php://input");
$data = json_decode($raw, true);

// Check if JSON or query param fallback
$s_no = $data['s-no'] ?? ($_GET['s-no'] ?? null);

if ($s_no === null || !is_numeric($s_no)) {
    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "message" => "Missing or invalid task identifier (s-no)."
    ]);
    exit;
}

// Prepare DELETE
$sql = "DELETE FROM create_tasks WHERE `s-no` = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Database error: failed to prepare statement."
    ]);
    exit;
}

$stmt->bind_param("i", $s_no);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        http_response_code(200);
        echo json_encode([
            "status" => "success",
            "message" => "Task deleted successfully.",
            "deleted_id" => $s_no
        ]);
    } else {
        http_response_code(404);
        echo json_encode([
            "status" => "error",
            "message" => "Task not found."
        ]);
    }
} else {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Database error: " . $stmt->error
    ]);
}

$stmt->close();
$conn->close();
?>
