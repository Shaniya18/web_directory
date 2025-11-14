<?php
require_once __DIR__ . '/../models/User.php';

class StaffAuthController {
    private $db;
    private $userModel;
    
    public function __construct($db) {
        $this->db = $db;
        $this->userModel = new User($db);
    }
    
    public function login() {
        // Redirect to dashboard if already logged in
        if (SessionManager::isAdmin()) {
            header('Location: staff.php?page=dashboard');
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleLogin();
        } else {
            $this->showLoginForm();
        }
    }
    
    private function handleLogin() {
        // CSRF validation
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $this->showLoginForm('Security validation failed');
            return;
        }
        
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        
        // Input validation
        if (empty($username) || empty($password)) {
            $this->showLoginForm('Please enter both username and password');
            return;
        }
        
        // Rate limiting
        if (!isset($_SESSION['staff_login_attempts'])) {
            $_SESSION['staff_login_attempts'] = 0;
            $_SESSION['staff_login_last_attempt'] = time();
        }
        
        // Reset attempts after 15 minutes
        if (time() - $_SESSION['staff_login_last_attempt'] > 900) {
            $_SESSION['staff_login_attempts'] = 0;
        }
        
        // Check if locked out
        if ($_SESSION['staff_login_attempts'] >= 5) {
            $this->showLoginForm('Too many failed attempts. Please try again in 15 minutes.');
            return;
        }
        
        $admin = $this->userModel->getAdminByUsername($username);
        
        if ($admin && password_verify($password, $admin['password'])) {
            // Successful login - reset attempts and set session
            $_SESSION['staff_login_attempts'] = 0;
            SessionManager::setAdmin($admin);
            
            // Log successful login
            logAuthEvent($username, true);
            
            header('Location: staff.php?page=dashboard');
            exit;
        } else {
            // Failed attempt
            $_SESSION['staff_login_attempts']++;
            $_SESSION['staff_login_last_attempt'] = time();
            
            // Log failed attempt
            logAuthEvent($username, false, $_SESSION['staff_login_attempts']);
            
            // Generic error message for security
            $this->showLoginForm('Invalid staff credentials');
        }
    }
    
    private function showLoginForm($error = '') {
        $errorMessage = $error;
        
        // Use separate staff layout
        include __DIR__ . '/../views/staff/login.php';
    }
    
    public function logout() {
        // Log logout action
        if (SessionManager::isAdmin()) {
            logSecurityEvent('STAFF_LOGOUT', $_SESSION['admin_id'] ?? null);
        }
        
        SessionManager::destroy();
        
        // Clear any staff-specific session data
        if (isset($_SESSION['staff_login_attempts'])) {
            unset($_SESSION['staff_login_attempts']);
        }
        if (isset($_SESSION['staff_login_last_attempt'])) {
            unset($_SESSION['staff_login_last_attempt']);
        }
        
        header('Location: staff.php?page=login');
        exit;
    }
}
?>