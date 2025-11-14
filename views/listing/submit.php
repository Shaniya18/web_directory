<h1>Submit Your Website</h1>
<p class="intro-text">Submit your website to be listed in the Fiji Web Directory. All submissions are reviewed before being published.</p>

<div class="form-container">
    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        <input type="hidden" name="action" value="submit_listing">
        
        <div class="form-group">
            <label>Website/Organization Name *</label>
            <input type="text" name="title" required maxlength="255">
        </div>
        
        <div class="form-group">
            <label>Website URL *</label>
            <input type="url" name="url" placeholder="https://example.com" required maxlength="500">
        </div>
        
        <!-- ✅ UPDATED: Website Images/Screenshots -->
        <div class="form-group">
            <label>Website Images/Screenshots (Optional)</label>
            <small class="form-text text-muted" style="display: block; margin-bottom: 10px;">
                Upload images to showcase your website. Users can browse these without visiting the site.
                Supported formats: JPEG, PNG, GIF, WebP. Maximum size: 5MB per image.
            </small>
            
            <!-- Main Screenshot -->
            <div style="margin-bottom: 15px;">
                <label style="font-weight: normal;">Main Screenshot *</label>
                <input type="file" name="main_image" accept="image/jpeg, image/png, image/gif, image/webp" required>
                <small style="display: block; color: #666;">Primary image that represents your website</small>
            </div>
            
            <!-- Additional Images -->
            <div style="margin-bottom: 10px;">
                <label style="font-weight: normal;">Additional Image 1</label>
                <input type="file" name="image_1" accept="image/jpeg, image/png, image/gif, image/webp">
            </div>
            
            <div style="margin-bottom: 10px;">
                <label style="font-weight: normal;">Additional Image 2</label>
                <input type="file" name="image_2" accept="image/jpeg, image/png, image/gif, image/webp">
            </div>
            
            <div style="margin-bottom: 10px;">
                <label style="font-weight: normal;">Additional Image 3</label>
                <input type="file" name="image_3" accept="image/jpeg, image/png, image/gif, image/webp">
            </div>
        </div>
        
        <div class="form-group">
            <label>Description *</label>
            <textarea name="description" required placeholder="Brief description of your website" maxlength="1000"></textarea>
        </div>
        
        <div class="form-group">
            <label>Category *</label>
            <select name="category_id" required>
                <option value="">Select a category...</option>
                <?php foreach ($categories as $cat): ?>
                    <?php 
                    $label = $cat['parent_name'] ? 
                        sanitizeOutput($cat['parent_name']) . ' > ' . sanitizeOutput($cat['name']) : 
                        sanitizeOutput($cat['name']);
                    ?>
                    <option value="<?php echo $cat['id']; ?>"><?php echo $label; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="form-group">
            <label>Region/Location *</label>
            <select name="region" required>
                <option value="">Select region...</option>
                <?php 
                $regions = ['Suva', 'Lautoka', 'Nadi', 'Ba', 'Labasa', 'Sigatoka', 'Nausori', 'Levuka', 'Other'];
                foreach ($regions as $region): ?>
                    <option value="<?php echo $region; ?>"><?php echo $region; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="form-group">
            <label>Contact Email</label>
            <input type="email" name="contact_email" maxlength="255">
        </div>
        
        <div class="form-group">
            <label>Phone</label>
            <input type="text" name="phone" maxlength="50">
        </div>
        
        <div class="form-group">
            <label>Address</label>
            <textarea name="address" rows="2"></textarea>
        </div>
        
        <div class="form-group">
            <label>Tags/Keywords</label>
            <input type="text" name="tags" placeholder="tourism, hotel, resort" maxlength="500">
        </div>
        
        <div class="form-group">
            <label>Security Check: What is <?php echo generateCaptcha(); ?>? *</label>
            <input type="number" name="captcha_answer" required>
        </div>
        
        <button type="submit" class="btn">Submit Listing</button>
    </form>
</div>