<h2>Pending Listings</h2>

<?php if (!empty($listings)): ?>
    <table style="width: 100%; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden;">
        <tr style="background: #2c3e50; color: white;">
            <th style="padding: 12px; text-align: left;">Title</th>
            <th style="padding: 12px; text-align: left;">URL</th>
            <th style="padding: 12px; text-align: left;">Submitted</th>
            <th style="padding: 12px; text-align: left;">Actions</th>
        </tr>
        
        <?php foreach ($listings as $row): ?>
            <tr style="border-bottom: 1px solid #eee;">
                <td style="padding: 12px;"><?php echo htmlspecialchars($row['title']); ?></td>
                <td style="padding: 12px;">
                    <a href="<?php echo htmlspecialchars($row['url']); ?>" target="_blank">
                        <?php echo htmlspecialchars(substr($row['url'], 0, 30)); ?>...
                    </a>
                </td>
                <td style="padding: 12px;"><?php echo date('M d, Y', strtotime($row['created_at'])); ?></td>
                <td style="padding: 12px;">
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <input type="hidden" name="action" value="approve_listing">
                        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                        <button type="submit" style="background: #27ae60; color: white; border: none; padding: 5px 10px; border-radius: 3px; cursor: pointer;">Approve</button>
                    </form>
                    
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <input type="hidden" name="action" value="delete_listing">
                        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                        <button type="submit" style="background: #e74c3c; color: white; border: none; padding: 5px 10px; border-radius: 3px; cursor: pointer;" onclick="return confirm('Delete this listing?')">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php else: ?>
    <div style="background: white; padding: 2rem; border-radius: 8px; text-align: center;">
        <p>No pending listings.</p>
    </div>
<?php endif; ?>