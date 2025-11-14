<h2>Pending Reviews</h2>

<?php if (!empty($reviews)): ?>
    <table style="width: 100%; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden;">
        <tr style="background: #2c3e50; color: white;">
            <th style="padding: 12px; text-align: left;">Listing</th>
            <th style="padding: 12px; text-align: left;">Rating</th>
            <th style="padding: 12px; text-align: left;">Review</th>
            <th style="padding: 12px; text-align: left;">Actions</th>
        </tr>
        
        <?php foreach ($reviews as $row): ?>
            <tr style="border-bottom: 1px solid #eee;">
                <td style="padding: 12px;"><?php echo htmlspecialchars($row['listing_title']); ?></td>
                <td style="padding: 12px;">
                    <?php 
                    for ($i = 1; $i <= 5; $i++) {
                        echo $i <= $row['rating'] ? '★' : '☆';
                    }
                    ?>
                </td>
                <td style="padding: 12px;"><?php echo htmlspecialchars(substr($row['review_text'], 0, 50)); ?>...</td>
                <td style="padding: 12px;">
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <input type="hidden" name="action" value="approve_review">
                        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                        <button type="submit" style="background: #27ae60; color: white; border: none; padding: 5px 10px; border-radius: 3px; cursor: pointer;">Approve</button>
                    </form>
                    
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <input type="hidden" name="action" value="delete_review">
                        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                        <button type="submit" style="background: #e74c3c; color: white; border: none; padding: 5px 10px; border-radius: 3px; cursor: pointer;" onclick="return confirm('Delete this review?')">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php else: ?>
    <div style="background: white; padding: 2rem; border-radius: 8px; text-align: center;">
        <p>No pending reviews.</p>
    </div>
<?php endif; ?>