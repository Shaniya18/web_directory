<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'web_directory');

// Site configuration
define('SITE_NAME', 'Fiji Web Directory');
define('SITE_URL', 'http://localhost/web_directory');

// Gmail SMTP Configuration
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'fijiwebdirectory@gmail.com');
define('SMTP_PASSWORD', 'lkvhhxryarddogeq');
define('SMTP_FROM_EMAIL', 'noreply@yourdomain.com');
define('SMTP_FROM_NAME', 'Fiji Web Directory');
define('SMTP_DEBUG', 0); //  CHANGED: 0 for production (was 2 for debugging)

date_default_timezone_set('Pacific/Fiji');

// Session security
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_samesite', 'Strict'); //  ADDED: CSRF protection

// CHANGED: Production error handling (was debug mode)
error_reporting(E_ALL); // Still log all errors internally
ini_set('display_errors', 0);          //  CHANGED: Hide errors from users (was 1)
ini_set('display_startup_errors', 0);  //  CHANGED: Hide startup errors (was 1)
ini_set('log_errors', 1);              //  ADDED: Enable error logging
ini_set('error_log', __DIR__ . '/logs/php_errors.log'); //  ADDED: Log file

//  ADDED: Security headers function
function setSecurityHeaders() {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('X-XSS-Protection: 1; mode=block');
    header_remove('X-Powered-By'); // Hide PHP version
}

//  ADDED: Create logs directory if it doesn't exist
$logDir = __DIR__ . '/logs';
if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
}
// Add to config.php
define('ENVIRONMENT', 'production'); // or 'development'
?>