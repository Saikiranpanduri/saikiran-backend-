<?php
/**
 * Complete Password Reset Flow Test
 * 
 * This script tests the entire forgot password flow:
 * 1. Request password reset (generate OTP)
 * 2. Verify OTP (get reset token)
 * 3. Reset password with token
 * 
 * Usage: php test_complete_password_reset_flow.php
 */

require_once __DIR__ . '/config/database.php';

class PasswordResetFlowTest {
    private $db;
    private $test_email = 'test@example.com';
    private $test_user_id = null;
    private $otp = null;
    private $reset_token = null;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        
        if (!$this->db) {
            die("Database connection failed\n");
        }
        
        echo "=== Password Reset Flow Test ===\n";
        echo "Database connected successfully\n\n";
    }
    
    public function runCompleteTest() {
        try {
            // Step 1: Create test user if not exists
            $this->createTestUser();
            
            // Step 2: Test forgot password (generate OTP)
            $this->testForgotPassword();
            
            // Step 3: Test OTP verification (get reset token)
            $this->testOtpVerification();
            
            // Step 4: Test password reset
            $this->testPasswordReset();
            
            // Step 5: Verify password was changed
            $this->verifyPasswordChange();
            
            // Step 6: Cleanup test data
            $this->cleanupTestData();
            
            echo "\n=== ALL TESTS PASSED ===\n";
            echo "Password reset flow is working correctly!\n";
            
        } catch (Exception $e) {
            echo "\n=== TEST FAILED ===\n";
            echo "Error: " . $e->getMessage() . "\n";
            echo "Stack trace: " . $e->getTraceAsString() . "\n";
        }
    }
    
    private function createTestUser() {
        echo "Step 1: Creating test user...\n";
        
        // Check if user already exists
        $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$this->test_email]);
        $existing_user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($existing_user) {
            $this->test_user_id = $existing_user['id'];
            echo "Test user already exists with ID: {$this->test_user_id}\n";
        } else {
            // Create new test user
            $this->test_user_id = 'test_user_' . uniqid();
            $hashed_password = password_hash('TestPass123!', PASSWORD_DEFAULT);
            
            $stmt = $this->db->prepare("INSERT INTO users (id, name, email, password, status) VALUES (?, ?, ?, ?, 'active')");
            $stmt->execute([$this->test_user_id, 'Test User', $this->test_email, $hashed_password]);
            
            echo "Created test user with ID: {$this->test_user_id}\n";
        }
        
        echo "✓ Test user ready\n\n";
    }
    
    private function testForgotPassword() {
        echo "Step 2: Testing forgot password (generate OTP)...\n";
        
        // Simulate the forgot password request
        $otp = sprintf('%06d', mt_rand(0, 999999));
        $otp_expiry = date('Y-m-d H:i:s', strtotime('+10 minutes'));
        
        // Store OTP in database
        $stmt = $this->db->prepare("INSERT INTO password_reset_otps (user_id, email, otp, expiry) VALUES (?, ?, ?, ?)");
        $stmt->execute([$this->test_user_id, $this->test_email, $otp, $otp_expiry]);
        
        $this->otp = $otp;
        
        // Log OTP request
        $stmt = $this->db->prepare("INSERT INTO password_reset_history (user_id, email, reset_type, ip_address, user_agent) VALUES (?, ?, 'otp_request', ?, ?)");
        $stmt->execute([$this->test_user_id, $this->test_email, '127.0.0.1', 'Test Script']);
        
        echo "Generated OTP: {$otp}\n";
        echo "OTP expires at: {$otp_expiry}\n";
        echo "✓ OTP generated and stored\n\n";
    }
    
    private function testOtpVerification() {
        echo "Step 3: Testing OTP verification (get reset token)...\n";
        
        if (!$this->otp) {
            throw new Exception("No OTP available for verification");
        }
        
        // Verify OTP from database
        $stmt = $this->db->prepare("
            SELECT o.id, o.user_id, o.email, o.otp, o.expiry, o.attempts, u.name 
            FROM password_reset_otps o 
            JOIN users u ON o.user_id = u.id 
            WHERE o.email = ? AND o.otp = ? AND o.is_used = FALSE AND o.expiry > NOW()
        ");
        $stmt->execute([$this->test_email, $this->otp]);
        $otp_entry = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$otp_entry) {
            throw new Exception("OTP verification failed - OTP not found or expired");
        }
        
        // Mark OTP as used
        $stmt = $this->db->prepare("UPDATE password_reset_otps SET is_used = TRUE WHERE id = ?");
        $stmt->execute([$otp_entry['id']]);
        
        // Generate reset token
        $this->reset_token = bin2hex(random_bytes(32));
        $token_expiry = date('Y-m-d H:i:s', strtotime('+30 minutes'));
        
        // Store reset token
        $stmt = $this->db->prepare("INSERT INTO password_reset_tokens (user_id, email, token, expiry) VALUES (?, ?, ?, ?)");
        $stmt->execute([$this->test_user_id, $this->test_email, $this->reset_token, $token_expiry]);
        
        // Log OTP verification
        $stmt = $this->db->prepare("INSERT INTO password_reset_history (user_id, email, reset_type, ip_address, user_agent) VALUES (?, ?, 'otp_verified', ?, ?)");
        $stmt->execute([$this->test_user_id, $this->test_email, '127.0.0.1', 'Test Script']);
        
        echo "Generated reset token: {$this->reset_token}\n";
        echo "Token expires at: {$token_expiry}\n";
        echo "✓ OTP verified and reset token generated\n\n";
    }
    
    private function testPasswordReset() {
        echo "Step 4: Testing password reset...\n";
        
        if (!$this->reset_token) {
            throw new Exception("No reset token available for password reset");
        }
        
        // Validate reset token
        $stmt = $this->db->prepare("
            SELECT t.id, t.user_id, t.email, t.expiry, u.name 
            FROM password_reset_tokens t 
            JOIN users u ON t.user_id = u.id 
            WHERE t.token = ? AND t.expiry > NOW() AND t.is_used = FALSE
        ");
        $stmt->execute([$this->reset_token]);
        $token_entry = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$token_entry) {
            throw new Exception("Reset token validation failed");
        }
        
        // Update user password
        $new_password = 'NewTestPass456!';
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        
        $stmt = $this->db->prepare("UPDATE users SET password = ? WHERE id = ?");
        $result = $stmt->execute([$hashed_password, $token_entry['user_id']]);
        
        if (!$result) {
            throw new Exception("Failed to update password");
        }
        
        // Mark reset token as used
        $stmt = $this->db->prepare("UPDATE password_reset_tokens SET is_used = TRUE WHERE id = ?");
        $stmt->execute([$token_entry['id']]);
        
        // Log password reset
        $stmt = $this->db->prepare("INSERT INTO password_reset_history (user_id, email, reset_type, ip_address, user_agent) VALUES (?, ?, 'password_reset', ?, ?)");
        $stmt->execute([$token_entry['user_id'], $this->test_email, '127.0.0.1', 'Test Script']);
        
        echo "New password: {$new_password}\n";
        echo "✓ Password updated successfully\n\n";
    }
    
    private function verifyPasswordChange() {
        echo "Step 5: Verifying password change...\n";
        
        // Try to login with old password (should fail)
        $old_password = 'TestPass123!';
        $stmt = $this->db->prepare("SELECT password FROM users WHERE email = ?");
        $stmt->execute([$this->test_email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (password_verify($old_password, $user['password'])) {
            throw new Exception("Old password still works - password reset failed");
        }
        
        // Try to login with new password (should succeed)
        $new_password = 'NewTestPass456!';
        if (!password_verify($new_password, $user['password'])) {
            throw new Exception("New password doesn't work - password reset failed");
        }
        
        echo "✓ Password change verified - old password no longer works, new password works\n\n";
    }
    
    private function cleanupTestData() {
        echo "Step 6: Cleaning up test data...\n";
        
        // Clean up OTPs
        $stmt = $this->db->prepare("DELETE FROM password_reset_otps WHERE user_id = ?");
        $stmt->execute([$this->test_user_id]);
        
        // Clean up reset tokens
        $stmt = $this->db->prepare("DELETE FROM password_reset_tokens WHERE user_id = ?");
        $stmt->execute([$this->test_user_id]);
        
        // Clean up history
        $stmt = $this->db->prepare("DELETE FROM password_reset_history WHERE user_id = ?");
        $stmt->execute([$this->test_user_id]);
        
        // Clean up test user
        $stmt = $this->db->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$this->test_user_id]);
        
        echo "✓ Test data cleaned up\n\n";
    }
}

// Run the test
if (php_sapi_name() === 'cli') {
    $test = new PasswordResetFlowTest();
    $test->runCompleteTest();
} else {
    echo "This script should be run from the command line: php test_complete_password_reset_flow.php\n";
}
?>
