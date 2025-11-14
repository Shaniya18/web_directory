<?php
require_once 'config.php';
require_once 'includes/Database.php';
require_once 'includes/SessionManager.php';
require_once 'includes/helpers.php';

SessionManager::init();

echo "<h1>Debug View System</h1>";

// Test the view function step by step
$db = Database::getInstance();
require_once 'models/Category.php';
$categoryModel = new Category($db);
$categories = $categoryModel->getAllWithSubcategories();

echo "<h2>Step 1: Testing template output</h2>";

// Test template directly
ob_start();
extract(['categories' => $categories, 'stats' => ['total_listings' => 6, 'total_categories' => 10, 'total_reviews' => 0]]);
include __DIR__ . '/views/home.php';
$template_content = ob_get_clean();

echo "<p>Template output length: " . strlen($template_content) . " bytes</p>";
echo "<div style='border: 2px solid blue; padding: 10px;'>" . $template_content . "</div>";

echo "<h2>Step 2: Testing layout with content</h2>";

// Now test layout with the content
$content = $template_content; // Use the content we captured
$pageTitle = "Debug Test";
include __DIR__ . '/views/layout.php';
?>