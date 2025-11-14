<div class="breadcrumbs">
    <a href="?page=home">Home</a> &gt; 
    <a href="?page=category&id=<?php echo $listing['category_id']; ?>">
        <?php echo sanitizeOutput($listing['category_name']); ?>
    </a> &gt; 
    <?php echo sanitizeOutput($listing['title']); ?>
</div>

<div style="background: white; padding: 30px; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
    <?php if ($listing['featured']): ?>
        <span class="featured-badge">FEATURED</span><br><br>
    <?php endif; ?>
    
    <h1><?php echo sanitizeOutput($listing['title']); ?></h1>
    
    <?php if ($listing['rating_count'] > 0): ?>
        <div class="rating-stars" style="font-size: 24px; margin: 10px 0;">
            <?php echo displayRatingStars(round($listing['rating_avg'])); ?>
            <span style="font-size: 18px; color: #333;"><?php echo number_format($listing['rating_avg'], 1); ?></span>
            <span style="font-size: 14px; color: #666;">(<?php echo $listing['rating_count']; ?> reviews)</span>
        </div>
    <?php endif; ?>
    
    <p style="color: #666; margin: 10px 0;">
        📍 <?php echo sanitizeOutput($listing['region']); ?> | 
        📂 <?php echo sanitizeOutput($listing['category_name']); ?> | 
        👁 <?php echo $listing['views']; ?> views
    </p>
    
    <hr style="margin: 20px 0;">
    
    <h2>About</h2>
    <p style="line-height: 1.8;"><?php echo nl2br(sanitizeOutput($listing['description'])); ?></p>
    
    <h2 style="margin-top: 30px;">Contact Information</h2>
    <p>
        <strong>Website:</strong> 
        <a href="<?php echo sanitizeOutput($listing['url']); ?>" target="_blank" rel="noopener noreferrer">
            <?php echo sanitizeOutput($listing['url']); ?>
        </a><br>
        
        <?php if (!empty($listing['contact_email'])): ?>
            <strong>Email:</strong> 
            <a href="mailto:<?php echo sanitizeOutput($listing['contact_email']); ?>">
                <?php echo sanitizeOutput($listing['contact_email']); ?>
            </a><br>
        <?php endif; ?>
        
        <?php if (!empty($listing['phone'])): ?>
            <strong>Phone:</strong> <?php echo sanitizeOutput($listing['phone']); ?><br>
        <?php endif; ?>
        
        <?php if (!empty($listing['address'])): ?>
            <strong>Address:</strong> <?php echo sanitizeOutput($listing['address']); ?><br>
        <?php endif; ?>
    </p>
    
    <?php if (!empty($listing['social_facebook']) || !empty($listing['social_twitter']) || !empty($listing['social_instagram'])): ?>
        <h2>Follow Us</h2>
        <div class="social-links">
            <?php if (!empty($listing['social_facebook'])): ?>
                <a href="<?php echo sanitizeOutput($listing['social_facebook']); ?>" target="_blank">📘 Facebook</a>
            <?php endif; ?>
            <?php if (!empty($listing['social_twitter'])): ?>
                <a href="<?php echo sanitizeOutput($listing['social_twitter']); ?>" target="_blank">🐦 Twitter</a>
            <?php endif; ?>
            <?php if (!empty($listing['social_instagram'])): ?>
                <a href="<?php echo sanitizeOutput($listing['social_instagram']); ?>" target="_blank">📷 Instagram</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<div style="margin-top: 30px;">
    <h2>Reviews (<?php echo $listing['rating_count']; ?>)</h2>
    
    <?php if (SessionManager::isLoggedIn()): ?>
        <div style="background: #f9f9f9; padding: 20px; border-radius: 5px; margin: 20px 0;">
            <h3>Write a Review</h3>
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <input type="hidden" name="action" value="submit_review">
                <input type="hidden" name="listing_id" value="<?php echo $listing['id']; ?>">
                <input type="hidden" id="rating-input" name="rating" value="5" required>
                
                <div class="form-group">
                    <label>Your Rating</label>
                    <div class="rating-stars" style="font-size: 30px; cursor: pointer;">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <span id="star-<?php echo $i; ?>" onclick="setRating(<?php echo $i; ?>)" style="color: #ffa500;">★</span>
                        <?php endfor; ?>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Review Title</label>
                    <input type="text" name="review_title" maxlength="255">
                </div>
                
                <div class="form-group">
                    <label>Your Review</label>
                    <textarea name="review_text" rows="4" required></textarea>
                </div>
                
                <button type="submit" class="btn">Submit Review</button>
            </form>
        </div>
    <?php else: ?>
        <div style="background: #f9f9f9; padding: 20px; border-radius: 5px; margin: 20px 0; text-align: center;">
            <p>You must be <a href="?page=login&tab=user">logged in</a> to submit a review.</p>
        </div>
    <?php endif; ?>
    
    <?php if (!empty($reviews)): ?>
        <?php foreach ($reviews as $review): ?>
            <div class="review-card">
                <div class="review-header">
                    <div>
                        <span class="user-avatar"><?php echo strtoupper(substr($review['username'] ?? 'U', 0, 1)); ?></span>
                        <span class="review-author"><?php echo sanitizeOutput($review['username'] ?? 'User'); ?></span>
                        <div class="rating-stars" style="display: inline-block; margin-left: 10px;">
                            <?php echo displayRatingStars($review['rating']); ?>
                        </div>
                    </div>
                    <span class="review-date"><?php echo formatDate($review['created_at']); ?></span>
                </div>
                
                <?php if (!empty($review['title'])): ?>
                    <h4 style="margin: 10px 0; color: #333;"><?php echo sanitizeOutput($review['title']); ?></h4>
                <?php endif; ?>
                
                <p style="color: #555; line-height: 1.6;"><?php echo nl2br(sanitizeOutput($review['review_text'])); ?></p>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p style="color: #999;">No reviews yet. Be the first to review!</p>
    <?php endif; ?>
</div>

<script>
function setRating(rating) {
    document.getElementById('rating-input').value = rating;
    updateStarDisplay(rating);
}

function updateStarDisplay(rating) {
    for (let i = 1; i <= 5; i++) {
        const star = document.getElementById('star-' + i);
        if (star) {
            star.innerHTML = i <= rating ? '★' : '☆';
            star.style.color = i <= rating ? '#ffa500' : '#ddd';
        }
    }
}

// Initialize stars
window.onload = function() {
    const ratingInput = document.getElementById('rating-input');
    if (ratingInput) {
        updateStarDisplay(parseInt(ratingInput.value));
    }
};
</script>
