<?php
require_once 'config.php';
require_once 'includes/Database.php';
require_once 'includes/SessionManager.php';
require_once 'includes/helpers.php';

SessionManager::init();

echo "<h1>Testing FIXED Home.php</h1>";

// Simple test data
$categories = [
    ['id' => 1, 'name' => 'Test Business', 'listing_count' => 5, 'subcategories' => []],
    ['id' => 2, 'name' => 'Test Tourism', 'listing_count' => 3, 'subcategories' => []]
];

$stats = [
    'total_listings' => 8,
    'total_categories' => 2,
    'total_reviews' => 12
];

// Test the view
view('home', [
    'categories' => $categories,
    'stats' => $stats,
    'pageTitle' => 'Fixed Home Test'
]);
?>