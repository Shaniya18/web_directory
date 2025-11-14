<div class="form-container" style="max-width: 500px; margin: 50px auto;">
    <h1>Create New Password</h1>
    <p>Enter your new password for <strong><?php echo sanitizeOutput($email); ?></strong></p>
    
    <?php echo displayMessages(); ?>
    
    <form method="POST" action="?page=reset-password&token=<?php echo sanitizeOutput($token); ?>">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        
        <div class="form-group">
            <label>New Password</label>
            <input type="password" name="password" required minlength="8" placeholder="Enter new password">
            <small>Minimum 8 characters</small>
        </div>
        
        <div class="form-group">
            <label>Confirm New Password</label>
            <input type="password" name="confirm_password" required placeholder="Confirm new password">
        </div>
        
        <button type="submit" class="btn">Reset Password</button>
    </form>
    
    <div style="text-align: center; margin-top: 20px;">
        <a href="?page=login">← Back to Login</a>
    </div>
</div>