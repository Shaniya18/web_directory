<?php
// Enable all errors
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/Database.php';
require_once __DIR__ . '/includes/helpers.php';

echo "<h1>Debug Test - Fiji Web Directory</h1>";

try {
    $db = Database::getInstance();
    echo "<p style='color: green;'>✅ Database connected successfully</p>";
    
    // Test 1: Check if models exist
    echo "<h2>1. Model Files Check</h2>";
    $models = ['Listing.php', 'Category.php'];
    foreach ($models as $model) {
        $path = __DIR__ . '/models/' . $model;
        if (file_exists($path)) {
            echo "<p style='color: green;'>✅ $model exists</p>";
        } else {
            echo "<p style='color: red;'>❌ $model MISSING at: $path</p>";
        }
    }
    
    // Test 2: Load models
    echo "<h2>2. Model Loading Test</h2>";
    require_once __DIR__ . '/models/Listing.php';
    require_once __DIR__ . '/models/Category.php';
    
    $listingModel = new Listing($db);
    $categoryModel = new Category($db);
    echo "<p style='color: green;'>✅ Models loaded successfully</p>";
    
    // Test 3: Search functionality
    echo "<h2>3. Search Test</h2>";
    $results = $listingModel->search('fiji', 5, 0);
    echo "<p>Search results count: " . count($results) . "</p>";
    
    if (count($results) > 0) {
        echo "<p style='color: green;'>✅ Search works - found " . count($results) . " results</p>";
        foreach ($results as $result) {
            echo "<div style='border: 1px solid #ccc; padding: 10px; margin: 5px;'>";
            echo "<strong>Title:</strong> " . htmlspecialchars($result['title']) . "<br>";
            echo "<strong>Category:</strong> " . ($result['category_name'] ?? 'MISSING') . "<br>";
            echo "<strong>URL:</strong> " . htmlspecialchars($result['url']);
            echo "</div>";
        }
    } else {
        echo "<p style='color: orange;'>⚠️ Search returned 0 results (might be expected)</p>";
    }
    
    // Test 4: Category counting
    echo "<h2>4. Category Counting Test</h2>";
    $categories = $categoryModel->getAllWithSubcategories();
    echo "<p>Total main categories: " . count($categories) . "</p>";
    
    foreach ($categories as $category) {
        echo "<div style='border: 1px solid #ccc; padding: 10px; margin: 5px;'>";
        echo "<strong>" . htmlspecialchars($category['name']) . "</strong> (" . $category['listing_count'] . " listings)<br>";
        
        if (!empty($category['subcategories'])) {
            echo "<div style='margin-left: 20px;'>";
            foreach ($category['subcategories'] as $subcat) {
                echo htmlspecialchars($subcat['name']) . " (" . $subcat['listing_count'] . " listings)<br>";
            }
            echo "</div>";
        }
        echo "</div>";
    }
    
    // Test 5: Direct database query
    echo "<h2>5. Direct Database Query Test</h2>";
    $result = $db->query("SELECT COUNT(*) as cnt FROM listings WHERE approved = 1");
    $row = $result->fetch_assoc();
    echo "<p>Total approved listings in database: " . $row['cnt'] . "</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<h2>Next Steps:</h2>";
echo "<p><a href='?page=home'>Go to Homepage</a></p>";
echo "<p><a href='?page=search&search=fiji'>Test Search Page</a></p>";
?>