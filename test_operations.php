<?php
$servername = "localhost";
$username = "root";
$password = "";
$db = "taskify";

$conn = new mysqli($servername, $username, $password, $db, 3306);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "=== Testing Database Operations ===\n\n";

// Test 1: Insert a task
echo "1. Testing task creation...\n";
$sql = "INSERT INTO create_tasks (title, startdate, enddate, starttime, endtime, description) VALUES (?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$title = "Test Task";
$startdate = "2024-01-15";
$enddate = "2024-01-20";
$starttime = "09:00:00";
$endtime = "17:00:00";
$description = "This is a test task";

$stmt->bind_param("ssssss", $title, $startdate, $enddate, $starttime, $endtime, $description);

if ($stmt->execute()) {
    $task_id = $conn->insert_id;
    echo "✓ Task created successfully with ID: $task_id\n";
} else {
    echo "✗ Task creation failed: " . $stmt->error . "\n";
}
$stmt->close();

// Test 2: Insert a todo
echo "\n2. Testing todo creation...\n";
$sql = "INSERT INTO todo (task_name, status, userid) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);
$task_name = "Test Todo";
$status = "pending";
$userid = 1;

$stmt->bind_param("ssi", $task_name, $status, $userid);

if ($stmt->execute()) {
    $todo_id = $conn->insert_id;
    echo "✓ Todo created successfully with ID: $todo_id\n";
} else {
    echo "✗ Todo creation failed: " . $stmt->error . "\n";
}
$stmt->close();

// Test 3: Insert a user profile
echo "\n3. Testing user profile creation...\n";
$sql = "INSERT INTO user (profile_photo, username, email, bio, phone_number, fullname) VALUES (?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$profile_photo = "test_photo.jpg";
$username = "testuser";
$email = "test@example.com";
$bio = "Test bio";
$phone_number = "1234567890";
$fullname = "Test User";

$stmt->bind_param("ssssss", $profile_photo, $username, $email, $bio, $phone_number, $fullname);

if ($stmt->execute()) {
    $user_id = $conn->insert_id;
    echo "✓ User profile created successfully with ID: $user_id\n";
} else {
    echo "✗ User profile creation failed: " . $stmt->error . "\n";
}
$stmt->close();

// Test 4: Verify data was stored
echo "\n4. Verifying stored data...\n";

// Check tasks
$result = $conn->query("SELECT * FROM create_tasks ORDER BY `s-no` DESC LIMIT 1");
if ($row = $result->fetch_assoc()) {
    echo "✓ Latest task: " . $row['title'] . " (Status: " . $row['status'] . ", Completed: " . ($row['completed'] ? 'Yes' : 'No') . ")\n";
} else {
    echo "✗ No tasks found\n";
}

// Check todos
$result = $conn->query("SELECT * FROM todo ORDER BY `s-no` DESC LIMIT 1");
if ($row = $result->fetch_assoc()) {
    echo "✓ Latest todo: " . $row['task_name'] . " (Status: " . $row['status'] . ")\n";
} else {
    echo "✗ No todos found\n";
}

// Check users
$result = $conn->query("SELECT * FROM user ORDER BY s_no DESC LIMIT 1");
if ($row = $result->fetch_assoc()) {
    echo "✓ Latest user: " . $row['username'] . " (" . $row['email'] . ")\n";
} else {
    echo "✗ No users found\n";
}

$conn->close();
echo "\n=== Test Complete ===\n";
?>
