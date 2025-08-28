<?php
$servername = "localhost";
$username = "root";
$password = "";
$db = "taskify";
// Create connection
$conn = new mysqli($servername, $username, $password, $db,3306);

$response = array();
if ($conn->connect_error) {
    $response['status'] = "error";
    $response['message'] = "Connection failed: " . $conn->connect_error;
} else {
    $response['status'] = "success";
    $response['message'] = "Connected successfully";
}
$json_response = json_encode($response);
?>

