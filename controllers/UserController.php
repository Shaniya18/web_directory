<?php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Listing.php';
require_once __DIR__ . '/../models/Review.php';

class UserController {
    private $db;
    private $userModel;
    private $listingModel;
    private $reviewModel;
    
    public function __construct($db) {
        $this->db = $db;
        $this->userModel = new User($db); 
        $this->listingModel = new Listing($db);
        $this->reviewModel = new Review($db); 
    }
    
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['action']) && $_POST['action'] === 'user_login') {
                $this->handleUserLogin();
            }
        }
        
        view('user/login', [
            'tab' => 'user',
            'pageTitle' => 'Login - Fiji Web Directory'
        ]);
    }
    
    public function register() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'user_register') {
            $this->handleUserRegistration();
        }
        
        view('user/register', [
            'pageTitle' => 'Register - Fiji Web Directory'
        ]);
    }
    
    public function profile() {
        if (!SessionManager::isLoggedIn()) {
            redirect('?page=login');
        }
        
        $userId = SessionManager::getUserId();
        $user = $this->userModel->findById($userId);
        
        if (!$user) {
            SessionManager::setMessage('error', 'User not found');
            redirect('?page=login');
        }
        
        $listings = $this->listingModel->getByUser($user['id']);
        $reviews = $this->reviewModel->getByUserId($user['id']);
        
        view('user/profile', [
            'user' => $user,
            'listings' => $listings,
            'reviews' => $reviews,
            'pageTitle' => 'My Profile - Fiji Web Directory'
        ]);
    }
    
    public function logout() {
        SessionManager::destroy();
        SessionManager::setMessage('success', 'You have been logged out successfully');
        redirect('?page=home');
    }
    
    public function staffLogin() {
        // Additional security checks
        $allowedIps = ['127.0.0.1', '::1']; // Add your allowed IPs
        $clientIp = $_SERVER['REMOTE_ADDR'];
        
        // Restrict access to specific IPs in production
        if (ENVIRONMENT === 'production' && !in_array($clientIp, $allowedIps)) {
            error_log("Unauthorized staff login attempt from: " . $clientIp);
            SessionManager::setMessage('error', 'Access denied');
            redirect('?page=home');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'staff_login') {
            $this->handleStaffLogin();
        }

        view('user/staff_login', [
            'pageTitle' => 'Staff Access - Fiji Web Directory'
        ]);
    }
    
    private function handleUserLogin() {
        // CSRF validation
        if (!$this->validateCsrfToken()) {
            SessionManager::setMessage('error', 'Security validation failed');
            redirect('?page=login');
        }
        
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        
        // Input validation
        if (empty($email) || empty($password)) {
            SessionManager::setMessage('error', 'Email and password are required');
            redirect('?page=login');
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            SessionManager::setMessage('error', 'Invalid email format');
            redirect('?page=login');
        }
        
        // Check if account is locked before attempting login
        $user = $this->userModel->findByEmail($email);
        if ($user && $this->userModel->isAccountLocked($user['id'])) {
            $lockTime = $this->userModel->getLockTimeRemaining($user['id']);
            SessionManager::setMessage('error', "Account temporarily locked. Please try again in $lockTime");
            redirect('?page=login');
        }
        
        // IP-based rate limiting
        if (!$this->checkIpRateLimit()) {
            SessionManager::setMessage('error', 'Too many login attempts from your IP. Please try again in 15 minutes.');
            redirect('?page=login');
        }
        
        $user = $this->userModel->validateLogin($email, $password);
        if ($user) {
            // Reset login attempts on successful login
            $this->userModel->resetLoginAttempts($user['id']);
            $this->resetIpRateLimit();
            
            if ($user['is_verified'] == 0) {
                SessionManager::setMessage('error', 'Please verify your email before logging in.');
                redirect('?page=login');
            }
            
            SessionManager::setUser($user);
            SessionManager::setMessage('success', 'Welcome back, ' . htmlspecialchars($user['username']) . '!');
            
            // Redirect to intended page or profile
            $redirectTo = $_SESSION['redirect_after_login'] ?? '?page=profile';
            unset($_SESSION['redirect_after_login']);
            redirect($redirectTo);
        } else {
            // Increment login attempts for the user
            if ($user) {
                $this->userModel->incrementLoginAttempts($user['id']);
                $attempts = $this->userModel->getLoginAttempts($user['id']);
                $attemptsLeft = 5 - $attempts;
                
                // Lock account after 5 failed attempts
                if ($attempts >= 5) {
                    $this->userModel->lockAccount($user['id']);
                    SessionManager::setMessage('error', 'Account locked due to too many failed attempts. Please try again in 15 minutes.');
                    
                    // Log the lockout for security monitoring
                    error_log("Account locked for user: " . $email . " from IP: " . $_SERVER['REMOTE_ADDR']);
                } else {
                    SessionManager::setMessage('error', "Invalid email or password. $attemptsLeft attempts remaining before account lock.");
                }
            } else {
                SessionManager::setMessage('error', 'Invalid email or password');
            }
            
            $this->incrementIpRateLimit();
            redirect('?page=login');
        }
    }
    
    private function handleUserRegistration() {
        // CSRF validation
        if (!$this->validateCsrfToken()) {
            SessionManager::setMessage('error', 'Security validation failed');
            return;
        }
        
        $data = [
            'username' => trim($_POST['username'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'password' => $_POST['password'] ?? '',
            'full_name' => trim($_POST['full_name'] ?? '')
        ];
        
        // Validation
        $validationErrors = $this->validateRegistrationData($data);
        if (!empty($validationErrors)) {
            SessionManager::setMessage('error', implode('<br>', $validationErrors));
            return;
        }
        
        // Check if username/email exists
        if ($this->userModel->findByUsernameOrEmail($data['username'], $data['email'])) {
            SessionManager::setMessage('error', 'Username or email already exists');
            return;
        }
        
        // Check if password has been used by any existing user
        if ($this->userModel->isPasswordUsedByAnyUser($data['password'])) {
            SessionManager::setMessage('error', 'This password has been used before. Please choose a different password.');
            return;
        }
        
        if ($this->userModel->create($data)) {
            SessionManager::setMessage('success', 'Registration successful! You can now login.');
            redirect('?page=login');
        } else {
            SessionManager::setMessage('error', 'Registration failed. Please try again.');
        }
    }
    
    private function handleStaffLogin() {
        // Enhanced CSRF validation
        if (!$this->validateCsrfToken()) {
            SessionManager::setMessage('error', 'Security validation failed');
            redirect('?page=staff');
        }

        // Rate limiting for staff login
        if (!$this->checkRateLimit('staff_login', 3, 1800)) { // 3 attempts per 30 minutes
            SessionManager::setMessage('error', 'Too many failed attempts. Please try again in 30 minutes.');
            redirect('?page=staff');
        }

        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($password)) {
            $this->incrementRateLimit('staff_login');
            SessionManager::setMessage('error', 'Username and password are required');
            redirect('?page=staff');
        }

        $admin = $this->userModel->getAdminByUsername($username);
        if ($admin && password_verify($password, $admin['password'])) {
            // Successful login - reset attempts
            $this->resetRateLimit('staff_login');
            SessionManager::setAdmin($admin);
            SessionManager::setMessage('success', 'Welcome back!');
            
            // Log successful admin login
            error_log("Staff login successful: " . $username . " from IP: " . $_SERVER['REMOTE_ADDR']);
            
            redirect('?page=admin');
        } else {
            // Failed attempt
            $this->incrementRateLimit('staff_login');
            
            // Log failed attempt
            error_log("Failed staff login attempt for: " . $username . " from IP: " . $_SERVER['REMOTE_ADDR']);
            
            SessionManager::setMessage('error', 'Invalid credentials');
            redirect('?page=staff');
        }
    }
    
    private function validateRegistrationData($data) {
        $errors = [];
        
        // Required fields
        if (empty($data['username'])) {
            $errors[] = 'Username is required';
        }
        
        if (empty($data['email'])) {
            $errors[] = 'Email is required';
        }
        
        if (empty($data['password'])) {
            $errors[] = 'Password is required';
        }
        
        // Email format
        if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email address';
        }
        
        // Password strength
        $passwordErrors = $this->validatePasswordStrength($data['password']);
        if (!empty($passwordErrors)) {
            $errors = array_merge($errors, $passwordErrors);
        }
        
        // Username format (alphanumeric and underscores)
        if (!empty($data['username']) && !preg_match('/^[a-zA-Z0-9_]{3,30}$/', $data['username'])) {
            $errors[] = 'Username must be 3-30 characters and can only contain letters, numbers, and underscores';
        }
        
        return $errors;
    }
    
    private function validatePasswordStrength($password) {
        $errors = [];
        
        if (strlen($password) < 12) {
            $errors[] = 'Password must be at least 12 characters long';
        }
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Password must contain at least one uppercase letter';
        }
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = 'Password must contain at least one lowercase letter';
        }
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = 'Password must contain at least one number';
        }
        if (!preg_match('/[!@#$%^&*()\-_=+{};:,<.>]/', $password)) {
            $errors[] = 'Password must contain at least one special character';
        }
        
        // Check for common passwords
        if ($this->isCommonPassword($password)) {
            $errors[] = 'This password is too common. Please choose a more unique password.';
        }
        
        return $errors;
    }
    
    private function isCommonPassword($password) {
        $commonPasswords = [
            'password', 'password123', '123456', '12345678', '123456789',
            'qwerty', 'abc123', 'letmein', 'welcome', 'admin123'
        ];
        
        return in_array(strtolower($password), $commonPasswords);
    }
    
    private function validateCsrfToken() {
        return isset($_POST['csrf_token']) && $_POST['csrf_token'] === ($_SESSION['csrf_token'] ?? '');
    }
    
    // IP-based rate limiting for login attempts
    private function checkIpRateLimit() {
        $ip = $_SERVER['REMOTE_ADDR'];
        $key = 'ip_login_attempts_' . md5($ip);
        $lastAttemptKey = 'ip_login_last_attempt_' . md5($ip);
        
        if (!isset($_SESSION[$key])) {
            $_SESSION[$key] = 0;
            $_SESSION[$lastAttemptKey] = time();
        }
        
        // Reset attempts after 15 minutes
        if (time() - $_SESSION[$lastAttemptKey] > 900) { // 15 minutes
            $_SESSION[$key] = 0;
        }
        
        return $_SESSION[$key] < 10; // 10 attempts per IP per 15 minutes
    }
    
    private function incrementIpRateLimit() {
        $ip = $_SERVER['REMOTE_ADDR'];
        $key = 'ip_login_attempts_' . md5($ip);
        $lastAttemptKey = 'ip_login_last_attempt_' . md5($ip);
        
        $_SESSION[$key] = ($_SESSION[$key] ?? 0) + 1;
        $_SESSION[$lastAttemptKey] = time();
    }
    
    private function resetIpRateLimit() {
        $ip = $_SERVER['REMOTE_ADDR'];
        $key = 'ip_login_attempts_' . md5($ip);
        $lastAttemptKey = 'ip_login_last_attempt_' . md5($ip);
        
        unset($_SESSION[$key], $_SESSION[$lastAttemptKey]);
    }
    
    private function checkRateLimit($type, $maxAttempts, $timeWindow) {
        $key = $type . '_attempts';
        $lastAttemptKey = $type . '_last_attempt';
        
        if (!isset($_SESSION[$key])) {
            $_SESSION[$key] = 0;
            $_SESSION[$lastAttemptKey] = time();
        }
        
        // Reset attempts after time window
        if (time() - $_SESSION[$lastAttemptKey] > $timeWindow) {
            $_SESSION[$key] = 0;
        }
        
        return $_SESSION[$key] < $maxAttempts;
    }
    
    private function incrementRateLimit($type) {
        $key = $type . '_attempts';
        $lastAttemptKey = $type . '_last_attempt';
        
        $_SESSION[$key] = ($_SESSION[$key] ?? 0) + 1;
        $_SESSION[$lastAttemptKey] = time();
    }
    
    private function resetRateLimit($type) {
        $key = $type . '_attempts';
        $lastAttemptKey = $type . '_last_attempt';
        
        unset($_SESSION[$key], $_SESSION[$lastAttemptKey]);
    }
}
?>