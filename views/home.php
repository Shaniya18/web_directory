<h1>Browse by Category</h1>

<div class="category-grid">
    <?php
    if (isset($categories) && is_array($categories) && count($categories) > 0) {
        foreach ($categories as $category) {
            echo '<div class="category-section">';
            echo '<h2>';
            echo '<a href="?page=category&id=' . $category['id'] . '">';
            echo htmlspecialchars($category['name']);
            echo '</a>';
            echo '<span class="count">(' . ($category['listing_count'] ?? 0) . ')</span>';
            echo '</h2>';
            
            if (!empty($category['subcategories']) && is_array($category['subcategories'])) {
                echo '<div class="subcategories">';
                $subLinks = [];
                foreach ($category['subcategories'] as $subcat) {
                    $subLinks[] = '<a href="?page=category&id=' . $subcat['id'] . '">' . 
                                 htmlspecialchars($subcat['name']) . ' (' . ($subcat['listing_count'] ?? 0) . ')</a>';
                }
                echo implode(', ', $subLinks);
                echo '</div>';
            } else {
                echo '<div class="subcategories" style="color: #999; font-style: italic;">No subcategories yet</div>';
            }
            echo '</div>';
        }
    } else {
        echo '<p>No categories found in the database.</p>';
    }
    ?>
</div>

<div class="stats-box">
    <p>
        <strong><?php echo $stats['total_listings'] ?? 0; ?></strong> websites listed in 
        <strong><?php echo $stats['total_categories'] ?? 0; ?></strong> categories | 
        <strong><?php echo $stats['total_reviews'] ?? 0; ?></strong> reviews
    </p>
</div>