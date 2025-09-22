<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Include database connection
require_once 'dbconn.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Read raw JSON
    $data = json_decode(file_get_contents("php://input"), true);

    $name = isset($data["name"]) ? trim($data["name"]) : "";
    $email = isset($data["email"]) ? trim($data["email"]) : "";
    $password = isset($data["password"]) ? $data["password"] : "";

    if (!empty($email) && !empty($password) && !empty($name)) {
        // Check if user already exists
        $checkStmt = $conn->prepare("SELECT `s-no` FROM auth WHERE email = ?");
        $checkStmt->bind_param("s", $email);
        $checkStmt->execute();
        $result = $checkStmt->get_result();
        
        if ($result->num_rows > 0) {
            $response = array("status" => "failed", "message" => "User with this email already exists.");
        } else {
            // Hash the password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = $conn->prepare("INSERT INTO auth (name, email, password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $name, $email, $hashedPassword);

            if ($stmt->execute()) {
                $userId = $conn->insert_id;
                $response = array(
                    "status" => "success", 
                    "message" => "User registered successfully.",
                    "data" => array(
                        "user_id" => $userId,
                        "name" => $name,
                        "email" => $email
                    )
                );
            } else {
                $response = array("status" => "failed", "message" => "Error: " . $stmt->error);
            }
            $stmt->close();
        }
        $checkStmt->close();
    } else {
        $response = array("status" => "failed", "message" => "Name, email and password are required.");
    }

    echo json_encode($response);
}

$conn->close();
?>
