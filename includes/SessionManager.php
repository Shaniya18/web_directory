<?php
class SessionManager {
    public static function init() {
        // Session security settings
        ini_set('session.cookie_httponly', 1);
        ini_set('session.cookie_secure', 1);
        ini_set('session.use_strict_mode', 1);
        ini_set('session.cookie_samesite', 'Strict'); // ADDED: SameSite protection
        
        session_start();
        
        // Regenerate session periodically
        if (!isset($_SESSION['created'])) {
            $_SESSION['created'] = time();
        } else if (time() - $_SESSION['created'] > 1800) {
            session_regenerate_id(true);
            $_SESSION['created'] = time();
        }
        
        // CSRF Token with expiration
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            $_SESSION['csrf_token_time'] = time(); //  ADDED: Token expiration tracking
        }
        
        //  ADDED: Session timeout (1 hour inactivity)
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 3600)) {
            self::destroy();
            header('Location: ?page=login');
            exit;
        }
        $_SESSION['last_activity'] = time();
    }
    
    public static function isAdmin() {
        return isset($_SESSION['admin']) && $_SESSION['admin'] === true;
    }
    
    public static function isLoggedIn() {
        return isset($_SESSION['user_id']) && $_SESSION['user_id'] > 0;
    }
    
    public static function getUserId() {
        return $_SESSION['user_id'] ?? null;
    }
    
    public static function getUsername() {
        return $_SESSION['username'] ?? null;
    }
    
    public static function setUser($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['last_activity'] = time(); // ADDED: Update activity on login
    }
    
    public static function setAdmin($admin) {
        $_SESSION['admin'] = true;
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['last_activity'] = time(); //  ADDED: Update activity on admin login
    }
    
    public static function setMessage($type, $message) {
        $_SESSION[$type] = $message;
    }
    
    public static function getMessage($type) {
        $message = $_SESSION[$type] ?? null;
        unset($_SESSION[$type]);
        return $message;
    }
    
    //  ADDED: Secure CSRF token validation with expiration
    public static function validateCsrfToken($token) {
        if (!isset($_SESSION['csrf_token']) || !isset($_SESSION['csrf_token_time'])) {
            return false;
        }
        
        // Token expires after 1 hour
        if (time() - $_SESSION['csrf_token_time'] > 3600) {
            unset($_SESSION['csrf_token'], $_SESSION['csrf_token_time']);
            return false;
        }
        
        return hash_equals($_SESSION['csrf_token'], $token);
    }
    
    // ADDED: Generate new CSRF token
    public static function generateCsrfToken() {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $_SESSION['csrf_token_time'] = time();
        return $_SESSION['csrf_token'];
    }
    
    public static function destroy() {
        //  IMPROVED: Clear all session data
        $_SESSION = array();
        
        // IMPROVED: Delete session cookie
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        session_destroy();
    }
}
?>