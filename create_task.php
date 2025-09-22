<?php
// No whitespace before <?php

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// ------------ Inline SMTP settings (edit these to your real Gmail + App Password) ------------
const SMTP_HOST = 'smtp.gmail.com';
const SMTP_PORT = 587;
const SMTP_USER = 'pandurisaikiran07@gmail.com';          // same Gmail as used in forgot password
const SMTP_PASS = 'sxcd mtiz ohzf inbb';   // 16-char Gmail App Password (no spaces)
const SMTP_FROM = 'pandurisaikiran07@gmail.com';
const SMTP_NAME = 'Taskify';

// ------------ PHPMailer loader (Composer or manual; v6+ and v5 support) ------------
function loadPHPMailer(): array {
	$loadedVersion = null;

	// Composer autoload
	if (file_exists(__DIR__ . '/vendor/autoload.php')) {
		require_once __DIR__ . '/vendor/autoload.php';
		if (class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
			$loadedVersion = 'v6+ (composer)';
			return [true, $loadedVersion, null];
		}
	}

	// Manual load (common folders: PHPMailer or phpmailer)
	$pathsV6 = [
		__DIR__ . '/PHPMailer/src/PHPMailer.php',
		__DIR__ . '/PHPMailer/src/SMTP.php',
		__DIR__ . '/PHPMailer/src/Exception.php',
	];
	$pathsV6alt = [
		__DIR__ . '/phpmailer/src/PHPMailer.php',
		__DIR__ . '/phpmailer/src/SMTP.php',
		__DIR__ . '/phpmailer/src/Exception.php',
	];

	// Try v6 manual
	if (file_exists($pathsV6[0]) && file_exists($pathsV6[1]) && file_exists($pathsV6[2])) {
		require_once $pathsV6[0];
		require_once $pathsV6[1];
		require_once $pathsV6[2];
		if (class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
			$loadedVersion = 'v6+ (manual PHPMailer/)';
			return [true, $loadedVersion, null];
		}
	}
	if (file_exists($pathsV6alt[0]) && file_exists($pathsV6alt[1]) && file_exists($pathsV6alt[2])) {
		require_once $pathsV6alt[0];
		require_once $pathsV6alt[1];
		require_once $pathsV6alt[2];
		if (class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
			$loadedVersion = 'v6+ (manual phpmailer/)';
			return [true, $loadedVersion, null];
		}
	}

	// Try legacy v5 (no namespaces): PHPMailerAutoload.php or class files in root
	if (file_exists(__DIR__ . '/PHPMailer/PHPMailerAutoload.php')) {
		require_once __DIR__ . '/PHPMailer/PHPMailerAutoload.php';
		if (class_exists('PHPMailer')) {
			$loadedVersion = 'v5 (PHPMailerAutoload)';
			return [true, $loadedVersion, null];
		}
	}
	if (file_exists(__DIR__ . '/phpmailer/PHPMailerAutoload.php')) {
		require_once __DIR__ . '/phpmailer/PHPMailerAutoload.php';
		if (class_exists('PHPMailer')) {
			$loadedVersion = 'v5 (phpmailer/PHPMailerAutoload)';
			return [true, $loadedVersion, null];
		}
	}
	// Some v5 zips have these in root folder
	if (file_exists(__DIR__ . '/PHPMailer/class.phpmailer.php')) {
		require_once __DIR__ . '/PHPMailer/class.phpmailer.php';
		require_once __DIR__ . '/PHPMailer/class.smtp.php';
		if (class_exists('PHPMailer')) {
			$loadedVersion = 'v5 (class.phpmailer.php)';
			return [true, $loadedVersion, null];
		}
	}
	if (file_exists(__DIR__ . '/phpmailer/class.phpmailer.php')) {
		require_once __DIR__ . '/phpmailer/class.phpmailer.php';
		require_once __DIR__ . '/phpmailer/class.smtp.php';
		if (class_exists('PHPMailer')) {
			$loadedVersion = 'v5 (phpmailer/class.phpmailer.php)';
			return [true, $loadedVersion, null];
		}
	}

	return [false, null, 'PHPMailer libraries not found. Place under /PHPMailer or /phpmailer, or install via Composer.'];
}

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
	http_response_code(204);
	exit;
}

$response = ['success' => false, 'message' => ''];

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	http_response_code(405);
	$response['message'] = 'Invalid request method.';
	echo json_encode($response);
	exit;
}

