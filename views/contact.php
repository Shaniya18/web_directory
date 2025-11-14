<h1>Contact Us</h1>
<div style="text-align: center; margin-top: 20px;">

    <div>
        <h2>Send us a Message</h2>
        <div class="form-container">
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <input type="hidden" name="action" value="contact_submit">
                
                <div class="form-group">
                    <label>Your Name *</label>
                    <input type="text" name="name" required maxlength="100">
                </div>
                
                <div class="form-group">
                    <label>Your Email *</label>
                    <input type="email" name="email" required maxlength="255">
                </div>
                
                <div class="form-group">
                    <label>Subject *</label>
                    <input type="text" name="subject" required maxlength="255">
                </div>
                
                <div class="form-group">
                    <label>Message *</label>
                    <textarea name="message" required rows="6"></textarea>
                </div>
                
                <button type="submit" class="btn">Send Message</button>
            </form>
        </div>
    </div>
</div>