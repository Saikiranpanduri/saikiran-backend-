<?php
require_once __DIR__ . '/../config/email_config.php';

class EmailService {
    private $smtp_host;
    private $smtp_port;
    private $smtp_username;
    private $smtp_password;
    private $smtp_from_email;
    private $smtp_from_name;

    public function __construct() {
        $this->smtp_host = EmailConfig::SMTP_HOST;
        $this->smtp_port = EmailConfig::SMTP_PORT;
        $this->smtp_username = EmailConfig::SMTP_USERNAME;
        $this->smtp_password = EmailConfig::SMTP_PASSWORD;
        $this->smtp_from_email = EmailConfig::SMTP_FROM_EMAIL;
        $this->smtp_from_name = EmailConfig::SMTP_FROM_NAME;
    }

    public function sendOtpEmail($to_email, $to_name, $otp, $expiry_minutes) {
        $subject = "Password Reset OTP - Taskify App";
        $html_body = EmailConfig::getOtpEmailTemplate($to_name, $otp, $expiry_minutes);
        
        return $this->sendEmail($to_email, $subject, $html_body);
    }

    public function sendPasswordResetSuccessEmail($to_email, $to_name) {
        $subject = "Password Reset Successful - Taskify App";
        $html_body = EmailConfig::getPasswordResetSuccessTemplate($to_name);
        
        return $this->sendEmail($to_email, $subject, $html_body);
    }

    private function sendEmail($to_email, $subject, $html_body) {
        // Use PHPMailer if available, otherwise fallback to basic mail
        if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
            return $this->sendWithPHPMailer($to_email, $subject, $html_body);
        } else {
            return $this->sendWithBasicMail($to_email, $subject, $html_body);
        }
    }

    private function sendWithPHPMailer($to_email, $subject, $html_body) {
        try {
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);
            
            // Server settings
            $mail->isSMTP();
            $mail->Host = $this->smtp_host;
            $mail->SMTPAuth = true;
            $mail->Username = $this->smtp_username;
            $mail->Password = $this->smtp_password;
            $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = $this->smtp_port;

            // Recipients
            $mail->setFrom($this->smtp_from_email, $this->smtp_from_name);
            $mail->addAddress($to_email);

            // Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $html_body;
            $mail->AltBody = strip_tags($html_body);

            $mail->send();
            return ['success' => true, 'message' => 'Email sent successfully'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => "Email could not be sent. Mailer Error: {$mail->ErrorInfo}"];
        }
    }

    private function sendWithBasicMail($to_email, $subject, $html_body) {
        // Fallback to basic PHP mail function
        $headers = array(
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=UTF-8',
            'From: ' . $this->smtp_from_name . ' <' . $this->smtp_from_email . '>',
            'Reply-To: ' . $this->smtp_from_email,
            'X-Mailer: PHP/' . phpversion()
        );

        $plain_text = strip_tags($html_body);
        
        if (mail($to_email, $subject, $html_body, implode("\r\n", $headers))) {
            return ['success' => true, 'message' => 'Email sent successfully'];
        } else {
            return ['success' => false, 'message' => 'Failed to send email using basic mail function'];
        }
    }

    // Alternative method using cURL for external email services
    public function sendWithExternalService($to_email, $to_name, $otp, $expiry_minutes) {
        // This is a placeholder for external email services like SendGrid, Mailgun, etc.
        // You can implement this based on your preferred email service
        
        $data = array(
            'to' => $to_email,
            'to_name' => $to_name,
            'subject' => 'Password Reset OTP - Taskify App',
            'html' => EmailConfig::getOtpEmailTemplate($to_name, $otp, $expiry_minutes),
            'from' => $this->smtp_from_email,
            'from_name' => $this->smtp_from_name
        );

        // Example for SendGrid (you'll need to implement this)
        // return $this->sendWithSendGrid($data);
        
        // For now, fallback to basic method
        return $this->sendOtpEmail($to_email, $to_name, $otp, $expiry_minutes);
    }
}
?>
