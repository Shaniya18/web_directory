<div class="form-container" style="max-width: 500px; margin: 50px auto;">
    <h1>Reset Your Password</h1>
    <p>Enter your email address and we'll send you a link to reset your password.</p>
    
    <?php echo displayMessages(); ?>
    
    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        
        <div class="form-group">
            <label>Email Address</label>
            <input type="email" name="email" required placeholder="Enter your email">
        </div>
        
        <button type="submit" class="btn">Send Reset Link</button>
    </form>

    <?php
    // Demo Mode Display - Check if demo data exists and display it
    if (isset($_SESSION['demo_reset_link']) && isset($_SESSION['demo_email'])) {
        $demo_email = $_SESSION['demo_email'] ?? '';
        $demo_username = $_SESSION['demo_username'] ?? 'User';
        $demo_timestamp = $_SESSION['demo_timestamp'] ?? date('Y-m-d H:i:s');
        $demo_reset_link = $_SESSION['demo_reset_link'] ?? '';
    ?>
    <div class="demo-email-container" style="background: #f0f8ff; padding: 15px; margin: 25px 0; border: 2px solid #0066cc; border-radius: 5px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <div style="text-align: center; margin-bottom: 10px;">
            <span style="background: #cc0000; color: white; padding: 5px 12px; border-radius: 4px; font-weight: bold; font-size: 14px;">DEMO MODE</span>
        </div>
        
        <div style="background: white; padding: 15px; border-radius: 4px; border: 1px solid #e0e0e0;">
            <h3 style="color: #0066cc; margin-top: 0; font-size: 16px; display: flex; align-items: center;">
                <span style="margin-right: 8px;">📧</span> Password Reset Email Simulation
            </h3>
            
            <div style="font-size: 13px; margin-bottom: 12px;">
                <strong>To:</strong> <?php echo htmlspecialchars($demo_email); ?><br>
                <strong>Time:</strong> <?php echo htmlspecialchars($demo_timestamp); ?>
            </div>
            
            <div style="padding: 12px; background: #f9f9f9; border-radius: 4px; margin: 12px 0; font-size: 13px;">
                <p>Hello <strong><?php echo htmlspecialchars($demo_username); ?></strong>,</p>
                <p style="margin-bottom: 12px;">You recently requested to reset your password for your Fiji Web Directory account.</p>
                
                <div style="text-align: center; margin: 15px 0;">
                    <a href="<?php echo htmlspecialchars($demo_reset_link); ?>" 
                       style="background: #0066cc; color: white; padding: 8px 20px; text-decoration: none; border-radius: 3px; display: inline-block; font-weight: bold; font-size: 14px; transition: background-color 0.3s;"
                       onmouseover="this.style.backgroundColor='#0052a3'" 
                       onmouseout="this.style.backgroundColor='#0066cc'">
                        Reset Your Password
                    </a>
                </div>
                
                <p style="margin: 10px 0 5px 0;">Or copy this link:</p>
                <div style="background: #f0f0f0; padding: 8px; border-radius: 3px; font-family: monospace; word-break: break-all; font-size: 12px; border: 1px solid #ddd;">
                    <?php echo htmlspecialchars($demo_reset_link); ?>
                </div>
            </div>
            
            <div style="margin-top: 10px; padding-top: 10px; border-top: 1px dashed #ccc; font-size: 12px; color: #666;">
                <em>This is a demo simulation. In production, this email would be sent via SMTP.</em>
            </div>
        </div>
    </div>
    <?php
        // Don't unset here - let it persist until the page is refreshed or reset is used
    }
    ?>
    
    <div style="text-align: center; margin-top: 20px;">
        <a href="?page=login" style="color: #0066cc; text-decoration: none;">← Back to Login</a>
    </div>
</div>