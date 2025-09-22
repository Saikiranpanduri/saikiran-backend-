<?php
require 'dbconn.php';

if ($conn->connect_error) {
    echo "Connection failed: " . $conn->connect_error;
} else {
    echo "Connected successfully\n";
    
    // Show tables
    $result = $conn->query("SHOW TABLES");
    echo "Tables in database:\n";
    while($row = $result->fetch_array()) {
        echo "- " . $row[0] . "\n";
    }
    
    // Show structure of each table
    $tables = ['user', 'create_tasks', 'todo'];
    foreach($tables as $table) {
        echo "\nStructure of table '$table':\n";
        $result = $conn->query("DESCRIBE $table");
        if($result) {
            while($row = $result->fetch_assoc()) {
                echo "- " . $row['Field'] . " (" . $row['Type'] . ")\n";
            }
        } else {
            echo "Table '$table' does not exist or error: " . $conn->error . "\n";
        }
    }
}

$conn->close();
?>
