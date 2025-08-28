-- Database schema for Taskify password reset system
-- Run this SQL to create the necessary tables

-- Create database if it doesn't exist
CREATE DATABASE IF NOT EXISTS taskify_db;
USE taskify_db;

-- Users table (if not exists)
CREATE TABLE IF NOT EXISTS users (
    id VARCHAR(50) PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    last_login TIMESTAMP NULL,
    INDEX idx_email (email),
    INDEX idx_status (status)
);

-- Password reset OTPs table
CREATE TABLE IF NOT EXISTS password_reset_otps (
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
    INDEX idx_user_id (user_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Password reset tokens table
CREATE TABLE IF NOT EXISTS password_reset_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL,
    token VARCHAR(64) UNIQUE NOT NULL,
    expiry TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_used BOOLEAN DEFAULT FALSE,
    INDEX idx_token (token),
    INDEX idx_expiry (expiry),
    INDEX idx_user_id (user_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Password reset history table
CREATE TABLE IF NOT EXISTS password_reset_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL,
    reset_type ENUM('otp_request', 'otp_verified', 'password_reset') NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    INDEX idx_email (email),
    INDEX idx_created_at (created_at),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Insert sample users (if needed for testing)
INSERT IGNORE INTO users (id, name, email, password, created_at, status) VALUES
('user_68a96f24228e99.31236225', 'P Saikiran', 'welcome12@gmail.com', '$2y$10$VwlAEFsU3Xa.lYuA/XYXUO5yUFFGkST/YiH2/vU.Ps/QN4jq7JF1i', '2025-08-23 09:35:00', 'active'),
('user_68a9700adb8600.35499645', 'saikiran', 'saikiran09@gmail.com', '$2y$10$sMjZFGD8xQ.dJ9W3Iv.d2.In1RNddBkCXMZ0WkT9g9i5NzOrSEH5y', '2025-08-23 09:38:50', 'active'),
('user_68a970c7552632.50991051', 'saikiran', 'panduri@gmail.com', '$2y$10$W1yXsL684.YPuR0cQNgz7eWUZ7LlIvyUNWEYHBRHUet5r/IfwHuny', '2025-08-23 09:41:59', 'active');

-- Create indexes for better performance
CREATE INDEX idx_otp_email_expiry ON password_reset_otps(email, expiry);
CREATE INDEX idx_token_expiry ON password_reset_tokens(token, expiry);
CREATE INDEX idx_history_user_type ON password_reset_history(user_id, reset_type);
