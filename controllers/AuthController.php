<?php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/PasswordReset.php';

class AuthController {
    private $db;
    private $userModel;
    private $passwordResetModel;
    
    public function __construct($db) {
        $this->db = $db;
        $this->userModel = new User($db);
        $this->passwordResetModel = new PasswordReset($db);
    }
    
    public function forgotPassword() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleForgotPassword();
            return;
        }
        
        view('auth/forgot_password', [
            'pageTitle' => 'Forgot Password - Fiji Web Directory'
        ]);
    }
    
    public function resetPassword() {
        $token = $_GET['token'] ?? '';
        
        if (!$token) {
            SessionManager::setMessage('error', 'Invalid reset link');
            redirect('?page=forgot-password');
            return;
        }
        
        // Validate token
        $tokenData = $this->passwordResetModel->validateToken($token);
        if (!$tokenData) {
            SessionManager::setMessage('error', 'Invalid or expired reset link');
            redirect('?page=forgot-password');
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handlePasswordReset($token, $tokenData);
            return;
        }
        
        view('auth/reset_password', [
            'pageTitle' => 'Reset Password - Fiji Web Directory',
            'token' => $token,
            'email' => $tokenData['email']
        ]);
    }
    
    private function handleForgotPassword() {
        // CSRF validation
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            SessionManager::setMessage('error', 'Security validation failed');
            redirect('?page=forgot-password');
            return;
        }
        
        $email = trim($_POST['email'] ?? '');
        
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            SessionManager::setMessage('error', 'Please enter a valid email address');
            redirect('?page=forgot-password');
            return;
        }
        
        // Check if user exists
        $user = $this->userModel->findByEmail($email);
        if (!$user) {
            // Don't reveal if email exists (security)
            SessionManager::setMessage('success', 'If that email exists in our system, a reset link has been sent.');
            redirect('?page=forgot-password');
            return;
        }
        
        // Create reset token
        $token = $this->passwordResetModel->createToken($email);
        if ($token) {
            // Send reset email using EmailService
            $emailSent = $this->sendResetEmail($email, $token, $user['username']);
            
            if ($emailSent) {
                SessionManager::setMessage('success', 'Password reset link has been generated. <strong>Demo mode:</strong> Reset link displayed below.');
            } else {
                SessionManager::setMessage('error', 'Failed to send email. Please contact support or try again later.');
            }
        } else {
            SessionManager::setMessage('error', 'Failed to create reset token. Please try again.');
        }
        
        redirect('?page=forgot-password');
    }
    
    private function handlePasswordReset($token, $tokenData) {
        // CSRF validation
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            SessionManager::setMessage('error', 'Security validation failed');
            redirect('?page=reset-password&token=' . $token);
            return;
        }
        
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        // Validation
        if (empty($password) || empty($confirm_password)) {
            SessionManager::setMessage('error', 'Please fill in all fields');
            redirect('?page=reset-password&token=' . $token);
            return;
        }
        
        if ($password !== $confirm_password) {
            SessionManager::setMessage('error', 'Passwords do not match');
            redirect('?page=reset-password&token=' . $token);
            return;
        }
        
        if (strlen($password) < 8) {
            SessionManager::setMessage('error', 'Password must be at least 8 characters');
            redirect('?page=reset-password&token=' . $token);
            return;
        }
        
        // Update password
        $success = $this->passwordResetModel->updatePassword($tokenData['user_id'], $password);
        
        if ($success) {
            // Mark token as used
            $this->passwordResetModel->markTokenUsed($token);
            
            SessionManager::setMessage('success', 'Password reset successfully! You can now login with your new password.');
            redirect('?page=login');
        } else {
            SessionManager::setMessage('error', 'Failed to reset password. Please try again.');
            redirect('?page=reset-password&token=' . $token);
        }
    }
    
   private function sendResetEmail($email, $token, $username) {
    try {
        // UNIVERSITY PROJECT DEMO MODE
        $_SESSION['demo_reset_link'] = SITE_URL . "/?page=reset-password&token=" . $token;
        $_SESSION['demo_email'] = $email;
        $_SESSION['demo_username'] = $username;
        $_SESSION['demo_timestamp'] = date('Y-m-d H:i:s');
        
        error_log("DEMO MODE: Password reset ready for $email");
        error_log("Reset Link: " . $_SESSION['demo_reset_link']);
        
        // Always return true for demo
        return true;
        
    } catch (Exception $e) {
        error_log("❌ Demo mode error: " . $e->getMessage());
        return false;
    }
}
}
?>