<?php
// Test the layout system directly
require_once 'includes/helpers.php';

// Test data
$categories = [
    ['id' => 1, 'name' => 'Test Category 1', 'listing_count' => 5],
    ['id' => 2, 'name' => 'Test Category 2', 'listing_count' => 3]
];

$stats = [
    'total_listings' => 8,
    'total_categories' => 2, 
    'total_reviews' => 12
];

$pageTitle = 'Test Page';

echo "<h1>Testing Layout System</h1>";

// Manually test the view function
function testView($template, $data = []) {
    extract($data);
    
    ob_start();
    $templateFile = __DIR__ . '/views/' . $template . '.php';
    
    if (file_exists($templateFile)) {
        include $templateFile;
    } else {
        echo "Template not found: $template";
    }
    
    $content = ob_get_clean();
    
    // Now include layout
    $layoutFile = __DIR__ . '/views/layout.php';
    if (file_exists($layoutFile)) {
        include $layoutFile;
    } else {
        echo "Layout not found!";
    }
}

echo "<h2>Calling testView function:</h2>";
testView('home', [
    'categories' => $categories,
    'stats' => $stats,
    'pageTitle' => 'Test Page'
]);
?>