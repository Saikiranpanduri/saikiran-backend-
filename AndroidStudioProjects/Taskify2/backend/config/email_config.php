<?php
// Email configuration for password reset system
class EmailConfig {
    // SMTP Configuration
    const SMTP_HOST = 'smtp.gmail.com';
    const SMTP_PORT = 587;
    const SMTP_USERNAME = 'your-email@gmail.com'; // ⚠️ REPLACE WITH YOUR GMAIL
    const SMTP_PASSWORD = 'your-app-password'; // ⚠️ REPLACE WITH YOUR GMAIL APP PASSWORD
    const SMTP_FROM_EMAIL = 'your-email@gmail.com'; // ⚠️ REPLACE WITH YOUR GMAIL
    const SMTP_FROM_NAME = 'Taskify App';

    // Email Templates
    public static function getOtpEmailTemplate($name, $otp, $expiry_minutes) {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='utf-8'>
            <title>Password Reset OTP</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #09919B; color: white; padding: 20px; text-align: center; }
                .content { background: #f9f9f9; padding: 30px; }
                .otp-box { background: #09919B; color: white; padding: 20px; text-align: center; font-size: 24px; font-weight: bold; margin: 20px 0; }
                .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
                .warning { background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; margin: 20px 0; border-radius: 5px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Taskify App</h1>
                    <h2>Password Reset Request</h2>
                </div>
                <div class='content'>
                    <p>Hello <strong>$name</strong>,</p>
                    <p>We received a request to reset your password. Use the following verification code:</p>
                    
                    <div class='otp-box'>
                        $otp
                    </div>
                    
                    <p><strong>Important:</strong></p>
                    <ul>
                        <li>This code will expire in <strong>$expiry_minutes minutes</strong></li>
                        <li>If you didn't request this, please ignore this email</li>
                        <li>Never share this code with anyone</li>
                    </ul>
                    
                    <div class='warning'>
                        <strong>Security Note:</strong> This code is valid for a limited time only. 
                        If you need a new code, you can request one from the app.
                    </div>
                </div>
                <div class='footer'>
                    <p>This is an automated message from Taskify App. Please do not reply to this email.</p>
                    <p>&copy; " . date('Y') . " Taskify App. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }

    public static function getPasswordResetSuccessTemplate($name) {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='utf-8'>
            <title>Password Reset Successful</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #28a745; color: white; padding: 20px; text-align: center; }
                .content { background: #f9f9f9; padding: 30px; }
                .success-box { background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; margin: 20px 0; border-radius: 5px; color: #155724; }
                .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Taskify App</h1>
                    <h2>Password Reset Successful</h2>
                </div>
                <div class='content'>
                    <p>Hello <strong>$name</strong>,</p>
                    
                    <div class='success-box'>
                        <strong>Your password has been successfully reset!</strong>
                    </div>
                    
                    <p>You can now log in to your account using your new password.</p>
                    
                    <p><strong>Security Tips:</strong></p>
                    <ul>
                        <li>Use a strong, unique password</li>
                        <li>Never share your password with anyone</li>
                        <li>Enable two-factor authentication if available</li>
                        <li>Regularly update your password</li>
                    </ul>
                </div>
                <div class='footer'>
                    <p>This is an automated message from Taskify App. Please do not reply to this email.</p>
                    <p>&copy; " . date('Y') . " Taskify App. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }
}
?>
