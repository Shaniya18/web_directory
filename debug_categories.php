<?php
require_once 'config.php';
require_once 'models/Database.php';
require_once 'models/Category.php';

$db = Database::getInstance()->getConnection();
$categoryModel = new Category($db);

echo "<h2>Category Debug Information</h2>";

// Test getting all categories with subcategories
$categories = $categoryModel->getAllWithSubcategories();

echo "<h3>Main Categories Found: " . count($categories) . "</h3>";

foreach ($categories as $cat) {
    echo "<div style='border: 1px solid #ccc; margin: 10px; padding: 10px;'>";
    echo "<strong>ID:</strong> " . $cat['id'] . "<br>";
    echo "<strong>Name:</strong> " . $cat['name'] . "<br>";
    echo "<strong>Description:</strong> " . ($cat['description'] ?? 'None') . "<br>";
    echo "<strong>Icon:</strong> " . ($cat['icon'] ?? 'None') . "<br>";
    echo "<strong>Listings Count:</strong> " . ($cat['listing_count'] ?? 0) . "<br>";
    echo "<strong>Subcategories:</strong> " . count($cat['subcategories'] ?? []) . "<br>";
    
    if (!empty($cat['subcategories'])) {
        echo "<ul>";
        foreach ($cat['subcategories'] as $sub) {
            echo "<li>{$sub['name']} (ID: {$sub['id']}, Listings: {$sub['listing_count']})</li>";
        }
        echo "</ul>";
    }
    echo "</div>";
}
?>