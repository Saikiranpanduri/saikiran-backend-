<?php
include 'dbconn.php';
header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $task_name = $_POST["task_name"] ?? "";
    $status = $_POST["status"] ?? "pending";
    $userid = $_POST["userid"] ?? "";

    if (!empty($task_name) && !empty($userid)) {
        $stmt = $conn->prepare("INSERT INTO todo (task_name, status, userid) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $task_name, $status, $userid);

        if ($stmt->execute()) {
            echo json_encode(["status" => "success", "message" => "Task added."]);
        } else {
            echo json_encode(["status" => "error", "message" => $stmt->error]);
        }
        $stmt->close();
    } else {
        echo json_encode(["status" => "error", "message" => "Task name and user ID required."]);
    }
}
$conn->close();
?>
