<?php
require_once 'config.php';
require_once 'includes/Database.php';
require_once 'includes/SessionManager.php';
require_once 'includes/helpers.php'; // ADD THIS LINE

SessionManager::init();

echo "<h1>Final Working Test</h1>";

// Test data
$categories = [
    ['id' => 1, 'name' => 'Business', 'listing_count' => 5, 'subcategories' => []],
    ['id' => 2, 'name' => 'Tourism', 'listing_count' => 3, 'subcategories' => [
        ['id' => 3, 'name' => 'Hotels', 'listing_count' => 2]
    ]]
];

$stats = [
    'total_listings' => 8,
    'total_categories' => 2,
    'total_reviews' => 12
];

echo "<h2>Calling view function:</h2>";

// Call the actual view function from helpers.php
view('home', [
    'categories' => $categories,
    'stats' => $stats,
    'pageTitle' => 'Final Test - Working!'
]);
?>