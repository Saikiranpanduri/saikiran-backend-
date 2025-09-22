<?php
header('Content-Type: application/json');
require 'dbconn.php';

// Try to fetch s_no (ID of user to update)
$s_no = $_POST['s_no'] ?? null;

if ($s_no === null) {
    $input = json_decode(file_get_contents("php://input"), true);
    if (isset($input['s_no'])) {
        $s_no = $input['s_no'];
    }
}

if ($s_no === null) {
    echo json_encode([
        "status" => false,
        "message" => "Missing required parameter: s_no"
    ]);
    exit;
}

$s_no = intval($s_no);

// Collect updatable fields
$username     = $_POST['username']     ?? null;
$email        = $_POST['email']        ?? null;
$bio          = $_POST['bio']          ?? null;
$phone_number = $_POST['phone_number'] ?? null;
$fullname     = $_POST['fullname']     ?? null;

// Handle profile photo upload if provided
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

// Build dynamic SQL for only provided fields
$fields = [];
$params = [];
$types  = "";

if ($username !== null)     { $fields[] = "username=?";     $params[] = $username;     $types .= "s"; }
if ($email !== null)        { $fields[] = "email=?";        $params[] = $email;        $types .= "s"; }
if ($bio !== null)          { $fields[] = "bio=?";          $params[] = $bio;          $types .= "s"; }
if ($phone_number !== null) { $fields[] = "phone_number=?"; $params[] = $phone_number; $types .= "s"; }
if ($fullname !== null)     { $fields[] = "fullname=?";     $params[] = $fullname;     $types .= "s"; }
if ($profile_photo !== null){ $fields[] = "profile_photo=?";$params[] = $profile_photo;$types .= "s"; }

if (empty($fields)) {
    echo json_encode([
        "status" => false,
        "message" => "No fields provided to update"
    ]);
    exit;
}

// Add s_no for WHERE clause
$params[] = $s_no;
$types   .= "i";

$sql = "UPDATE user SET " . implode(", ", $fields) . " WHERE s_no=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo json_encode([
            "status" => true,
            "message" => "Profile updated successfully"
        ]);
    } else {
        echo json_encode([
            "status" => false,
            "message" => "No changes made or profile not found"
        ]);
    }
} else {
    echo json_encode([
        "status" => false,
        "message" => "Update failed: " . $stmt->error
    ]);
}

$stmt->close();
$conn->close();
?>