// Read JSON
$raw = file_get_contents('php://input');
$input = json_decode($raw, true);
if (!is_array($input)) {
	http_response_code(400);
	$response['message'] = 'Invalid JSON payload.';
	echo json_encode($response);
	exit;
}

// Extract fields
$title       = isset($input['title']) ? trim($input['title']) : '';
$startdate   = isset($input['startdate']) ? trim($input['startdate']) : '';
$enddate     = isset($input['enddate']) ? trim($input['enddate']) : '';
$starttime   = isset($input['starttime']) ? trim($input['starttime']) : '';
$endtime     = isset($input['endtime']) ? trim($input['endtime']) : '';
$description = isset($input['description']) ? trim($input['description']) : '';
$email       = isset($input['email']) ? trim($input['email']) : '';

// Minimal validation
if ($title === '') {
	http_response_code(400);
	$response['message'] = 'Title is required.';
	echo json_encode($response);
	exit;
}
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
	http_response_code(400);
	$response['message'] = 'Valid email is required.';
	echo json_encode($response);
	exit;
}

// Optional format checks
if ($startdate !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $startdate)) {
	http_response_code(400);
	$response['message'] = 'startdate must be YYYY-MM-DD.';
	echo json_encode($response);
	exit;
}
if ($enddate !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $enddate)) {
	http_response_code(400);
	$response['message'] = 'enddate must be YYYY-MM-DD.';
	echo json_encode($response);
	exit;
}
if ($starttime !== '' && !preg_match('/^\d{2}:\d{2}$/', $starttime)) {
	http_response_code(400);
	$response['message'] = 'starttime must be HH:MM.';
	echo json_encode($response);
	exit;
}
if ($endtime !== '' && !preg_match('/^\d{2}:\d{2}$/', $endtime)) {
	http_response_code(400);
	$response['message'] = 'endtime must be HH:MM.';
	echo json_encode($response);
	exit;
}

// DB connection
require_once __DIR__ . '/dbconn.php';
if (!isset($conn) || $conn->connect_errno) {
	http_response_code(500);
	$response['message'] = 'Database connection failed.';
	echo json_encode($response);
	exit;
}

