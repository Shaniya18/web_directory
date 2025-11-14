<h1>Login</h1>

<!-- REMOVE THE ADMIN TAB COMPLETELY -->
<div class="tabs">
    <div class="tab active" data-tab="user" onclick="switchTab('user')">Member Login</div>
    <!-- Admin tab removed -->
</div>

<!-- REMOVE ADMIN LOGIN FORM -->
<div id="user" class="tab-content active"> <!-- Changed to always active -->
    <div class="form-container">
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <input type="hidden" name="action" value="user_login">
            
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" required>
            </div>
            
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>
            
            <button type="submit" class="btn">Login</button>
        </form>
        
        <div style="text-align: center; margin-top: 20px;">
            <p>
                Don't have an account? <a href="?page=register">Register here</a>
            </p>
            <!-- NEW FORGOT PASSWORD LINK -->
            <p style="margin-top: 10px;">
                <a href="?page=forgot-password">Forgot your password?</a>
            </p>
        </div>
    </div>
</div>

<!-- REMOVE THE ADMIN TAB CONTENT COMPLETELY -->

<script>
// Simplify the JavaScript since only one tab
function switchTab(tabName) {
    // Only user tab exists now
    document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
    document.querySelector('[data-tab="' + tabName + '"]').classlist.add('active');
    document.getElementById(tabName).classList.add('active');
}
</script>