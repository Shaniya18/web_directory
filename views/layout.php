<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Fiji Web Directory - Browse, review and discover Fijian websites and businesses">
    <title><?php echo $pageTitle ?? 'Fiji Web Directory'; ?></title>
    
    <!-- FIXED: Use relative paths -->
    <link rel="stylesheet" href="public/style.css">
    
    <style>
        .rating-stars { color: #ffa500; font-size: 18px; }
        .review-card { background: #f9f9f9; padding: 20px; margin: 15px 0; border-radius: 5px; border: 1px solid #e0e0e0; }
        .review-header { display: flex; justify-content: space-between; margin-bottom: 10px; }
        .review-author { font-weight: bold; color: #333; }
        .review-date { color: #999; font-size: 12px; }
        .user-avatar { width: 40px; height: 40px; border-radius: 50%; background: #0066cc; color: white; display: inline-flex; align-items: center; justify-content: center; font-weight: bold; margin-right: 10px; }
        .featured-badge { background: #ffa500; color: white; padding: 3px 8px; border-radius: 3px; font-size: 11px; font-weight: bold; }
        .tabs { display: flex; border-bottom: 2px solid #e0e0e0; margin-bottom: 20px; }
        .tab { padding: 10px 20px; cursor: pointer; border-bottom: 3px solid transparent; }
        .tab.active { border-bottom-color: #0066cc; font-weight: bold; color: #0066cc; }
        .tab-content { display: none; }
        .tab-content.active { display: block; }
        .admin-nav { background: #333; padding: 15px 0; margin-bottom: 20px; }
        .admin-nav a { color: white; margin-right: 20px; text-decoration: none; }
        .admin-nav a:hover { text-decoration: underline; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: #f0f0f0; padding: 20px; border-radius: 5px; text-align: center; }
        .stat-card h2 { margin: 0; font-size: 2em; color: #0066cc; }
     </style>
</head>
<body>
    <?php partial('header'); ?>
    
    <main>
        <div class="container">
            <?php echo displayMessages(); ?>
            <?php echo $content ?? '<p>Content not loaded</p>'; ?>
        </div>
    </main>
    
    <?php partial('footer'); ?>
    
    <!-- FIXED: Use relative paths -->
    <script src="public/script.js"></script>
</body>
</html>