<?php
require 'dbconn.php';

$result = $conn->query('SELECT * FROM create_tasks WHERE `s-no` = 45');
$row = $result->fetch_assoc();

if($row) {
    echo "Task found: " . $row['title'] . " - Status: " . $row['status'] . "\n";
} else {
    echo "Task not found\n";
}

$conn->close();
?>
