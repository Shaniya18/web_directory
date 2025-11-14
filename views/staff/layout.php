<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Panel - Fiji Web Directory</title>
    <style>
        * { 
            margin: 0; 
            padding: 0; 
            box-sizing: border-box; 
        }
        body { 
            font-family: Arial, sans-serif; 
            background: #f5f5f5;
            color: #333;
        }
        .staff-header {
            background: #2c3e50;
            color: white;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .staff-nav {
            background: #34495e;
            padding: 1rem 2rem;
            display: flex;
            gap: 1rem;
        }
        .staff-nav a {
            color: white;
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            transition: background 0.3s;
        }
        .staff-nav a:hover, .staff-nav a.active {
            background: #4a6378;
        }
        .staff-content {
            padding: 2rem;
            min-height: 80vh;
        }
        .logout-btn {
            background: #e74c3c;
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        .logout-btn:hover {
            background: #c0392b;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin: 2rem 0;
        }
        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-align: center;
        }
        .stat-card h3 {
            font-size: 2rem;
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }
    </style>
</head>
<body>
    <div class="staff-header">
        <h1>🏢 Staff Panel - Fiji Web Directory</h1>
        <a href="staff.php?page=logout" class="logout-btn">Logout</a>
    </div>
    
    <div class="staff-nav">
        <a href="staff.php?page=dashboard" class="<?php echo ($_GET['section'] ?? 'dashboard') === 'dashboard' ? 'active' : ''; ?>">Dashboard</a>
        <a href="staff.php?page=dashboard&section=listings" class="<?php echo ($_GET['section'] ?? '') === 'listings' ? 'active' : ''; ?>">Pending Listings</a>
        <a href="staff.php?page=dashboard&section=reviews" class="<?php echo ($_GET['section'] ?? '') === 'reviews' ? 'active' : ''; ?>">Pending Reviews</a>
        <a href="staff.php?page=dashboard&section=messages" class="<?php echo ($_GET['section'] ?? '') === 'messages' ? 'active' : ''; ?>">Messages</a>
    </div>
    
    <div class="staff-content">
        <?php 
        // Include the specific staff page
        if (isset($template) && file_exists(__DIR__ . '/' . $template . '.php')) {
            include __DIR__ . '/' . $template . '.php';
        } else {
            echo '<div class="error-message">Staff page not found: ' . ($template ?? 'unknown') . '</div>';
        }
        ?>
    </div>
</body>
</html>