<div class="breadcrumbs">
    <a href="?page=home">Home</a> &gt; 
    <?php foreach ($breadcrumbs as $index => $crumb): ?>
        <?php if ($index < count($breadcrumbs) - 1): ?>
            <a href="<?php echo $crumb['url']; ?>"><?php echo sanitizeOutput($crumb['name']); ?></a> &gt; 
        <?php else: ?>
            <?php echo sanitizeOutput($crumb['name']); ?>
        <?php endif; ?>
    <?php endforeach; ?>
</div>

<h1><?php echo sanitizeOutput($category['name']); ?></h1>

<?php if (!empty($category['description'])): ?>
    <p class="intro-text"><?php echo sanitizeOutput($category['description']); ?></p>
<?php endif; ?>

<?php if (!empty($subcategories)): ?>
    <h2 class="section-title">Subcategories</h2>
    <div class="category-grid">
        <?php foreach ($subcategories as $subcat): ?>
            <div class="category-section">
                <h2>
                    <a href="?page=category&id=<?php echo $subcat['id']; ?>">
                        <?php echo sanitizeOutput($subcat['name']); ?>
                    </a>
                    <span class="count">(<?php echo $subcat['listing_count']; ?>)</span>
                </h2>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php if ($totalListings > 0): ?>
    <h2 class="section-title">Listings (<?php echo $totalListings; ?>)</h2>
    
    <?php foreach ($listings as $listing): ?>
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
                <?php if ($listing['views'] > 0): ?>
                    | 👁 <?php echo $listing['views']; ?> views
                <?php endif; ?>
            </div>
            
            <div class="listing-description">
                <?php echo nl2br(sanitizeOutput(substr($listing['description'], 0, 200))); ?>...
            </div>
            
            <div class="listing-url">
                <a href="<?php echo sanitizeOutput($listing['url']); ?>" target="_blank" rel="noopener noreferrer">
                    <?php echo sanitizeOutput($listing['url']); ?>
                </a>
            </div>
        </div>
    <?php endforeach; ?>
    
    <?php displayPagination($totalListings, $perPage, $currentPage, "?page=category&id=" . $category['id']); ?>
    
<?php elseif (empty($subcategories)): ?>
    <p class="no-results">No listings in this category yet.</p>
<?php endif; ?>
