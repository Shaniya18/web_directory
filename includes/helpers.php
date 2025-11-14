<?php
require_once __DIR__ . '/SessionManager.php';

// SECURITY: Output sanitization for XSS protection
function sanitizeOutput($text) {
    return htmlspecialchars($text ?? '', ENT_QUOTES, 'UTF-8');
}

// SECURITY: Input sanitization for all user inputs
function sanitizeInput($input) {
    return htmlspecialchars(trim($input ?? ''), ENT_QUOTES, 'UTF-8');
}

// SECURITY: Input validation functions
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function validateURL($url) {
    return filter_var($url, FILTER_VALIDATE_URL) !== false;
}

// SECURITY: CSRF protection
function generateCSRFToken() {
    return SessionManager::generateCSRFToken();
}

function validateCSRFToken($token) {
    return SessionManager::verifyCSRFToken($token);
}

function csrfField() {
    return '<input type="hidden" name="csrf_token" value="' . generateCSRFToken() . '">';
}

// Application functions
function redirect($url) {
    header('Location: ' . $url);
    exit;
}

function view($template, $data = []) {
    // SECURITY: Path traversal protection
    $template = str_replace(['../', './', '/\\'], '', $template);
    
    extract($data);
    ob_start();
    $templateFile = __DIR__ . '/../views/' . $template . '.php';
    
    // SECURITY: Validate template is within allowed directory
    if (file_exists($templateFile) && strpos(realpath($templateFile), realpath(__DIR__ . '/../views/')) === 0) {
        include $templateFile;
    } else {
        http_response_code(404);
        echo "Page not found";
    }
    
    $content = ob_get_clean();
    $layoutFile = __DIR__ . '/../views/layout.php';
    if (file_exists($layoutFile)) {
        include $layoutFile;
    } else {
        echo $content;
    }
}

function partial($partial, $data = []) {
    extract($data);
    $partialFile = __DIR__ . '/../views/partials/' . $partial . '.php';
    if (file_exists($partialFile)) {
        include $partialFile;
    }
}

function generateCaptcha() {
    $_SESSION['captcha_num1'] = rand(1, 10);
    $_SESSION['captcha_num2'] = rand(1, 10);
    return $_SESSION['captcha_num1'] . ' + ' . $_SESSION['captcha_num2'];
}

function validateCaptcha($answer) {
    $expected = intval($_SESSION['captcha_num1'] ?? 0) + intval($_SESSION['captcha_num2'] ?? 0);
    return intval($answer) === $expected;
}

function displayMessages() {
    $html = '';
    if ($success = SessionManager::getMessage('success')) {
        $html .= '<div class="message message-success">' . sanitizeOutput($success) . '</div>';
    }
    if ($error = SessionManager::getMessage('error')) {
        $html .= '<div class="message message-error">' . sanitizeOutput($error) . '</div>';
    }
    return $html;
}

function displayRatingStars($rating) {
    $stars = '';
    for ($i = 1; $i <= 5; $i++) {
        $stars .= $i <= $rating ? '★' : '☆';
    }
    return $stars;
}

function formatDate($date) {
    return date('M d, Y', strtotime($date));
}

function displayPagination($total, $per_page, $current_page, $base_url) {
    $total_pages = ceil($total / $per_page);
    
    if ($total_pages <= 1) return;
    ?>
    <div class="pagination">
        <?php if ($current_page > 1): ?>
            <a href="<?php echo $base_url . '&p=' . ($current_page - 1); ?>">&laquo; Previous</a>
        <?php endif; ?>
        
        <?php for ($i = max(1, $current_page - 3); $i <= min($total_pages, $current_page + 3); $i++): ?>
            <?php if ($i == $current_page): ?>
                <span class="current"><?php echo $i; ?></span>
            <?php else: ?>
                <a href="<?php echo $base_url . '&p=' . $i; ?>"><?php echo $i; ?></a>
            <?php endif; ?>
        <?php endfor; ?>
        
        <?php if ($current_page < $total_pages): ?>
            <a href="<?php echo $base_url . '&p=' . ($current_page + 1); ?>">Next &raquo;</a>
        <?php endif; ?>
    </div>
    <?php
}
// SECURITY: Comprehensive security event logging
function logSecurityEvent($eventType, $userId = null, $additionalData = '') {
    $userId = $userId ?? SessionManager::getUserId();
    $ip = $_SERVER['REMOTE_ADDR'];
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
    $requestUri = $_SERVER['REQUEST_URI'] ?? 'Unknown';
    
    $logEntry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'event' => $eventType,
        'user_id' => $userId ?: 'guest',
        'ip_address' => $ip,
        'user_agent' => substr($userAgent, 0, 200),
        'request_uri' => $requestUri,
        'additional_data' => $additionalData
    ];
    
    $logMessage = json_encode($logEntry) . PHP_EOL;
    
    $logDir = __DIR__ . '/../logs';
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    $logFile = $logDir . '/security.log';
    file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
}

// SECURITY: Authentication event logging
function logAuthEvent($username, $success, $attemptNumber = null) {
    $eventType = $success ? 'LOGIN_SUCCESS' : 'LOGIN_FAILED';
    $details = 'username: ' . $username;
    if ($attemptNumber) {
        $details .= ' | attempt: ' . $attemptNumber;
    }
    logSecurityEvent($eventType, null, $details);
}
?>