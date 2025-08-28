<?php
// Test database connection and create tables if they don't exist
header('Content-Type: text/html; charset=utf-8');

echo "<h1>Database Connection Test</h1>";

try {
    // Test database connection
    require_once __DIR__ . '/config/database.php';
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db) {
        echo "✅ Database connection successful!<br>";
        
        // Create tables if they don't exist
        $sql_queries = [
            // Users table
            "CREATE TABLE IF NOT EXISTS users (
                id VARCHAR(50) PRIMARY KEY,
                name VARCHAR(100) NOT NULL,
                email VARCHAR(100) UNIQUE NOT NULL,
                password VARCHAR(255) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
                last_login TIMESTAMP NULL,
                INDEX idx_email (email),
                INDEX idx_status (status)
            )",
            
            // Password reset OTPs table
            "CREATE TABLE IF NOT EXISTS password_reset_otps (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id VARCHAR(50) NOT NULL,
                email VARCHAR(100) NOT NULL,
                otp VARCHAR(6) NOT NULL,
                expiry TIMESTAMP NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                is_used BOOLEAN DEFAULT FALSE,
                attempts INT DEFAULT 0,
                INDEX idx_email (email),
                INDEX idx_otp (otp),
                INDEX idx_expiry (expiry),
                INDEX idx_user_id (user_id)
            )",
            
            // Password reset tokens table
            "CREATE TABLE IF NOT EXISTS password_reset_tokens (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id VARCHAR(50) NOT NULL,
                email VARCHAR(100) NOT NULL,
                token VARCHAR(64) UNIQUE NOT NULL,
                expiry TIMESTAMP NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                is_used BOOLEAN DEFAULT FALSE,
                INDEX idx_token (token),
                INDEX idx_expiry (expiry),
                INDEX idx_user_id (user_id)
            )",
            
            // Password reset history table
            "CREATE TABLE IF NOT EXISTS password_reset_history (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id VARCHAR(50) NOT NULL,
                email VARCHAR(100) NOT NULL,
                reset_type ENUM('otp_request', 'otp_verified', 'password_reset') NOT NULL,
                ip_address VARCHAR(45),
                user_agent TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_user_id (user_id),
                INDEX idx_email (email),
                INDEX idx_created_at (created_at)
            )"
        ];
        
        foreach ($sql_queries as $sql) {
            try {
                $stmt = $db->prepare($sql);
                $stmt->execute();
                echo "✅ Table created/verified successfully<br>";
            } catch (Exception $e) {
                echo "⚠️ Table creation warning: " . $e->getMessage() . "<br>";
            }
        }
        
        // Insert sample users if table is empty
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM users");
        $stmt->execute();
        $user_count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        if ($user_count == 0) {
            echo "<h3>Inserting sample users...</h3>";
            
            $sample_users = [
                ['user_68a96f24228e99.31236225', 'P Saikiran', 'welcome12@gmail.com', '$2y$10$VwlAEFsU3Xa.lYuA/XYXUO5yUFFGkST/YiH2/vU.Ps/QN4jq7JF1i'],
                ['user_68a9700adb8600.35499645', 'saikiran', 'saikiran09@gmail.com', '$2y$10$sMjZFGD8xQ.dJ9W3Iv.d2.In1RNddBkCXMZ0WkT9g9i5NzOrSEH5y'],
                ['user_68a970c7552632.50991051', 'saikiran', 'panduri@gmail.com', '$2y$10$W1yXsL684.YPuR0cQNgz7eWUZ7LlIvyUNWEYHBRHUet5r/IfwHuny']
            ];
            
            foreach ($sample_users as $user) {
                try {
                    $stmt = $db->prepare("INSERT INTO users (id, name, email, password) VALUES (?, ?, ?, ?)");
                    $stmt->execute($user);
                    echo "✅ User inserted: " . $user[2] . "<br>";
                } catch (Exception $e) {
                    echo "⚠️ User insertion warning: " . $e->getMessage() . "<br>";
                }
            }
        } else {
            echo "✅ Users table already has $user_count users<br>";
        }
        
        // Show table structure
        echo "<h3>Database Tables:</h3>";
        $tables = ['users', 'password_reset_otps', 'password_reset_tokens', 'password_reset_history'];
        
        foreach ($tables as $table) {
            try {
                $stmt = $db->prepare("SELECT COUNT(*) as count FROM $table");
                $stmt->execute();
                $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
                echo "📊 $table: $count records<br>";
            } catch (Exception $e) {
                echo "❌ Error checking $table: " . $e->getMessage() . "<br>";
            }
        }
        
    } else {
        echo "❌ Database connection failed!<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}

echo "<hr>";
echo "<p><strong>Next Steps:</strong></p>";
echo "<ol>";
echo "<li>Update email configuration in <code>config/email_config.php</code></li>";
echo "<li>Test the forgot password endpoint</li>";
echo "<li>Verify email delivery</li>";
echo "</ol>";
?>
