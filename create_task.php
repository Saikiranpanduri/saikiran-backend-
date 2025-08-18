<?php
include 'dbconn.php';

// Check if form data is POSTed
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Get POST values (no underscores in variable names)
    $title = $_POST["title"] ?? "";
    $startdate = $_POST["startdate"] ?? "";
    $enddate = $_POST["enddate"] ?? "";
    $starttime = $_POST["starttime"] ?? "";
    $endtime = $_POST["endtime"] ?? "";
    $description = $_POST["description"] ?? "";

    // Validate required fields (title)
    if (!empty($title)) {
        // Prepare SQL query to insert task into the table
        $stmt = $conn->prepare("INSERT INTO create_tasks (title, startdate, enddate, starttime, endtime, description) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $title, $startdate, $enddate, $starttime, $endtime, $description);

        // Execute the query and check for success
        if ($stmt->execute()) {
            echo "Task created successfully.";
        } else {
            echo "Error: " . $stmt->error;
        }

        $stmt->close();
    } else {
        echo "Title is required.";
    }
}

$conn->close();
?>
