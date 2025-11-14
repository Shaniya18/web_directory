<h2>Contact Messages</h2>

<?php if (!empty($messages)): ?>
    <div style="background: white; border-radius: 8px; overflow: hidden;">
        <?php foreach ($messages as $row): ?>
            <div style="border-bottom: 1px solid #eee; padding: 1.5rem;">
                <div style="display: flex; justify-content: between; margin-bottom: 1rem;">
                    <div style="flex: 1;">
                        <strong>From:</strong> <?php echo htmlspecialchars($row['name']); ?><br>
                        <strong>Email:</strong> <?php echo htmlspecialchars($row['email']); ?><br>
                        <strong>Date:</strong> <?php echo date('M d, Y g:i A', strtotime($row['created_at'])); ?>
                    </div>
                    <div style="flex: 1;">
                        <strong>IP:</strong> <?php echo htmlspecialchars($row['ip_address'] ?? 'Unknown'); ?><br>
                        <strong>Status:</strong> 
                        <span style="
                            background: <?php echo $row['status'] === 'new' ? '#e74c3c' : ($row['status'] === 'read' ? '#3498db' : '#27ae60'); ?>;
                            color: white;
                            padding: 2px 8px;
                            border-radius: 12px;
                            font-size: 0.8rem;
                        ">
                            <?php echo ucfirst($row['status'] ?? 'new'); ?>
                        </span>
                    </div>
                </div>
                
                <div style="margin-bottom: 1rem;">
                    <strong>Subject:</strong> <?php echo htmlspecialchars($row['subject']); ?>
                </div>
                
                <div style="
                    background: #f8f9fa;
                    padding: 1rem;
                    border-radius: 4px;
                    border-left: 4px solid #3498db;
                ">
                    <strong>Message:</strong><br>
                    <?php echo nl2br(htmlspecialchars($row['message'])); ?>
                </div>
                
                <?php if (!empty($row['user_agent'])): ?>
                <div style="margin-top: 0.5rem; font-size: 0.8rem; color: #666;">
                    <strong>Browser:</strong> <?php echo htmlspecialchars(substr($row['user_agent'], 0, 100)); ?>
                </div>
                <?php endif; ?>
                
                <div style="margin-top: 1rem;">
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <input type="hidden" name="action" value="delete_message">
                        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                        <button type="submit" style="
                            background: #e74c3c;
                            color: white;
                            border: none;
                            padding: 5px 15px;
                            border-radius: 3px;
                            cursor: pointer;
                        " onclick="return confirm('Are you sure you want to delete this message?')">Delete Message</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <div style="background: white; padding: 2rem; border-radius: 8px; text-align: center;">
        <p>No contact messages received yet.</p>
    </div>
<?php endif; ?>