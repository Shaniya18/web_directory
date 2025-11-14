<?php
// Use manual autoloader
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class EmailService {
    private $mailer;
    
    public function __construct() {
        $this->mailer = new PHPMailer(true);
        $this->setup();
    }
    
    private function setup() {
        try {
            // Server settings - using constants from config.php
            $this->mailer->isSMTP();
            $this->mailer->Host       = SMTP_HOST;
            $this->mailer->Port       = SMTP_PORT;
            $this->mailer->SMTPAuth   = true;
            $this->mailer->Username   = SMTP_USERNAME;
            $this->mailer->Password   = SMTP_PASSWORD;
            
            // FIXED: Use string instead of constant
            $this->mailer->SMTPSecure = 'tls';  // For port 587
            
            // CRITICAL: Disable SSL verification for development
            $this->mailer->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );
            
            // Enable debug output
            $this->mailer->SMTPDebug = 2;
            $this->mailer->Debugoutput = function($str, $level) {
                error_log("SMTP Debug level $level: $str");
                // Also output to browser for immediate debugging
                if (php_sapi_name() !== 'cli') {
                    echo "SMTP: $str<br>";
                }
            };
            
            // From address
            $this->mailer->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
            
        } catch (Exception $e) {
            error_log("Email setup failed: " . $e->getMessage());
            throw new Exception("Email configuration error: " . $e->getMessage());
        }
    }
    
    public function sendPasswordReset($toEmail, $toName, $resetLink) {
        try {
            // Recipients
            $this->mailer->addAddress($toEmail, $toName);
            
            // Content
            $this->mailer->isHTML(true);
            $this->mailer->Subject = 'Password Reset - Fiji Web Directory';
            
            $htmlContent = $this->getResetEmailTemplate($toName, $resetLink);
            $textContent = $this->getResetEmailText($toName, $resetLink);
            
            $this->mailer->Body = $htmlContent;
            $this->mailer->AltBody = $textContent;
            
            // Send email
            error_log("Attempting to send email to: " . $toEmail);
            $result = $this->mailer->send();
            
            if ($result) {
                error_log("✅ Password reset email sent to: " . $toEmail);
            } else {
                error_log("❌ Failed to send email to: " . $toEmail . " - Error: " . $this->mailer->ErrorInfo);
            }
            
            // Clear addresses for next email
            $this->mailer->clearAddresses();
            
            return $result;
            
        } catch (Exception $e) {
            error_log("❌ Email failed for {$toEmail}: " . $e->getMessage() . " - SMTP Error: " . $this->mailer->ErrorInfo);
            // Clear addresses even on failure
            $this->mailer->clearAddresses();
            throw new Exception("Failed to send email: " . $e->getMessage());
        }
    }
    
    private function getResetEmailTemplate($name, $resetLink) {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; }
                .container { max-width: 600px; margin: 0 auto; background: #ffffff; }
                .header { background: #0066cc; color: white; padding: 20px; text-align: center; }
                .content { padding: 30px; background: #f9f9f9; }
                .button { background: #0066cc; color: white; padding: 12px 24px; text-decoration: none; border-radius: 4px; display: inline-block; margin: 15px 0; }
                .footer { text-align: center; padding: 20px; font-size: 12px; color: #666; background: #fff; }
                .code { background: #f4f4f4; padding: 10px; border-radius: 4px; font-family: monospace; word-break: break-all; margin: 10px 0; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>🔒 Fiji Web Directory</h1>
                    <p>Password Reset Request</p>
                </div>
                <div class='content'>
                    <h2>Hello {$name},</h2>
                    <p>You recently requested to reset your password for your Fiji Web Directory account.</p>
                    
                    <p style='text-align: center;'>
                        <a href='{$resetLink}' class='button' style='color: white; text-decoration: none;'>Reset Your Password</a>
                    </p>
                    
                    <p>Or copy and paste this URL into your browser:</p>
                    <div class='code'>{$resetLink}</div>
                    
                    <p><strong>This link will expire in 1 hour.</strong></p>
                    
                    <p>If you did not request a password reset, please ignore this email or contact support if you have concerns.</p>
                    
                    <p>Best regards,<br>The Fiji Web Directory Team</p>
                </div>
                <div class='footer'>
                    <p>&copy; " . date('Y') . " Fiji Web Directory. All rights reserved.</p>
                    <p>This is an automated message, please do not reply to this email.</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }
    
    private function getResetEmailText($name, $resetLink) {
        return "
Password Reset - Fiji Web Directory

Hello {$name},

You recently requested to reset your password for your Fiji Web Directory account.

Click here to reset your password:
{$resetLink}

This link will expire in 1 hour.

If you did not request a password reset, please ignore this email.

Best regards,
The Fiji Web Directory Team

---
© " . date('Y') . " Fiji Web Directory. All rights reserved.
This is an automated message, please do not reply to this email.
        ";
    }
}
?>