<?php
header('Content-Type: application/json');
date_default_timezone_set('Asia/Kolkata');
require 'dbconn.php';

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['email']) || !isset($data['otp'])) {
    echo json_encode(["status" => false, "message" => "Email and OTP are required"]);
    exit;
}

$email = trim($data['email']);
$otp = trim($data['otp']);

// s-no needs backticks because of the dash in the name
$stmt = $conn->prepare("SELECT `s-no` FROM auth WHERE email = ? AND otp = ?");
$stmt->bind_param("ss", $email, $otp);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $row = $result->fetch_assoc();
    echo json_encode([
        "status" => true,
        "message" => "OTP verified",
        "s-no" => $row['s-no']
    ]);
} else {
    echo json_encode(["status" => false, "message" => "Invalid OTP or Email"]);
}

$stmt->close();
$conn->close();
?>
