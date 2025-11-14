<?php
require_once 'config.php';
require_once 'includes/Database.php';
require_once 'includes/SessionManager.php';
require_once 'includes/helpers.php';

SessionManager::init();

echo "<h1>Direct View Test</h1>";

// Test the view system step by step
$db = Database::getInstance();

// Get real data
require_once 'models/Category.php';
$categoryModel = new Category($db);
$categories = $categoryModel->getAllWithSubcategories();

echo "<h2>Step 1: Data loaded</h2>";
echo "<p>Categories count: " . count($categories) . "</p>";

echo "<h2>Step 2: Testing template directly</h2>";

// Test template directly
$templateFile = __DIR__ . '/views/home.php';
if (file_exists($templateFile)) {
    echo "<p>Template file exists</p>";
    
    // Extract data
    extract(['categories' => $categories, 'stats' => ['total_listings' => 6, 'total_categories' => 79, 'total_reviews' => 0]]);
    
    // Test output buffering
    ob_start();
    include $templateFile;
    $content = ob_get_clean();
    
    echo "<h3>Template Output:</h3>";
    echo "<div style='border: 2px solid red; padding: 10px;'>";
    echo $content;
    echo "</div>";
    echo "<p>Content length: " . strlen($content) . " bytes</p>";
} else {
    echo "<p>Template file NOT found: $templateFile</p>";
}

echo "<h2>Step 3: Testing layout</h2>";
$layoutFile = __DIR__ . '/views/layout.php';
if (file_exists($layoutFile)) {
    echo "<p>Layout file exists</p>";
    
    // Now test the full layout with content
    $pageTitle = "Direct Test";
    include $layoutFile;
} else {
    echo "<p>Layout file NOT found</p>";
}
?>