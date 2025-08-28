<?php
header("Content-Type: application/json");
include 'dbconn.php';

// Response array
$response = [
    "success" => false,
    "message" => "",
];

// Only allow POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Decode JSON input (instead of $_POST)
    $input = json_decode(file_get_contents("php://input"), true);

    $title = $input["title"] ?? "";
    $startdate = $input["startdate"] ?? "";
    $enddate = $input["enddate"] ?? "";
    $starttime = $input["starttime"] ?? "";
    $endtime = $input["endtime"] ?? "";
    $description = $input["description"] ?? "";

    if (!empty($title)) {
        $stmt = $conn->prepare(
            "INSERT INTO create_tasks (title, startdate, enddate, starttime, endtime, description) VALUES (?, ?, ?, ?, ?, ?)"
        );
        $stmt->bind_param("ssssss", $title, $startdate, $enddate, $starttime, $endtime, $description);

        if ($stmt->execute()) {
            $response["success"] = true;
            $response["message"] = "Task created successfully.";
        } else {
            $response["message"] = "Database error: " . $stmt->error;
        }

        $stmt->close();
    } else {
        $response["message"] = "Title is required.";
    }
} else {
    $response["message"] = "Invalid request method.";
}

$conn->close();

echo json_encode($response);
?>
