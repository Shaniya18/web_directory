<?php
// Emergency test - bypass everything
require_once 'config.php';
require_once 'includes/Database.php';
require_once 'includes/SessionManager.php';
require_once 'includes/helpers.php';

SessionManager::init();

// Get real data
$db = Database::getInstance();
require_once 'models/Category.php';
$categoryModel = new Category($db);
$categories = $categoryModel->getAllWithSubcategories();

echo "<h1>EMERGENCY TEST - Direct Output</h1>";
echo "<p>Categories count: " . count($categories) . "</p>";

if (!empty($categories)) {
    echo "<h2>Categories:</h2>";
    foreach ($categories as $cat) {
        echo "<p>" . htmlspecialchars($cat['name']) . " (" . ($cat['listing_count'] ?? 0) . ")</p>";
    }
}

echo "<h2>Now testing view system:</h2>";

// Test view system
view('home', [
    'categories' => $categories,
    'stats' => ['total_listings' => 6, 'total_categories' => 79, 'total_reviews' => 0],
    'pageTitle' => 'Emergency Test'
]);
?>