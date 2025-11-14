<?php
echo "<h1>Verifying Home.php File</h1>";

$homeFile = __DIR__ . '/views/home.php';

// Create the file if it doesn't exist or is empty
if (!file_exists($homeFile) || filesize($homeFile) === 0) {
    $content = '<?php
ob_start();
?>
<h1>Browse by Category</h1>

<div class="category-grid">
    <?php if (isset($categories) && !empty($categories)): ?>
        <?php foreach ($categories as $category): ?>
            <div class="category-section">
                <h2>
                    <a href="?page=category&id=<?php echo $category[\'id\']; ?>">
                        <?php echo htmlspecialchars($category[\'name\']); ?>
                    </a>
                    <span class="count">(<?php echo $category[\'listing_count\'] ?? 0; ?>)</span>
                </h2>
                
                <?php if (!empty($category[\'subcategories\'])): ?>
                    <div class="subcategories">
                        <?php 
                        $subLinks = [];
                        foreach ($category[\'subcategories\'] as $subcat) {
                            $subLinks[] = \'<a href="?page=category&id=\' . $subcat[\'id\'] . \'">\' . 
                                         htmlspecialchars($subcat[\'name\']) . \' (\' . ($subcat[\'listing_count\'] ?? 0) . \')</a>\';
                        }
                        echo implode(\', \', $subLinks);
                        ?>
                    </div>
                <?php else: ?>
                    <div class="subcategories" style="color: #999; font-style: italic;">
                        No subcategories yet
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>No categories found in the database.</p>
    <?php endif; ?>
</div>

<div class="stats-box">
    <p>
        <strong><?php echo $stats[\'total_listings\'] ?? 0; ?></strong> websites listed in 
        <strong><?php echo $stats[\'total_categories\'] ?? 0; ?></strong> categories | 
        <strong><?php echo $stats[\'total_reviews\'] ?? 0; ?></strong> reviews
    </p>
</div>
<?php
$content = ob_get_clean();
?>';

    file_put_contents($homeFile, $content);
    echo "<p style='color: green;'>✅ Created new home.php file</p>";
} else {
    echo "<p>home.php already exists with content</p>";
}

// Verify the file
if (file_exists($homeFile)) {
    $size = filesize($homeFile);
    echo "<p>File size: " . $size . " bytes</p>";
    
    if ($size > 0) {
        echo "<p style='color: green;'>✅ File has content!</p>";
        
        // Test including it
        ob_start();
        $categories = [['id' => 1, 'name' => 'Test', 'listing_count' => 5, 'subcategories' => []]];
        $stats = ['total_listings' => 1, 'total_categories' => 1, 'total_reviews' => 0];
        include $homeFile;
        $output = ob_get_clean();
        
        echo "<p>Output length: " . strlen($output) . " bytes</p>";
        echo "<div style='border: 2px solid green; padding: 10px;'>" . $output . "</div>";
    } else {
        echo "<p style='color: red;'>❌ File is still empty!</p>";
    }
} else {
    echo "<p style='color: red;'>❌ File was not created!</p>";
}
?>
