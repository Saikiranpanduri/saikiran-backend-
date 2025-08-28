<?php
// Simple test script for forgot password functionality
header('Content-Type: text/html; charset=utf-8');

echo "<h1>Forgot Password Test</h1>";

// Test 1: Check if users.json exists and has valid structure
echo "<h2>Test 1: Users Database Check</h2>";
if (file_exists('users.json')) {
    $users_data = json_decode(file_get_contents('users.json'), true);
    if ($users_data) {
        echo "✅ users.json exists and is valid JSON<br>";
        
        if (is_array($users_data)) {
            echo "✅ Structure: Array of users (direct)<br>";
            echo "Number of users: " . count($users_data) . "<br>";
            
            if (count($users_data) > 0) {
                $first_user = $users_data[0];
                echo "First user: " . $first_user['email'] . " (ID: " . $first_user['id'] . ")<br>";
            }
        } elseif (isset($users_data['users'])) {
            echo "✅ Structure: Object with 'users' key<br>";
            echo "Number of users: " . count($users_data['users']) . "<br>";
        } else {
            echo "⚠️ Structure: Unknown format<br>";
        }
    } else {
        echo "❌ users.json is not valid JSON<br>";
    }
} else {
    echo "❌ users.json not found<br>";
}

// Test 2: Test forgot password with a valid email
echo "<h2>Test 2: Test Forgot Password API</h2>";
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
    $users_data = json_decode(file_get_contents('users.json'), true);
    $user_found = false;
    $user_id = null;
    
    if (is_array($users_data)) {
        $users_array = $users_data;
    } elseif (isset($users_data['users'])) {
        $users_array = $users_data['users'];
    } else {
        $users_array = [];
    }
    
    foreach ($users_array as $user) {
        if ($user['email'] === $test_email) {
            $user_found = true;
            $user_id = $user['id'];
            break;
        }
    }
    
    if ($user_found) {
        echo "✅ User found: ID = $user_id<br>";
        
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
        echo "<p>Please use an email that exists in the users.json file.</p>";
    }
}

// Test 3: Show available test emails
echo "<h2>Test 3: Available Test Emails</h2>";
if (file_exists('users.json')) {
    $users_data = json_decode(file_get_contents('users.json'), true);
    if (is_array($users_data)) {
        $users_array = $users_data;
    } elseif (isset($users_data['users'])) {
        $users_array = $users_data['users'];
    } else {
        $users_array = [];
    }
    
    if (count($users_array) > 0) {
        echo "<p>Available test emails:</p><ul>";
        foreach ($users_array as $user) {
            echo "<li><strong>" . $user['email'] . "</strong> (Name: " . $user['name'] . ")</li>";
        }
        echo "</ul>";
        echo "<p><strong>Tip:</strong> Copy one of these emails to test the forgot password functionality.</p>";
    } else {
        echo "<p>No users found in database.</p>";
    }
}

echo "<hr>";
echo "<p><strong>Note:</strong> This is a test file. Remove it in production.</p>";
?>
