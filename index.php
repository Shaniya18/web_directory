<?php
// Load manual autoloader for PHPMailer
require_once __DIR__ . '/vendor/autoload.php';

// Error handling
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/logs/error.log');

// Load configuration FIRST
require_once __DIR__ . '/config.php';

// DEBUG: Check if config loaded
error_log("=== APPLICATION START ===");
error_log("Config loaded: " . DB_HOST . ", " . DB_USER . ", " . DB_NAME);

// Load core classes
require_once __DIR__ . '/includes/Database.php';
require_once __DIR__ . '/includes/SessionManager.php';
require_once __DIR__ . '/includes/Router.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/EmailService.php';

// DEBUG: Check if models exist
error_log("Checking model files:");
error_log("Listing.php exists: " . (file_exists(__DIR__ . '/models/Listing.php') ? 'YES' : 'NO'));
error_log("Category.php exists: " . (file_exists(__DIR__ . '/models/Category.php') ? 'YES' : 'NO'));

// Initialize session
SessionManager::init();

// Initialize database
try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    error_log("Database connected successfully!");
    
    // DEBUG: Test database connection and basic queries
    error_log("=== DATABASE TEST ===");
    
    // Test categories count
    $result = $db->query("SELECT COUNT(*) as cnt FROM categories");
    $catCount = $result->fetch_assoc();
    error_log("Total categories in DB: " . $catCount['cnt']);
    
    // Test listings count
    $result = $db->query("SELECT COUNT(*) as cnt FROM listings WHERE approved = 1");
    $listCount = $result->fetch_assoc();
    error_log("Total approved listings in DB: " . $listCount['cnt']);
    
    // Test search query directly
    $searchTerm = "fiji";
    $result = $db->query(
        "SELECT l.*, c.name as category_name 
         FROM listings l 
         LEFT JOIN categories c ON l.category_id = c.id 
         WHERE l.approved = 1 
         AND (l.title LIKE ? OR l.description LIKE ? OR l.tags LIKE ?) 
         LIMIT 5",
        ["%$searchTerm%", "%$searchTerm%", "%$searchTerm%"],
        "sss"
    );
    
    if ($result) {
        $searchResults = $result->fetch_all(MYSQLI_ASSOC);
        error_log("Direct search test for '$searchTerm': " . count($searchResults) . " results");
        foreach ($searchResults as $index => $row) {
            error_log("  Result $index: {$row['title']} | Category: {$row['category_name']}");
        }
    }
    
} catch (Exception $e) {
    error_log("Database error: " . $e->getMessage());
}

// DEBUG: Test if models load correctly
try {
    error_log("=== MODEL LOAD TEST ===");
    
    // Test Listing model
    require_once __DIR__ . '/models/Listing.php';
    $listingModel = new Listing($db);
    error_log("Listing model loaded successfully");
    
    // Test Category model  
    require_once __DIR__ . '/models/Category.php';
    $categoryModel = new Category($db);
    error_log("Category model loaded successfully");
    
    // Test search method
    $testResults = $listingModel->search('fiji', 3, 0);
    error_log("Listing model search test: " . count($testResults) . " results");
    
    // Test category counting
    $testCategories = $categoryModel->getAllWithSubcategories();
    error_log("Category model test: " . count($testCategories) . " main categories");
    
} catch (Exception $e) {
    error_log("Model loading error: " . $e->getMessage());
}

// Initialize and run router
try {
    error_log("=== ROUTER INIT ===");
    $router = new Router($db);
    error_log("Router created, handling request...");
    $router->handleRequest();
} catch (Exception $e) {
    error_log("Router error: " . $e->getMessage());
    echo "Application error. Check error logs for details.";
}

// Close database connection
if (isset($db)) {
    $db->close();
    error_log("Database connection closed");
}

error_log("=== APPLICATION END ===");
?>