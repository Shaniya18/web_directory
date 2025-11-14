<h1>Search Results <?php echo !empty($searchTerm) ? 'for "' . sanitizeOutput($searchTerm) . '"' : ''; ?></h1>

<?php if (empty($searchTerm)): ?>
    <div class="message message-error">
        <p>Please enter a search term to find listings.</p>
    </div>
<?php elseif ($totalResults > 0): ?>
    <p class="result-count"><?php echo $message; ?></p>
    
    <?php foreach ($results as $listing): ?>
        <div class="listing">
            <?php if ($listing['featured']): ?>
                <span class="featured-badge">FEATURED</span>
            <?php endif; ?>
            
            <h3>
                <a href="?page=listing&listing_id=<?php echo $listing['id']; ?>">
                    <?php echo sanitizeOutput($listing['title']); ?>
                </a>
            </h3>
            
            <?php if ($listing['rating_count'] > 0): ?>
                <div class="rating-stars">
                    <?php echo displayRatingStars(round($listing['rating_avg'])); ?>
                    <?php echo number_format($listing['rating_avg'], 1); ?> (<?php echo $listing['rating_count']; ?> reviews)
                </div>
            <?php endif; ?>
            
            <div class="listing-meta">
                📍 <?php echo sanitizeOutput($listing['region']); ?> 
                | 📂 <?php echo sanitizeOutput($listing['category_name']); ?>
                <?php if ($listing['views'] > 0): ?>
                    | 👁 <?php echo $listing['views']; ?> views
                <?php endif; ?>
            </div>
            
            <div class="listing-description">
                <?php 
                $description = sanitizeOutput($listing['description']);
                // Highlight search term in description
                if (!empty($searchTerm)) {
                    $description = preg_replace("/(" . preg_quote($searchTerm, '/') . ")/i", '<mark>$1</mark>', $description);
                }
                echo nl2br(substr($description, 0, 200)); 
                if (strlen($listing['description']) > 200) echo '...';
                ?>
            </div>
            
            <div class="listing-url">
                <a href="<?php echo sanitizeOutput($listing['url']); ?>" target="_blank" rel="noopener noreferrer">
                    🔗 <?php echo sanitizeOutput($listing['url']); ?>
                </a>
            </div>
        </div>
    <?php endforeach; ?>
    
    <?php displayPagination($totalResults, $perPage, $currentPage, "?page=search&search=" . urlencode($searchTerm)); ?>
    
<?php else: ?>
    <div class="no-results">
        <p><?php echo $message; ?></p>
        <p>Suggestions:</p>
        <ul>
            <li>Check your spelling</li>
            <li>Try more general keywords</li>
            <li>Browse by <a href="?page=home">category</a> instead</li>
        </ul>
    </div>
<?php endif; ?>

