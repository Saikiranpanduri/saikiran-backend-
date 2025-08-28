<?php
include 'dbconn.php';

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Read raw JSON input
    $data = json_decode(file_get_contents("php://input"), true);

    $email = isset($data["email"]) ? $data["email"] : "";
    $password = isset($data["password"]) ? $data["password"] : "";

    if (!empty($email) && !empty($password)) {
        $stmt = $conn->prepare("SELECT * FROM auth WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows === 1) {
            $user = $result->fetch_assoc();

            if ($user['password'] === $password) { // 🔐 plain-text check (hash recommended)
                $response = array(
                    "status" => "success",
                    "message" => "Login successful.",
                    "user" => array(
                        "name" => $user["name"],
                        "email" => $user["email"]
                    )
                );
            } else {
                $response = array("status" => "failed", "message" => "Invalid password.");
            }
        } else {
            $response = array("status" => "failed", "message" => "User not found.");
        }

        echo json_encode($response);
        $stmt->close();
    } else {
        echo json_encode(array("status" => "failed", "message" => "Email and password are required."));
    }
}

$conn->close();
?>
