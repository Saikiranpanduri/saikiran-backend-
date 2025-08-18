<?php
include 'dbconn.php';
header('Content-Type: application/json');

$userid = $_GET["userid"] ?? "";

if (!empty($userid)) {
    $stmt = $conn->prepare("SELECT `s-no`, task_name, status FROM todo WHERE userid = ?");
    $stmt->bind_param("i", $userid);
    $stmt->execute();

    $result = $stmt->get_result();
    $tasks = [];

    while ($row = $result->fetch_assoc()) {
        $tasks[] = $row;
    }

    echo json_encode(["status" => "success", "tasks" => $tasks]);
    $stmt->close();
} else {
    echo json_encode(["status" => "error", "message" => "User ID is required."]);
}
$conn->close();
?>
