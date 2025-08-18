<?php
include 'dbconn.php';

header('Content-Type: application/json');

// You can add `user_id` via GET or authentication logic if needed
// Example: $user_id = $_GET['user_id'];

$completed   = 0;
$inprogress  = 0;
$upcoming    = 0;

// Completed: status = 'completed'
$sql1 = "SELECT COUNT(*) FROM tasks WHERE status = 'completed'";
$result1 = $conn->query($sql1);
if ($row = $result1->fetch_row()) $completed = $row[0];

// In-Progress: current date between start and end
$sql2 = "SELECT COUNT(*) FROM tasks WHERE CURDATE() BETWEEN start_date AND end_date AND status != 'completed'";
$result2 = $conn->query($sql2);
if ($row = $result2->fetch_row()) $inprogress = $row[0];

// Upcoming: start date in future
$sql3 = "SELECT COUNT(*) FROM tasks WHERE start_date > CURDATE() AND status != 'completed'";
$result3 = $conn->query($sql3);
if ($row = $result3->fetch_row()) $upcoming = $row[0];

$response = [
    "status" => "success",
    "data" => [
        "completed_count"  => $completed,
        "inprogress_count" => $inprogress,
        "upcoming_count"   => $upcoming
    ]
];

echo json_encode($response);
$conn->close();
?>
