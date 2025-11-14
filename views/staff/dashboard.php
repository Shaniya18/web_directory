<h2>Staff Dashboard</h2>

<!-- Download Button -->
<div style="text-align: right; margin-bottom: 1rem;">
    <a href="staff.php?page=dashboard&download=csv" 
       style="background: #27ae60; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px; display: inline-block; font-weight: bold;">
        📊 Download CSV Report
    </a>
</div>

<!-- Statistics Cards -->
<div class="stats-grid">
    <div class="stat-card">
        <h3><?php echo $stats['total_listings'] ?? 0; ?></h3>
        <p>Total Listings</p>
    </div>
    <div class="stat-card">
        <h3><?php echo $stats['pending_listings'] ?? 0; ?></h3>
        <p>Pending Listings</p>
    </div>
    <div class="stat-card">
        <h3><?php echo $stats['total_users'] ?? 0; ?></h3>
        <p>Registered Users</p>
    </div>
    <div class="stat-card">
        <h3><?php echo $stats['pending_reviews'] ?? 0; ?></h3>
        <p>Pending Reviews</p>
    </div>
    <div class="stat-card">
        <h3><?php echo $stats['total_messages'] ?? 0; ?></h3>
        <p>Contact Messages</p>
    </div>
</div>

<!-- Top Rated Listings -->
<div style="background: white; padding: 2rem; border-radius: 8px; margin-top: 2rem;">
    <h3 style="margin-bottom: 1rem;">Top Rated Listings</h3>
    
    <?php if (!empty($topListings)): ?>
        <table style="width: 100%; border-collapse: collapse;">
            <tr style="background: #2c3e50; color: white;">
                <th style="padding: 12px; text-align: left;">Title</th>
                <th style="padding: 12px; text-align: left;">Rating</th>
                <th style="padding: 12px; text-align: left;">Reviews</th>
                <th style="padding: 12px; text-align: left;">Views</th>
            </tr>
            
            <?php foreach ($topListings as $row): ?>
                <tr style="border-bottom: 1px solid #eee;">
                    <td style="padding: 12px;">
                        <a href="?page=listing&listing_id=<?php echo $row['id']; ?>" style="color: #3498db; text-decoration: none;">
                            <?php echo htmlspecialchars($row['title']); ?>
                        </a>
                    </td>
                    <td style="padding: 12px;">
                        <?php echo number_format($row['rating_avg'], 1); ?> ★
                    </td>
                    <td style="padding: 12px;"><?php echo $row['rating_count']; ?></td>
                    <td style="padding: 12px;"><?php echo $row['views']; ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p style="text-align: center; color: #666; padding: 2rem;">No rated listings yet.</p>
    <?php endif; ?>
</div>

<!-- Quick Actions -->
<div style="background: white; padding: 2rem; border-radius: 8px; margin-top: 2rem;">
    <h3>Quick Actions</h3>
    <div style="display: flex; gap: 1rem; margin-top: 1rem; flex-wrap: wrap;">
        <a href="staff.php?page=dashboard&section=listings" style="background: #3498db; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px; display: inline-block;">
            📝 Review Listings (<?php echo $stats['pending_listings'] ?? 0; ?>)
        </a>
        <a href="staff.php?page=dashboard&section=reviews" style="background: #9b59b6; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px; display: inline-block;">
            ⭐ Moderate Reviews (<?php echo $stats['pending_reviews'] ?? 0; ?>)
        </a>
        <a href="staff.php?page=dashboard&section=messages" style="background: #e67e22; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px; display: inline-block;">
            📧 View Messages (<?php echo $stats['total_messages'] ?? 0; ?>)
        </a>
    </div>
</div>