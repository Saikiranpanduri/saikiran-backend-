<?php
include 'dbconn.php';
header('Content-Type: application/json');

$sno = $_POST["s-no"] ?? null;
$status = $_POST["status"] ?? null;

if (!empty($sno) && !empty($status)) {
    $stmt = $conn->prepare("UPDATE todo SET status = ? WHERE `s-no` = ?");
    $stmt->bind_param("si", $status, $sno);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Status updated successfully."]);
    } else {
        echo json_encode(["status" => "error", "message" => $stmt->error]);
    }

    $stmt->close();
} else {
    echo json_encode(["status" => "error", "message" => "Task ID and status required."]);
}
$conn->close();
?>