// Insert
$sql = "INSERT INTO create_tasks (title, startdate, enddate, starttime, endtime, description, email)
        VALUES (?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
if (!$stmt) {
	http_response_code(500);
	$response['message'] = 'Database error: failed to prepare statement.';
	echo json_encode($response);
	exit;
}
$stmt->bind_param('sssssss', $title, $startdate, $enddate, $starttime, $endtime, $description, $email);

if ($stmt->execute()) {
	$task_id = $conn->insert_id;

	$fetch_sql = "SELECT * FROM create_tasks WHERE `s-no` = ?";
	$fetch_stmt = $conn->prepare($fetch_sql);
	if ($fetch_stmt) {
		$fetch_stmt->bind_param('i', $task_id);
		$fetch_stmt->execute();
		$result = $fetch_stmt->get_result();
		$task_data = $result->fetch_assoc();
		$fetch_stmt->close();
	} else {
		$task_data = null;
	}

	if ($task_data) {
		$response['success']  = true;
		$response['message']  = 'Task created successfully.';
		$response['task'] = [
			's-no'       => $task_data['s-no'],
			'title'      => $task_data['title'],
			'description'=> $task_data['description'],
			'startdate'  => $task_data['startdate'],
			'enddate'    => $task_data['enddate'],
			'starttime'  => $task_data['starttime'],
			'endtime'    => $task_data['endtime'],
			'completed'  => (bool)($task_data['completed'] ?? false),
			'status'     => $task_data['status'] ?? 'PENDING',
			'createdAt'  => $task_data['created_at'] ?? null,
			'updatedAt'  => $task_data['updated_at'] ?? null,
			'email'      => $task_data['email'] ?? $email
		];

		// ---------- Email sending (supports PHPMailer v6+ and v5) ----------
		[$okLoad, $ver, $loadErr] = loadPHPMailer();
		$emailNotice = $okLoad ? "PHPMailer loaded: $ver" : "PHPMailer not loaded: $loadErr";

		if ($okLoad) {
			try {
				if (class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
					// v6+ (namespaced)
					$mail = new PHPMailer\PHPMailer\PHPMailer(true);
					$mail->isSMTP();
					$mail->Host = SMTP_HOST;
					$mail->SMTPAuth = true;
					$mail->Username = SMTP_USER;
					$mail->Password = SMTP_PASS;
					$mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
					$mail->Port = SMTP_PORT;
					$mail->CharSet = 'UTF-8';

					$mail->setFrom(SMTP_FROM, SMTP_NAME);
					$mail->addReplyTo(SMTP_FROM, SMTP_NAME);
					$mail->addAddress($email);

					$mail->isHTML(true);
					$mail->Subject = 'Task Created - ' . ($task_data['title'] ?? 'Task');

					$titleEsc = htmlspecialchars($task_data['title'] ?? '');
					$descEsc  = nl2br(htmlspecialchars($task_data['description'] ?? ''));
					$sdEsc    = htmlspecialchars($task_data['startdate'] ?? '');
					$edEsc    = htmlspecialchars($task_data['enddate'] ?? '');
					$stEsc    = htmlspecialchars($task_data['starttime'] ?? '');
					$etEsc    = htmlspecialchars($task_data['endtime'] ?? '');

					$mail->Body = "
						<h2>Task Created Successfully</h2>
						<p><strong>Title:</strong> {$titleEsc}</p>
						<p><strong>Description:</strong> {$descEsc}</p>
						<p><strong>Start:</strong> {$sdEsc} {$stEsc}</p>
						<p><strong>End:</strong> {$edEsc} {$etEsc}</p>
						<p><strong>Status:</strong> PENDING</p>
					";
					$mail->AltBody = "Task Created Successfully\n"
						. "Title: {$titleEsc}\n"
						. "Description: " . strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $descEsc)) . "\n"
						. "Start: {$sdEsc} {$stEsc}\n"
						. "End: {$edEsc} {$etEsc}\n"
						. "Status: PENDING";

					$mail->send();
					$emailNotice = 'Email sent';
				} elseif (class_exists('PHPMailer')) {
					// v5 (no namespaces)
					$mail = new PHPMailer(true);
					$mail->isSMTP();
					$mail->Host = SMTP_HOST;
					$mail->SMTPAuth = true;
					$mail->Username = SMTP_USER;
					$mail->Password = SMTP_PASS;
					$mail->SMTPSecure = 'tls';
					$mail->Port = SMTP_PORT;
					$mail->CharSet = 'UTF-8';

					$mail->setFrom(SMTP_FROM, SMTP_NAME);
					$mail->addReplyTo(SMTP_FROM, SMTP_NAME);
					$mail->addAddress($email);

					$mail->isHTML(true);
					$mail->Subject = 'Task Created - ' . ($task_data['title'] ?? 'Task');

					$titleEsc = htmlspecialchars($task_data['title'] ?? '');
					$descEsc  = nl2br(htmlspecialchars($task_data['description'] ?? ''));
					$sdEsc    = htmlspecialchars($task_data['startdate'] ?? '');
					$edEsc    = htmlspecialchars($task_data['enddate'] ?? '');
					$stEsc    = htmlspecialchars($task_data['starttime'] ?? '');
					$etEsc    = htmlspecialchars($task_data['endtime'] ?? '');

					$mail->Body = "
						<h2>Task Created Successfully</h2>
						<p><strong>Title:</strong> {$titleEsc}</p>
						<p><strong>Description:</strong> {$descEsc}</p>
						<p><strong>Start:</strong> {$sdEsc} {$stEsc}</p>
						<p><strong>End:</strong> {$edEsc} {$etEsc}</p>
						<p><strong>Status:</strong> PENDING</p>
					";
					$mail->AltBody = "Task Created Successfully\n"
						. "Title: {$titleEsc}\n"
						. "Description: " . strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $descEsc)) . "\n"
						. "Start: {$sdEsc} {$stEsc}\n"
						. "End: {$edEsc} {$etEsc}\n"
						. "Status: PENDING";

					$mail->send();
					$emailNotice = 'Email sent';
				} else {
					$emailNotice = 'PHPMailer classes not found after load';
				}
			} catch (\Throwable $e) {
				$err = isset($mail) ? ($mail->ErrorInfo ?? '') : '';
				$emailNotice = 'Email failed' . ($err ? (': ' . $err) : (': ' . $e->getMessage()));
			}
		}

		$response['email'] = $emailNotice;
		http_response_code(201);
	} else {
		http_response_code(500);
		$response['message'] = 'Task created but could not retrieve details.';
	}
} else {
	http_response_code(500);
	$response['message'] = 'Database error: ' . $stmt->error;
}

$stmt->close();
$conn->close();

echo json_encode($response);
exit;