<h1>My Profile</h1>

<div style="background: white; padding: 30px; border-radius: 5px; margin-bottom: 30px;">
    <h2>Account Information</h2>
    <p><strong>Username:</strong> <?php echo sanitizeOutput($user['username']); ?></p>
    <p><strong>Email:</strong> <?php echo sanitizeOutput($user['email']); ?></p>
    <p><strong>Full Name:</strong> <?php echo sanitizeOutput($user['full_name'] ?? 'Not set'); ?></p>
    <p><strong>Member Since:</strong> <?php echo date('F Y', strtotime($user['created_at'])); ?></p>
</div>

<h2>My Listings (<?php echo count($listings); ?>)</h2>

<?php if (!empty($listings)): ?>
    <?php foreach ($listings as $listing): ?>
        <?php $status = $listing['approved'] ? '<span style="color: green;">✓ Approved</span>' : '<span style="color: orange;">⏳ Pending</span>'; ?>
        
        <div class="listing">
            <h3>
                <a href="?page=listing&listing_id=<?php echo $listing['id']; ?>">
                    <?php echo sanitizeOutput($listing['title']); ?>
                </a> 
                <?php echo $status; ?>
            </h3>
            <p><?php echo sanitizeOutput(substr($listing['description'], 0, 150)); ?>...</p>
            <small>Submitted: <?php echo formatDate($listing['created_at']); ?></small>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <p>You haven't submitted any listings yet. <a href="?page=submit">Submit one now</a></p>
<?php endif; ?>

<?php if (!empty($reviews)): ?>
    <h2 style="margin-top: 40px;">My Recent Reviews (<?php echo count($reviews); ?>)</h2>
    
    <?php foreach ($reviews as $review): ?>
        <div class="comment-box">
            <strong><?php echo sanitizeOutput($review['listing_title']); ?></strong> 
            <span class="rating-stars"><?php echo displayRatingStars($review['rating']); ?></span><br>
            <p><?php echo sanitizeOutput(substr($review['review_text'], 0, 100)); ?>...</p>
            <small><?php echo formatDate($review['created_at']); ?></small>
        </div>
    <?php endforeach; ?>
<?php endif; ?>
