<?php
header('Content-Type: application/json');
require 'dbconn.php';

// Try to fetch s_no from POST (form-data)
$s_no = $_POST['s_no'] ?? null;

// If not found, try JSON body
if ($s_no === null) {
    $input = json_decode(file_get_contents("php://input"), true);
    if (isset($input['s_no'])) {
        $s_no = $input['s_no'];
    }
}

// If still not found, try GET param
if ($s_no === null && isset($_GET['s_no'])) {
    $s_no = $_GET['s_no'];
}

if ($s_no === null) {
    echo json_encode([
        "status" => false,
        "message" => "Missing required parameter: s_no"
    ]);
    exit;
}

$s_no = intval($s_no);

// Prepare and execute query
$sql = "SELECT * FROM user WHERE s_no = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $s_no);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $profile = $result->fetch_assoc();
    echo json_encode([
        "status" => true,
        "data" => $profile
    ]);
} else {
    echo json_encode([
        "status" => false,
        "message" => "Profile not found"
    ]);
}

$stmt->close();
$conn->close();
?>
