<header>
    <div class="container">
        <div class="logo">
            <h1>FIJI Web Directory</h1>
            <p>Browse, Review & Discover</p>
        </div>
        
        <div class="search-bar">
            <form method="GET" action="">
                <input type="hidden" name="page" value="search">
                <input type="text" name="search" placeholder="Search websites..." value="<?php echo sanitizeOutput($_GET['search'] ?? ''); ?>">
                <button type="submit">Search</button>
            </form>
        </div>
        
        <nav>
            <a href="?page=home">Home</a>
            <a href="?page=submit">Submit Website</a>
            <a href="?page=contact">Contact</a>
            <a href="?page=about">About</a>
            <?php if (SessionManager::isLoggedIn()): ?>
                <a href="?page=profile">My Profile</a>
                <a href="?page=logout">Logout (<?php echo sanitizeOutput(SessionManager::getUsername()); ?>)</a>
            <?php else: ?>
                <a href="?page=login">Login</a>
                <a href="?page=register">Register</a>
            <?php endif; ?>
        </nav>
    </div>
</header>