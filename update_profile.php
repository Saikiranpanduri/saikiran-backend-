<?php
header('Content-Type: application/json');
require 'dbconn.php'; // Make sure this connects to your DB

// Check if s_no is provided
if (!isset($_POST['s_no'])) {
    echo json_encode([
        "status" => false,
        "message" => "Missing required parameter: s_no"
    ]);
    exit;
}

$s_no = intval($_POST['s_no']);
$username = $_POST['username'] ?? null;
$email = $_POST['email'] ?? null;
$bio = $_POST['bio'] ?? null;
$phone_number = $_POST['phone_number'] ?? null;
$fullname = $_POST['fullname'] ?? null;

// Handle profile photo upload
$profile_photo = null;
if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
    $upload_dir = "uploads/";
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $file_name = basename($_FILES["profile_photo"]["name"]);
    $target_path = $upload_dir . uniqid() . "_" . $file_name;

    if (move_uploaded_file($_FILES["profile_photo"]["tmp_name"], $target_path)) {
        $profile_photo = $target_path;
    } else {
        echo json_encode([
            "status" => false,
            "message" => "File upload failed"
        ]);
        exit;
    }
}

// Build update query dynamically
$fields = [];
$params = [];
$types = "";

if ($profile_photo !== null) {
    $fields[] = "profile_photo = ?";
    $params[] = $profile_photo;
    $types .= "s";
}
if ($username !== null) {
    $fields[] = "username = ?";
    $params[] = $username;
    $types .= "s";
}
if ($email !== null) {
    $fields[] = "email = ?";
    $params[] = $email;
    $types .= "s";
}
if ($bio !== null) {
    $fields[] = "bio = ?";
    $params[] = $bio;
    $types .= "s";
}
if ($phone_number !== null) {
    $fields[] = "phone_number = ?";
    $params[] = $phone_number;
    $types .= "s";
}
if ($fullname !== null) {
    $fields[] = "fullname = ?";
    $params[] = $fullname;
    $types .= "s";
}

if (empty($fields)) {
    echo json_encode([
        "status" => false,
        "message" => "No fields to update"
    ]);
    exit;
}

$sql = "UPDATE user SET " . implode(", ", $fields) . " WHERE s_no = ?";
$params[] = $s_no;
$types .= "i";

// Execute the prepared statement
$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);

if ($stmt->execute()) {
    echo json_encode([
        "status" => true,
        "message" => "Profile updated successfully"
    ]);
} else {
    echo json_encode([
        "status" => false,
        "message" => "Update failed: " . $stmt->error
    ]);
}

$stmt->close();
$conn->close();
?>
