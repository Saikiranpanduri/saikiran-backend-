<?php
// Simple email service with fallback options
require_once __DIR__ . '/../config/email_config.php';

class SimpleEmailService {
    
    public function sendOtpEmail($to_email, $to_name, $otp, $expiry_minutes) {
        $subject = "Password Reset OTP - Taskify App";
        $html_body = EmailConfig::getOtpEmailTemplate($to_name, $otp, $expiry_minutes);
        $plain_text = strip_tags($html_body);
        
        // Try multiple email methods
        $methods = [
            'basic_mail' => [$this, 'sendWithBasicMail'],
            'smtp_gmail' => [$this, 'sendWithGmailSMTP'],
            'file_log' => [$this, 'logToFile']
        ];
        
        foreach ($methods as $method_name => $method) {
            try {
                $result = $method($to_email, $subject, $html_body, $plain_text);
                if ($result['success']) {
                    return $result;
                }
            } catch (Exception $e) {
                // Continue to next method
                continue;
            }
        }
        
        // If all methods fail, log to file as last resort
        return $this->logToFile($to_email, $subject, $html_body, $plain_text);
    }
    
    private function sendWithBasicMail($to_email, $subject, $html_body, $plain_text) {
        $headers = array(
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=UTF-8',
            'From: ' . EmailConfig::SMTP_FROM_NAME . ' <' . EmailConfig::SMTP_FROM_EMAIL . '>',
            'Reply-To: ' . EmailConfig::SMTP_FROM_EMAIL,
            'X-Mailer: PHP/' . phpversion()
        );
        
        if (mail($to_email, $subject, $html_body, implode("\r\n", $headers))) {
            return ['success' => true, 'message' => 'Email sent successfully using basic mail function'];
        } else {
            return ['success' => false, 'message' => 'Basic mail function failed'];
        }
    }
    
    private function sendWithGmailSMTP($to_email, $subject, $html_body, $plain_text) {
        // Check if Gmail credentials are configured
        if (EmailConfig::SMTP_USERNAME === 'your-email@gmail.com') {
            return ['success' => false, 'message' => 'Gmail credentials not configured'];
        }
        
        // Try to use PHPMailer if available
        if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
            try {
                $mail = new PHPMailer\PHPMailer\PHPMailer(true);
                
                $mail->isSMTP();
                $mail->Host = EmailConfig::SMTP_HOST;
                $mail->SMTPAuth = true;
                $mail->Username = EmailConfig::SMTP_USERNAME;
                $mail->Password = EmailConfig::SMTP_PASSWORD;
                $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = EmailConfig::SMTP_PORT;
                
                $mail->setFrom(EmailConfig::SMTP_FROM_EMAIL, EmailConfig::SMTP_FROM_NAME);
                $mail->addAddress($to_email);
                
                $mail->isHTML(true);
                $mail->Subject = $subject;
                $mail->Body = $html_body;
                $mail->AltBody = $plain_text;
                
                $mail->send();
                return ['success' => true, 'message' => 'Email sent successfully using Gmail SMTP'];
            } catch (Exception $e) {
                return ['success' => false, 'message' => 'Gmail SMTP failed: ' . $e->getMessage()];
            }
        } else {
            return ['success' => false, 'message' => 'PHPMailer not available'];
        }
    }
    
    private function logToFile($to_email, $subject, $html_body, $plain_text) {
        $log_dir = __DIR__ . '/../logs';
        if (!is_dir($log_dir)) {
            mkdir($log_dir, 0755, true);
        }
        
        $log_file = $log_dir . '/email_log.txt';
        $timestamp = date('Y-m-d H:i:s');
        
        $log_entry = "=== EMAIL LOG ENTRY ===\n";
        $log_entry .= "Timestamp: $timestamp\n";
        $log_entry .= "To: $to_email\n";
        $log_entry .= "Subject: $subject\n";
        $log_entry .= "Content: $plain_text\n";
        $log_entry .= "========================\n\n";
        
        if (file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX)) {
            return [
                'success' => true, 
                'message' => 'Email logged to file (check logs/email_log.txt)',
                'log_file' => $log_file,
                'warning' => 'Email was not actually sent - check your email configuration'
            ];
        } else {
            return ['success' => false, 'message' => 'Failed to log email to file'];
        }
    }
    
    public function sendPasswordResetSuccessEmail($to_email, $to_name) {
        $subject = "Password Reset Successful - Taskify App";
        $html_body = EmailConfig::getPasswordResetSuccessTemplate($to_name);
        $plain_text = strip_tags($html_body);
        
        return $this->sendOtpEmail($to_email, $to_name, 'SUCCESS', 0);
    }
}
?>
