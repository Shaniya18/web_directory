<h1>Create Account</h1>
<p class="intro-text">Register to submit listings, write reviews, and manage your submissions.</p>

<div class="form-container">
    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        <input type="hidden" name="action" value="user_register">
        
        <div class="form-group">
            <label>Username *</label>
            <input type="text" name="username" required maxlength="50">
        </div>
        
        <div class="form-group">
            <label>Email *</label>
            <input type="email" name="email" required maxlength="255">
        </div>
        
        <div class="form-group">
            <label>Full Name</label>
            <input type="text" name="full_name" maxlength="100">
        </div>
        
        <div class="form-group">
            <label>Password *</label>
            <input type="password" name="password" required minlength="8">
            <small>Minimum 8 characters</small>
        </div>
        
        <button type="submit" class="btn">Register</button>
    </form>
    
    <p style="text-align: center; margin-top: 20px;">
        Already have an account? <a href="?page=login&tab=user">Login here</a>
    </p>
</div>