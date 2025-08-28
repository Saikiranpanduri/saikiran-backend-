<?php
// Simple test for forgot password functionality
header('Content-Type: text/html; charset=utf-8');

echo "<h1>Forgot Password Test</h1>";

// Test 1: Check if required files exist
echo "<h2>Test 1: File Existence Check</h2>";
$required_files = [
    'config/database.php' => 'Database configuration',
    'config/email_config.php' => 'Email configuration',
    'services/EmailService.php' => 'Email service',
    'forgot_password.php' => 'Forgot password endpoint'
];

foreach ($required_files as $file => $description) {
    if (file_exists(__DIR__ . '/' . $file)) {
        echo "✅ $description: $file exists<br>";
    } else {
        echo "❌ $description: $file missing<br>";
    }
}

// Test 2: Test database connection
echo "<h2>Test 2: Database Connection</h2>";
try {
    require_once __DIR__ . '/config/database.php';
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db) {
        echo "✅ Database connection successful<br>";
        
        // Check if users table exists and has data
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM users");
        $stmt->execute();
        $user_count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        echo "✅ Users table has $user_count users<br>";
        
        // Show available test emails
        $stmt = $db->prepare("SELECT email, name FROM users LIMIT 5");
        $stmt->execute();
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($users) > 0) {
            echo "<h3>Available Test Emails:</h3><ul>";
            foreach ($users as $user) {
                echo "<li><strong>" . $user['email'] . "</strong> (Name: " . $user['name'] . ")</li>";
            }
            echo "</ul>";
        }
        
    } else {
        echo "❌ Database connection failed<br>";
    }
} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "<br>";
}

// Test 3: Test forgot password endpoint
echo "<h2>Test 3: Forgot Password Endpoint Test</h2>";
echo "<form method='post'>";
echo "<input type='email' name='test_email' placeholder='Enter email to test' required>";
echo "<input type='submit' name='test_forgot' value='Test Forgot Password'>";
echo "</form>";

if (isset($_POST['test_forgot']) && isset($_POST['test_email'])) {
    $test_email = $_POST['test_email'];
    echo "<h3>Testing with email: $test_email</h3>";
    
    // Simulate the API call
    $input = ['email' => $test_email];
    $json_input = json_encode($input);
    
    echo "<p>API Input: $json_input</p>";
    
    // Check if user exists
    try {
        $stmt = $db->prepare("SELECT id, name, email, status FROM users WHERE email = ? AND status = 'active'");
        $stmt->execute([$test_email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            echo "✅ User found: " . $user['name'] . " (ID: " . $user['id'] . ")<br>";
            
            // Generate test OTP
            $otp = sprintf('%06d', mt_rand(0, 999999));
            echo "✅ Test OTP generated: $otp<br>";
            
            echo "<p><strong>Success!</strong> The forgot password functionality should work with this email.</p>";
            echo "<p>You can now test the full flow:</p>";
            echo "<ol>";
            echo "<li>Use this email in the Android app</li>";
            echo "<li>Check the console for the OTP</li>";
            echo "<li>Enter the OTP in the verification screen</li>";
            echo "<li>Set a new password</li>";
            echo "</ol>";
            
        } else {
            echo "❌ User not found with email: $test_email<br>";
            echo "<p>Please use an email that exists in the users table.</p>";
        }
    } catch (Exception $e) {
        echo "❌ Error checking user: " . $e->getMessage() . "<br>";
    }
}

echo "<hr>";
echo "<p><strong>Note:</strong> This is a test file. Remove it in production.</p>";
echo "<p><strong>Next:</strong> Test the actual forgot_password.php endpoint with your Android app.</p>";
?>
