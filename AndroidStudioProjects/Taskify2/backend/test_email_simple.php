<?php
// Simple email test script
header('Content-Type: text/html; charset=utf-8');

echo "<h1>Email Test Script</h1>";

// Test 1: Check if required files exist
echo "<h2>Test 1: File Existence Check</h2>";
$required_files = [
    'config/email_config.php' => 'Email configuration',
    'services/EmailService.php' => 'Email service'
];

foreach ($required_files as $file => $description) {
    if (file_exists(__DIR__ . '/' . $file)) {
        echo "✅ $description: $file exists<br>";
    } else {
        echo "❌ $description: $file missing<br>";
    }
}

// Test 2: Test basic PHP mail function
echo "<h2>Test 2: Basic PHP Mail Function</h2>";
$test_email = 'test@example.com';
$test_subject = 'Test Email from Taskify';
$test_message = 'This is a test email to verify PHP mail() function is working.';

if (function_exists('mail')) {
    echo "✅ PHP mail() function exists<br>";
    
    // Try to send a test email
    $headers = "From: Taskify App <noreply@taskify.com>\r\n";
    $headers .= "Reply-To: noreply@taskify.com\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    
    $result = mail($test_email, $test_subject, $test_message, $headers);
    
    if ($result) {
        echo "✅ Basic mail() function test successful<br>";
        echo "📧 Test email sent to: $test_email<br>";
    } else {
        echo "❌ Basic mail() function test failed<br>";
        echo "💡 This might be due to server configuration<br>";
    }
} else {
    echo "❌ PHP mail() function not available<br>";
}

// Test 3: Test EmailService class
echo "<h2>Test 3: EmailService Class Test</h2>";
try {
    require_once __DIR__ . '/services/EmailService.php';
    $emailService = new EmailService();
    echo "✅ EmailService class loaded successfully<br>";
    
    // Test OTP email template
    $otp = '123456';
    $name = 'Test User';
    $expiry = 10;
    
    $template = EmailConfig::getOtpEmailTemplate($name, $otp, $expiry);
    if (strlen($template) > 100) {
        echo "✅ Email template generation successful<br>";
        echo "📝 Template length: " . strlen($template) . " characters<br>";
    } else {
        echo "❌ Email template generation failed<br>";
    }
    
} catch (Exception $e) {
    echo "❌ EmailService error: " . $e->getMessage() . "<br>";
}

// Test 4: Configuration Check
echo "<h2>Test 4: Email Configuration Check</h2>";
try {
    require_once __DIR__ . '/config/email_config.php';
    
    echo "📧 SMTP Host: " . EmailConfig::SMTP_HOST . "<br>";
    echo "📧 SMTP Port: " . EmailConfig::SMTP_PORT . "<br>";
    echo "📧 Username: " . EmailConfig::SMTP_USERNAME . "<br>";
    echo "📧 From Email: " . EmailConfig::SMTP_FROM_EMAIL . "<br>";
    echo "📧 From Name: " . EmailConfig::SMTP_FROM_NAME . "<br>";
    
    if (EmailConfig::SMTP_USERNAME === 'your-email@gmail.com') {
        echo "⚠️ <strong>WARNING: You need to update email configuration!</strong><br>";
        echo "💡 Edit <code>config/email_config.php</code> with your actual Gmail credentials<br>";
    } else {
        echo "✅ Email configuration appears to be set<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Configuration error: " . $e->getMessage() . "<br>";
}

// Test 5: Simple Email Test Form
echo "<h2>Test 5: Send Test Email</h2>";
echo "<form method='post'>";
echo "<input type='email' name='test_email' placeholder='Enter your email to test' required style='width: 300px; padding: 8px; margin: 5px;'><br>";
echo "<input type='submit' name='send_test' value='Send Test Email' style='padding: 10px 20px; margin: 5px; background: #09919B; color: white; border: none; border-radius: 5px;'>";
echo "</form>";

if (isset($_POST['send_test']) && isset($_POST['test_email'])) {
    $user_email = $_POST['test_email'];
    echo "<h3>Testing email to: $user_email</h3>";
    
    try {
        $emailService = new EmailService();
        $result = $emailService->sendOtpEmail($user_email, 'Test User', '123456', 10);
        
        if ($result['success']) {
            echo "✅ Test email sent successfully!<br>";
            echo "📧 Check your inbox (and spam folder) for the email<br>";
        } else {
            echo "❌ Test email failed: " . $result['message'] . "<br>";
            echo "💡 This indicates an email configuration issue<br>";
        }
    } catch (Exception $e) {
        echo "❌ Error sending test email: " . $e->getMessage() . "<br>";
    }
}

echo "<hr>";
echo "<h3>🔧 Troubleshooting Steps:</h3>";
echo "<ol>";
echo "<li><strong>Update Email Configuration:</strong> Edit <code>config/email_config.php</code> with your Gmail credentials</li>";
echo "<li><strong>Generate Gmail App Password:</strong> Go to Google Account → Security → 2-Step Verification → App Passwords</li>";
echo "<li><strong>Check Server Configuration:</strong> Ensure your server allows outgoing emails</li>";
echo "<li><strong>Test Basic Mail:</strong> The basic mail() function test above should work</li>";
echo "</ol>";

echo "<h3>📧 Gmail Setup Instructions:</h3>";
echo "<ol>";
echo "<li>Go to <a href='https://myaccount.google.com/' target='_blank'>Google Account Settings</a></li>";
echo "<li>Navigate to <strong>Security</strong> → <strong>2-Step Verification</strong></li>";
echo "<li>Click <strong>App passwords</strong></li>";
echo "<li>Generate a new app password for <strong>Mail</strong></li>";
echo "<li>Use this password in your email configuration</li>";
echo "</ol>";
?>
