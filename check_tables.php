<?php
$servername = "localhost";
$username = "root";
$password = "";
$db = "taskify";

$conn = new mysqli($servername, $username, $password, $db, 3306);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$tables = ['user', 'create_tasks', 'todo', 'auth'];

foreach($tables as $table) {
    echo "\n=== Table: $table ===\n";
    $result = $conn->query("DESCRIBE $table");
    if($result) {
        while($row = $result->fetch_assoc()) {
            echo $row['Field'] . " - " . $row['Type'] . " - " . $row['Null'] . " - " . $row['Key'] . " - " . $row['Default'] . "\n";
        }
    } else {
        echo "Error: " . $conn->error . "\n";
    }
}

$conn->close();
?>
