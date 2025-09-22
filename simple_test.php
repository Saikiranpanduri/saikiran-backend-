<?php
$servername = "localhost";
$username = "root";
$password = "";
$db = "taskify";

$conn = new mysqli($servername, $username, $password, $db, 3306);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "Connected successfully\n";

// Show tables
$result = $conn->query("SHOW TABLES");
if ($result) {
    echo "Tables:\n";
    while($row = $result->fetch_array()) {
        echo "- " . $row[0] . "\n";
    }
} else {
    echo "Error: " . $conn->error . "\n";
}

$conn->close();
?>