<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load PHPMailer
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

header('Content-Type: application/json');

// Include DB connection
require 'dbconn.php';

// ---------------------------
// 1. Capture input (JSON or form-data)
// ---------------------------
$data = json_decode(file_get_contents("php://input"), true);
$email = $data['email'] ?? ($_POST['email'] ?? '');

// ---------------------------
// 2. Validate email
// ---------------------------
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        "status" => false,
        "message" => "Valid email address is required"
    ]);
    exit;
}

// ---------------------------
// 3. Generate OTP
// ---------------------------
$otp = rand(100000, 999999);
$timestamp = date("Y-m-d H:i:s");

// Save OTP to DB
$stmt = $conn->prepare("UPDATE auth SET otp=?, otp_created_at=? WHERE email=?");
$stmt->bind_param("sss", $otp, $timestamp, $email);
$stmt->execute();

// ---------------------------
// 4. Send Email
// ---------------------------
$mail = new PHPMailer(true);
try {
    // SMTP Config
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'pandurisaikiran07@gmail.com';   // ✅ your Gmail
    $mail->Password   = 'sxcd mtiz ohzf inbb';           // ✅ App Password
    $mail->SMTPSecure = 'tls';
    $mail->Port       = 587;

    // Email Content
    $mail->setFrom('pandurisaikiran07@gmail.com', 'Taskify');
    $mail->addAddress($email);
    $mail->isHTML(true);
    $mail->Subject = 'Taskify OTP Verification';
    $mail->Body    = "<h2>Your OTP is: $otp</h2>";

    $mail->send();

    echo json_encode([
        "status" => true,
        "message" => "OTP sent to your email",
        "otp" => $otp // ⚠️ Remove in production
    ]);
} catch (Exception $e) {
    echo json_encode([
        "status" => false,
        "message" => "Mailer Error: {$mail->ErrorInfo}"
    ]);
}
?>
