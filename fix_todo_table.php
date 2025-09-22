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

// Add missing columns to todo table
$alter_queries = [
    "ALTER TABLE todo ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP",
    "ALTER TABLE todo ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP"
];

foreach($alter_queries as $query) {
    if ($conn->query($query) === TRUE) {
        echo "Successfully executed: $query\n";
    } else {
        echo "Error executing '$query': " . $conn->error . "\n";
    }
}

// Show updated table structure
echo "\nUpdated todo table structure:\n";
$result = $conn->query("DESCRIBE todo");
if($result) {
    while($row = $result->fetch_assoc()) {
        echo $row['Field'] . " - " . $row['Type'] . " - " . $row['Null'] . " - " . $row['Key'] . " - " . $row['Default'] . "\n";
    }
}

$conn->close();
?>
