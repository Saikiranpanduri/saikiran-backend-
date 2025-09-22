<?php
header('Content-Type: application/json');
require 'dbconn.php'; // DB connection

// Collect POST data
$username     = $_POST['username']     ?? null;
$email        = $_POST['email']        ?? null;
$bio          = $_POST['bio']          ?? null;
$phone_number = $_POST['phone_number'] ?? null;
$fullname     = $_POST['fullname']     ?? null;

// Validate required fields
if (!$username || !$email) {
    echo json_encode([
        "status" => false,
        "message" => "username and email are required"
    ]);
    exit;
}

// Handle profile photo upload
$profile_photo = null;
if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
    $upload_dir = "uploads/";
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $file_name   = basename($_FILES["profile_photo"]["name"]);
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

// Insert new row
$sql = "INSERT INTO user (profile_photo, username, email, bio, phone_number, fullname) 
        VALUES (?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssssss", $profile_photo, $username, $email, $bio, $phone_number, $fullname);

if ($stmt->execute()) {
    echo json_encode([
        "status" => true,
        "message" => "Profile created successfully",
        "s_no" => $stmt->insert_id
    ]);
} else {
    echo json_encode([
        "status" => false,
        "message" => "Insert failed: " . $stmt->error
    ]);
}

$stmt->close();
$conn->close();
?>
