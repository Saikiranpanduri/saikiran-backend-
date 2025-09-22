<?php
require 'dbconn.php';

$result = $conn->query('SELECT * FROM auth WHERE `s-no` = 35');
$row = $result->fetch_assoc();

if($row) {
    echo "User found: " . $row['name'] . " (" . $row['email'] . ")\n";
} else {
    echo "User not found\n";
}

// Show latest 3 users
echo "\nLatest 3 users:\n";
$result = $conn->query('SELECT * FROM auth ORDER BY `s-no` DESC LIMIT 3');
while($row = $result->fetch_assoc()) {
    echo "ID: " . $row['s-no'] . " - " . $row['name'] . " (" . $row['email'] . ")\n";
}

$conn->close();
?>
