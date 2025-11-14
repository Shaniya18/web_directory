<?php
// staff.php - Separate Staff Entry Point
require_once __DIR__ . '/vendor/autoload.php';

// Error handling
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Load configuration
require_once __DIR__ . '/config.php';

// Load core classes
require_once __DIR__ . '/includes/Database.php';
require_once __DIR__ . '/includes/SessionManager.php';
require_once __DIR__ . '/includes/helpers.php';

// Initialize session
SessionManager::init();

// Initialize database
try {
    $db = Database::getInstance();
} catch (Exception $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Staff-only routes
$page = $_GET['page'] ?? 'login';

// Staff authentication check (except for login page)
if ($page !== 'login' && !SessionManager::isAdmin()) {
    header('Location: staff.php?page=login');
    exit;
}

// Staff pages routing
switch ($page) {
    case 'login':
        require_once __DIR__ . '/controllers/StaffAuthController.php';
        $controller = new StaffAuthController($db);
        $controller->login();
        break;
        
    case 'dashboard':
        require_once __DIR__ . '/controllers/StaffDashboardController.php';
        $controller = new StaffDashboardController($db);
        $controller->index();
        break;
        
    case 'logout':
        require_once __DIR__ . '/controllers/StaffAuthController.php';
        $controller = new StaffAuthController($db);
        $controller->logout();
        break;
        
    default:
        header('Location: staff.php?page=dashboard');
        exit;
}
?>