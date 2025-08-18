<?php

include 'dbconn.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = isset($_POST["name"]) ? $_POST["name"] : "";
    $email = isset($_POST["email"]) ? $_POST["email"] : "";
    $password = isset($_POST["password"]) ? $_POST["password"] : "";

    if (!empty($email) && !empty($password)) {

        
        $stmt = $conn->prepare("INSERT INTO auth (name, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $email, $password);

        if ($stmt->execute()) {
            $response = array("status" => "success", "message" => "User registered successfully.");
        } else {
            $response = array("status" => "failed", "message" => "Error: " . $stmt->error);
        }

        echo json_encode($response);
        $stmt->close();

    } else {
        $response = array("status" => "failed", "message" => "Email and password are required.");
        echo json_encode($response);
    }
}

$conn->close();
?>