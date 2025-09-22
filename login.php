<?php
include 'dbconn.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Read raw JSON input
    $data = json_decode(file_get_contents("php://input"), true);

    $email = isset($data["email"]) ? trim($data["email"]) : "";
    $password = isset($data["password"]) ? $data["password"] : "";

    if (!empty($email) && !empty($password)) {
        $stmt = $conn->prepare("SELECT `s-no`, name, email, password FROM auth WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows === 1) {
            $user = $result->fetch_assoc();

            // Verify password using password_verify for hashed passwords
            if (password_verify($password, $user['password'])) {
                $response = array(
                    "status" => "success",
                    "message" => "Login successful.",
                    "data" => array(
                        "user_id" => $user["s-no"],
                        "name" => $user["name"],
                        "email" => $user["email"]
                    )
                );
            } else {
                $response = array("status" => "failed", "message" => "Invalid email or password.");
            }
        } else {
            $response = array("status" => "failed", "message" => "Invalid email or password.");
        }

        echo json_encode($response);
        $stmt->close();
    } else {
        echo json_encode(array("status" => "failed", "message" => "Email and password are required."));
    }
}

$conn->close();
?>
