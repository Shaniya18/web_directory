<?php
require_once 'config.php';
require_once 'includes/Database.php';
require_once 'includes/SessionManager.php';
require_once 'includes/helpers.php';

SessionManager::init();

echo "<h1>Simple View Test</h1>";

// Test with simple hardcoded data first
$categories = [
    ['id' => 1, 'name' => 'Test Business', 'listing_count' => 5, 'subcategories' => []],
    ['id' => 2, 'name' => 'Test Tourism', 'listing_count' => 3, 'subcategories' => []]
];

$stats = [
    'total_listings' => 8,
    'total_categories' => 2,
    'total_reviews' => 12
];

echo "<h2>Testing with hardcoded data:</h2>";

// Use the view function
view('home', [
    'categories' => $categories,
    'stats' => $stats,
    'pageTitle' => 'Simple Test'
]);

echo "<h2>Testing with real data:</h2>";

// Now test with real data
$db = Database::getInstance();
require_once 'models/Category.php';
$categoryModel = new Category($db);
$realCategories = $categoryModel->getAllWithSubcategories();

view('home', [
    'categories' => $realCategories,
    'stats' => $stats, // Using same stats for now
    'pageTitle' => 'Real Data Test'
]);
?>