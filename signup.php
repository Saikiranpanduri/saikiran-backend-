<?php
include 'dbconn.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Read raw JSON
    $data = json_decode(file_get_contents("php://input"), true);

    $name = isset($data["name"]) ? $data["name"] : "";
    $email = isset($data["email"]) ? $data["email"] : "";
    $password = isset($data["password"]) ? $data["password"] : "";

    if (!empty($email) && !empty($password)) {
        $stmt = $conn->prepare("INSERT INTO auth (name, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $email, $password);

        if ($stmt->execute()) {
            $response = array("status" => "success", "message" => "User registered successfully.");
        } else {
            $response = array("status" => "failed", "message" => "Error: " . $stmt->error);
        }
        $stmt->close();
    } else {
        $response = array("status" => "failed", "message" => "Email and password are required.");
    }

    echo json_encode($response);
}

$conn->close();
?>
