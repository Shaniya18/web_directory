<?php
// ============================================
// FIJI WEB DIRECTORY - ENHANCED VERSION
// Full index.php file
// ============================================

error_reporting(0);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error.log');

// Session security
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.use_strict_mode', 1);
session_start();

// Regenerate session periodically
if (!isset($_SESSION['created'])) {
    $_SESSION['created'] = time();
} else if (time() - $_SESSION['created'] > 1800) {
    session_regenerate_id(true);
    $_SESSION['created'] = time();
}

// NOTE: 'config.php' must be present in the same directory
require_once 'config.php';

// CSRF Token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Database connection
$conn = null;
try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) throw new Exception("Connection failed");
    $conn->set_charset("utf8mb4");
} catch (Exception $e) {
    error_log($e->getMessage());
    die("Service temporarily unavailable.");
}

// ============================================
// HELPER FUNCTIONS (Placed before use)
// ============================================

function isAdmin() {
    return isset($_SESSION['admin']) && $_SESSION['admin'] === true;
}

function isLoggedIn() {
    return isset($_SESSION['user_id']) && $_SESSION['user_id'] > 0;
}

function getCurrentUser($conn) {
    if (!isLoggedIn()) return null;
    $user_id = intval($_SESSION['user_id']);
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function generateCaptcha() {
    $_SESSION['captcha_num1'] = rand(1, 10);
    $_SESSION['captcha_num2'] = rand(1, 10);
    return $_SESSION['captcha_num1'] . ' + ' . $_SESSION['captcha_num2'];
}

function sanitizeOutput($text) {
    // This function must be defined for XSS prevention
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

// ============================================
// FORM HANDLER FUNCTIONS
// ============================================

function handleAdminLogin($conn) {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    $stmt = $conn->prepare("SELECT id, username, password FROM admin_users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $admin = $result->fetch_assoc();
        if (password_verify($password, $admin['password'])) {
            session_regenerate_id(true);
            $_SESSION['admin'] = true;
            $_SESSION['admin_id'] = $admin['id'];
            header('Location: ?page=admin');
            exit;
        }
    }
    
    $_SESSION['error'] = 'Invalid credentials';
    header('Location: ?page=login');
    exit;
}

function handleUserLogin($conn) {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    $stmt = $conn->prepare("SELECT id, username, email, password, is_verified FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            if ($user['is_verified'] == 0) {
                $_SESSION['error'] = 'Please verify your email before logging in.';
                header('Location: ?page=login&tab=user');
                exit;
            }
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['success'] = 'Welcome back, ' . htmlspecialchars($user['username']) . '!';
            header('Location: ?page=profile');
            exit;
        }
    }
    
    $_SESSION['error'] = 'Invalid email or password';
    header('Location: ?page=login&tab=user');
    exit;
}

function handleUserRegister($conn) {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $full_name = trim($_POST['full_name'] ?? '');
    
    // Validation
    if (empty($username) || empty($email) || empty($password)) {
        $_SESSION['error'] = 'All fields are required';
        header('Location: ?page=register');
        exit;
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = 'Invalid email address';
        header('Location: ?page=register');
        exit;
    }
    
    if (strlen($password) < 8) {
        $_SESSION['error'] = 'Password must be at least 8 characters';
        header('Location: ?page=register');
        exit;
    }
    
    // Check if username/email exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        $_SESSION['error'] = 'Username or email already exists';
        header('Location: ?page=register');
        exit;
    }
    
    // Create user
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    $verification_token = bin2hex(random_bytes(32));
    
    // NOTE: is_verified is set to 1 for immediate login in this demo code.
    $stmt = $conn->prepare("INSERT INTO users (username, email, password, full_name, verification_token, is_verified) VALUES (?, ?, ?, ?, ?, 1)");
    $stmt->bind_param("sssss", $username, $email, $password_hash, $full_name, $verification_token);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = 'Registration successful! You can now login.';
        header('Location: ?page=login&tab=user');
        exit;
    } else {
        $_SESSION['error'] = 'Registration failed. Please try again.';
        header('Location: ?page=register');
        exit;
    }
}

function handleSubmitListing($conn) {
     // Only allow logged in users to submit listings
    if (!isLoggedIn()) {
        $_SESSION['error'] = 'You must be logged in to submit a website';
        header('Location: ?page=login&tab=user');
        exit;
    }
    
    // CAPTCHA validation
    $captcha_answer = intval($_POST['captcha_answer'] ?? 0);
    $expected = intval($_SESSION['captcha_num1'] ?? 0) + intval($_SESSION['captcha_num2'] ?? 0);
    
    if ($captcha_answer !== $expected) {
        $_SESSION['error'] = 'CAPTCHA verification failed';
        header('Location: ?page=submit');
        exit;
    }
    
    $title = trim($_POST['title'] ?? '');
    $url = trim($_POST['url'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $category_id = intval($_POST['category_id'] ?? 0);
    $region = trim($_POST['region'] ?? '');
    $contact_email = trim($_POST['contact_email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $tags = trim($_POST['tags'] ?? '');
    $user_id = intval($_SESSION['user_id']);
    
    if (empty($title) || empty($url) || empty($description) || $category_id <= 0) {
        $_SESSION['error'] = 'Please fill all required fields';
        header('Location: ?page=submit');
        exit;
    }
    
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        $_SESSION['error'] = 'Please enter a valid URL';
        header('Location: ?page=submit');
        exit;
    }
    
    $stmt = $conn->prepare("INSERT INTO listings (title, url, description, category_id, region, contact_email, phone, address, tags, user_id, approved) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0)");
    $stmt->bind_param("sssisssssi", $title, $url, $description, $category_id, $region, $contact_email, $phone, $address, $tags, $user_id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = 'Listing submitted successfully! It will be reviewed by our team.';
    } else {
        $_SESSION['error'] = 'An error occurred. Please try again.';
    }
    
    header('Location: ?page=submit');
    exit;
}

function handleSubmitReview($conn) {
    // Only allow logged in users to submit reviews
    if (!isLoggedIn()) {
        $_SESSION['error'] = 'You must be logged in to submit a review';
        header('Location: ?page=login&tab=user');
        exit;
    }
    
    $listing_id = intval($_POST['listing_id'] ?? 0);
    $rating = intval($_POST['rating'] ?? 0);
    $title = trim($_POST['review_title'] ?? '');
    $review_text = trim($_POST['review_text'] ?? '');
    
    if ($listing_id <= 0 || $rating < 1 || $rating > 5) {
        $_SESSION['error'] = 'Invalid review data';
        header('Location: ?page=listing&listing_id=' . $listing_id);
        exit;
    }
    
    // Only logged in users can reach here, so always use user_id
    $user_id = intval($_SESSION['user_id']);
    $stmt = $conn->prepare("INSERT INTO reviews (listing_id, user_id, rating, title, review_text, approved) VALUES (?, ?, ?, ?, ?, 0)");
    $stmt->bind_param("iiiss", $listing_id, $user_id, $rating, $title, $review_text);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = 'Review submitted! It will be published after approval.';
    } else {
        $_SESSION['error'] = 'Failed to submit review';
    }
    
    header('Location: ?page=listing&listing_id=' . $listing_id);
    exit;
}

function handleContactSubmit($conn) {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    
    // Validation
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $_SESSION['error'] = 'All fields are required';
        header('Location: ?page=contact');
        exit;
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = 'Invalid email address';
        header('Location: ?page=contact');
        exit;
    }
    
    $ip_address = $_SERVER['REMOTE_ADDR'];
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    
    $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, subject, message, ip_address, user_agent) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $name, $email, $subject, $message, $ip_address, $user_agent);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = 'Message sent successfully! We will get back to you soon.';
    } else {
        $_SESSION['error'] = 'Failed to send message. Please try again.';
    }
    
    header('Location: ?page=contact');
    exit;
}

function approveListing($conn, $id) {
    $stmt = $conn->prepare("UPDATE listings SET approved = 1 WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $_SESSION['success'] = 'Listing approved!';
    header('Location: ?page=admin');
    exit;
}

function deleteListing($conn, $id) {
    $stmt = $conn->prepare("DELETE FROM listings WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $_SESSION['success'] = 'Listing deleted!';
    header('Location: ?page=admin');
    exit;
}

function approveReview($conn, $id) {
    $stmt = $conn->prepare("UPDATE reviews SET approved = 1 WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    
    // Update listing rating
    $stmt = $conn->prepare("SELECT listing_id FROM reviews WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $listing_id = $stmt->get_result()->fetch_assoc()['listing_id'];
    
    // This assumes a stored procedure exists
    $conn->query("CALL UpdateListingRating($listing_id)"); 
    
    $_SESSION['success'] = 'Review approved!';
    header('Location: ?page=admin&section=reviews');
    exit;
}

function deleteReview($conn, $id) {
    $stmt = $conn->prepare("DELETE FROM reviews WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $_SESSION['success'] = 'Review deleted!';
    header('Location: ?page=admin&section=reviews');
    exit;
}

// ============================================
// PAGE DISPLAY FUNCTIONS
// ============================================

function displayListingDetail($conn, $listing_id) {
    // Increment view count - **SECURITY FIX: Using prepared statement here**
    $stmt_views = $conn->prepare("CALL IncrementListingViews(?)");
    if ($stmt_views) {
        $stmt_views->bind_param("i", $listing_id);
        @$stmt_views->execute(); // @ to suppress errors if the SP doesn't exist/fails
        $stmt_views->close();
    }
    
    // Get listing
    $stmt = $conn->prepare("SELECT l.*, c.name as category_name, u.username as owner_username FROM listings l LEFT JOIN categories c ON l.category_id = c.id LEFT JOIN users u ON l.user_id = u.id WHERE l.id = ? AND l.approved = 1");
    $stmt->bind_param("i", $listing_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 0) {
        echo '<p>Listing not found.</p>';
        return;
    }
    
    $listing = $result->fetch_assoc();
    
    echo '<div class="breadcrumbs">';
    echo '<a href="?page=home">Home</a> &gt; ';
    echo '<a href="?page=category&id=' . $listing['category_id'] . '">' . sanitizeOutput($listing['category_name']) . '</a> &gt; ';
    echo sanitizeOutput($listing['title']);
    echo '</div>';
    
    echo '<div style="background: white; padding: 30px; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">';
    
    if ($listing['featured']) {
        echo '<span class="featured-badge">FEATURED</span><br><br>';
    }
    
    echo '<h1>' . sanitizeOutput($listing['title']) . '</h1>';
    
    // Rating
    if ($listing['rating_count'] > 0) {
        echo '<div class="rating-stars" style="font-size: 24px; margin: 10px 0;">';
        $rating = round($listing['rating_avg']);
        for ($i = 1; $i <= 5; $i++) {
            echo $i <= $rating ? '★' : '☆';
        }
        echo ' <span style="font-size: 18px; color: #333;">' . number_format($listing['rating_avg'], 1) . '</span>';
        echo ' <span style="font-size: 14px; color: #666;">(' . $listing['rating_count'] . ' reviews)</span>';
        echo '</div>';
    }
    
    echo '<p style="color: #666; margin: 10px 0;">';
    echo '📍 ' . sanitizeOutput($listing['region']) . ' | ';
    echo '📂 ' . sanitizeOutput($listing['category_name']) . ' | ';
    echo '👁 ' . $listing['views'] . ' views';
    echo '</p>';
    
    echo '<hr style="margin: 20px 0;">';
    
    // Description
    echo '<h2>About</h2>';
    echo '<p style="line-height: 1.8;">' . nl2br(sanitizeOutput($listing['description'])) . '</p>';
    
    // Contact Info
    echo '<h2 style="margin-top: 30px;">Contact Information</h2>';
    echo '<p>';
    echo '<strong>Website:</strong> <a href="' . sanitizeOutput($listing['url']) . '" target="_blank" rel="noopener noreferrer">' . sanitizeOutput($listing['url']) . '</a><br>';
    if (!empty($listing['contact_email'])) {
        echo '<strong>Email:</strong> <a href="mailto:' . sanitizeOutput($listing['contact_email']) . '">' . sanitizeOutput($listing['contact_email']) . '</a><br>';
    }
    if (!empty($listing['phone'])) {
        echo '<strong>Phone:</strong> ' . sanitizeOutput($listing['phone']) . '<br>';
    }
    if (!empty($listing['address'])) {
        echo '<strong>Address:</strong> ' . sanitizeOutput($listing['address']) . '<br>';
    }
    echo '</p>';
    
    // Social Media
    if (!empty($listing['social_facebook']) || !empty($listing['social_twitter']) || !empty($listing['social_instagram'])) {
        echo '<h2>Follow Us</h2>';
        echo '<div class="social-links">';
        if (!empty($listing['social_facebook'])) {
            echo '<a href="' . sanitizeOutput($listing['social_facebook']) . '" target="_blank">📘 Facebook</a>';
        }
        if (!empty($listing['social_twitter'])) {
            echo '<a href="' . sanitizeOutput($listing['social_twitter']) . '" target="_blank">🐦 Twitter</a>';
        }
        if (!empty($listing['social_instagram'])) {
            echo '<a href="' . sanitizeOutput($listing['social_instagram']) . '" target="_blank">📷 Instagram</a>';
        }
        echo '</div>';
    }
    
    echo '</div>';
    
    // Reviews Section
    echo '<div style="margin-top: 30px;">';
    echo '<h2>Reviews (' . $listing['rating_count'] . ')</h2>';
    
    // Add Review Form - Only show if user is logged in
    if (isLoggedIn()) {
        echo '<div style="background: #f9f9f9; padding: 20px; border-radius: 5px; margin: 20px 0;">';
        echo '<h3>Write a Review</h3>';
        echo '<form method="POST">';
        echo '<input type="hidden" name="csrf_token" value="' . $_SESSION['csrf_token'] . '">';
        echo '<input type="hidden" name="action" value="submit_review">';
        echo '<input type="hidden" name="listing_id" value="' . $listing_id . '">';
        echo '<input type="hidden" id="rating-input" name="rating" value="5" required>';
        
        echo '<div class="form-group">';
        echo '<label>Your Rating</label>';
        echo '<div class="rating-stars" style="font-size: 30px; cursor: pointer;">';
        for ($i = 1; $i <= 5; $i++) {
            echo '<span id="star-' . $i . '" onclick="setRating(' . $i . ')" style="color: #ffa500;">★</span>';
        }
        echo '</div>';
        echo '</div>';
        
        echo '<div class="form-group"><label>Review Title</label><input type="text" name="review_title" maxlength="255"></div>';
        echo '<div class="form-group"><label>Your Review</label><textarea name="review_text" rows="4" required></textarea></div>';
        echo '<button type="submit" class="btn">Submit Review</button>';
        echo '</form>';
        echo '</div>';
    } else {
        echo '<div style="background: #f9f9f9; padding: 20px; border-radius: 5px; margin: 20px 0; text-align: center;">';
        echo '<p>You must be <a href="?page=login&tab=user">logged in</a> to submit a review.</p>';
        echo '</div>';
    }
    
    // Display Reviews
    $stmt = $conn->prepare("SELECT r.*, u.username FROM reviews r LEFT JOIN users u ON r.user_id = u.id WHERE r.listing_id = ? AND r.approved = 1 ORDER BY r.created_at DESC");
    $stmt->bind_param("i", $listing_id);
    $stmt->execute();
    $reviews = $stmt->get_result();
    
    if ($reviews->num_rows > 0) {
        while ($review = $reviews->fetch_assoc()) {
            echo '<div class="review-card">';
            echo '<div class="review-header">';
            echo '<div>';
            
            $display_name = !empty($review['username']) ? $review['username'] : 'User';
            echo '<div class="user-avatar">' . strtoupper(substr($display_name, 0, 1)) . '</div> ';
            echo '<span class="review-author">' . sanitizeOutput($display_name) . '</span>';
            
            echo '<div class="rating-stars" style="display: inline-block; margin-left: 10px;">';
            for ($i = 1; $i <= 5; $i++) {
                echo $i <= $review['rating'] ? '★' : '☆';
            }
            echo '</div>';
            echo '</div>';
            
            echo '<span class="review-date">' . date('M d, Y', strtotime($review['created_at'])) . '</span>';
            echo '</div>';
            
            if (!empty($review['title'])) {
                echo '<h4 style="margin: 10px 0; color: #333;">' . sanitizeOutput($review['title']) . '</h4>';
            }
            
            echo '<p style="color: #555; line-height: 1.6;">' . nl2br(sanitizeOutput($review['review_text'])) . '</p>';
            echo '</div>';
        }
    } else {
        echo '<p style="color: #999;">No reviews yet. Be the first to review!</p>';
    }
    
    echo '</div>';
}

function displaySearchResults($conn, $search, $page_num, $per_page, $offset) {
    echo '<h1>Search Results for "' . sanitizeOutput($search) . '"</h1>';
    
    if (empty($search)) {
        echo '<p>Please enter a search term.</p>';
        return;
    }
    
    $search_param = "%$search%";
    
    $stmt = $conn->prepare("SELECT COUNT(*) as cnt FROM listings WHERE approved = 1 AND (title LIKE ? OR description LIKE ? OR tags LIKE ?)");
    $stmt->bind_param("sss", $search_param, $search_param, $search_param);
    $stmt->execute();
    $total = $stmt->get_result()->fetch_assoc()['cnt'];
    
    $stmt = $conn->prepare("SELECT * FROM listings WHERE approved = 1 AND (title LIKE ? OR description LIKE ? OR tags LIKE ?) ORDER BY featured DESC, title LIMIT ? OFFSET ?");
    $stmt->bind_param("sssii", $search_param, $search_param, $search_param, $per_page, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        echo '<p class="result-count">Found ' . $total . ' result(s)</p>';
        while ($listing = $result->fetch_assoc()) {
            displayListingCard($listing);
        }
        displayPagination($total, $per_page, $page_num, "?page=search&search=" . urlencode($search));
    } else {
        echo '<p class="no-results">No results found. Try different keywords.</p>';
    }
}

function displaySubmitForm($conn) {
    // Check if user is logged in
    if (!isLoggedIn()) {
        echo '<div class="message message-error">You must be <a href="?page=login&tab=user">logged in</a> to submit a website</div>';
        return;
    }
    
    echo '<h1>Submit Your Website</h1>';
    echo '<p class="intro-text">Submit your website to be listed in the Fiji Web Directory. All submissions are reviewed before being published.</p>';
    
    echo '<div class="form-container">';
    echo '<form method="POST" action="">';
    echo '<input type="hidden" name="csrf_token" value="' . $_SESSION['csrf_token'] . '">';
    echo '<input type="hidden" name="action" value="submit_listing">';
    
    echo '<div class="form-group"><label>Website/Organization Name *</label><input type="text" name="title" required maxlength="255"></div>';
    echo '<div class="form-group"><label>Website URL *</label><input type="url" name="url" placeholder="https://example.com" required maxlength="500"></div>';
    echo '<div class="form-group"><label>Description *</label><textarea name="description" required placeholder="Brief description of your website" maxlength="1000"></textarea></div>';
    
    echo '<div class="form-group"><label>Category *</label><select name="category_id" required>';
    echo '<option value="">Select a category...</option>';
    
    $stmt = $conn->prepare("SELECT c1.id, c1.name, c2.name as parent_name FROM categories c1 LEFT JOIN categories c2 ON c1.parent_id = c2.id ORDER BY c2.name, c1.name");
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $label = $row['parent_name'] ? sanitizeOutput($row['parent_name']) . ' > ' . sanitizeOutput($row['name']) : sanitizeOutput($row['name']);
        echo '<option value="' . $row['id'] . '">' . $label . '</option>';
    }
    
    echo '</select></div>';
    
    echo '<div class="form-group"><label>Region/Location *</label><select name="region" required>';
    echo '<option value="">Select region...</option>';
    $regions = ['Suva', 'Lautoka', 'Nadi', 'Ba', 'Labasa', 'Sigatoka', 'Nausori', 'Levuka', 'Other'];
    foreach ($regions as $region) {
        echo '<option value="' . $region . '">' . $region . '</option>';
    }
    echo '</select></div>';
    
    echo '<div class="form-group"><label>Contact Email</label><input type="email" name="contact_email" maxlength="255"></div>';
    echo '<div class="form-group"><label>Phone</label><input type="text" name="phone" maxlength="50"></div>';
    echo '<div class="form-group"><label>Address</label><textarea name="address" rows="2"></textarea></div>';
    echo '<div class="form-group"><label>Tags/Keywords</label><input type="text" name="tags" placeholder="tourism, hotel, resort" maxlength="500"></div>';
    
    echo '<div class="form-group"><label>Security Check: What is ' . generateCaptcha() . '? *</label><input type="number" name="captcha_answer" required></div>';
    
    echo '<button type="submit" class="btn">Submit Listing</button>';
    echo '</form>';
    echo '</div>';
}

function displayLoginForm() {
    $tab = $_GET['tab'] ?? 'admin';
    
    echo '<h1>Login</h1>';
    
    echo '<div class="tabs">';
    echo '<div class="tab ' . ($tab == 'admin' ? 'active' : '') . '" data-tab="admin" onclick="switchTab(\'admin\')">Admin Login</div>';
    echo '<div class="tab ' . ($tab == 'user' ? 'active' : '') . '" data-tab="user" onclick="switchTab(\'user\')">User Login</div>';
    echo '</div>';
    
    // Admin Login
    echo '<div id="admin" class="tab-content ' . ($tab == 'admin' ? 'active' : '') . '">';
    echo '<div class="form-container">';
    echo '<form method="POST">';
    echo '<input type="hidden" name="csrf_token" value="' . $_SESSION['csrf_token'] . '">';
    echo '<input type="hidden" name="action" value="admin_login">';
    echo '<div class="form-group"><label>Username</label><input type="text" name="username" required></div>';
    echo '<div class="form-group"><label>Password</label><input type="password" name="password" required></div>';
    echo '<button type="submit" class="btn">Login as Admin</button>';
    echo '</form>';
    echo '</div>';
    echo '</div>';
    
    // User Login
    echo '<div id="user" class="tab-content ' . ($tab == 'user' ? 'active' : '') . '">';
    echo '<div class="form-container">';
    echo '<form method="POST">';
    echo '<input type="hidden" name="csrf_token" value="' . $_SESSION['csrf_token'] . '">';
    echo '<input type="hidden" name="action" value="user_login">';
    echo '<div class="form-group"><label>Email</label><input type="email" name="email" required></div>';
    echo '<div class="form-group"><label>Password</label><input type="password" name="password" required></div>';
    echo '<button type="submit" class="btn">Login</button>';
    echo '</form>';
    echo '<p style="text-align: center; margin-top: 20px;">Don\'t have an account? <a href="?page=register">Register here</a></p>';
    echo '</div>';
    echo '</div>';
}

function displayRegisterForm() {
    echo '<h1>Create Account</h1>';
    echo '<p class="intro-text">Register to submit listings, write reviews, and manage your submissions.</p>';
    
    echo '<div class="form-container">';
    echo '<form method="POST">';
    echo '<input type="hidden" name="csrf_token" value="' . $_SESSION['csrf_token'] . '">';
    echo '<input type="hidden" name="action" value="user_register">';
    
    echo '<div class="form-group"><label>Username *</label><input type="text" name="username" required maxlength="50"></div>';
    echo '<div class="form-group"><label>Email *</label><input type="email" name="email" required maxlength="255"></div>';
    echo '<div class="form-group"><label>Full Name</label><input type="text" name="full_name" maxlength="100"></div>';
    echo '<div class="form-group"><label>Password *</label><input type="password" name="password" required minlength="8"><small>Minimum 8 characters</small></div>';
    
    echo '<button type="submit" class="btn">Register</button>';
    echo '</form>';
    
    echo '<p style="text-align: center; margin-top: 20px;">Already have an account? <a href="?page=login&tab=user">Login here</a></p>';
    echo '</div>';
}

function displayUserProfile($conn) {
    $user = getCurrentUser($conn);
    
    echo '<h1>My Profile</h1>';
    
    echo '<div style="background: white; padding: 30px; border-radius: 5px; margin-bottom: 30px;">';
    echo '<h2>Account Information</h2>';
    echo '<p><strong>Username:</strong> ' . sanitizeOutput($user['username']) . '</p>';
    echo '<p><strong>Email:</strong> ' . sanitizeOutput($user['email']) . '</p>';
    echo '<p><strong>Full Name:</strong> ' . sanitizeOutput($user['full_name'] ?? 'Not set') . '</p>';
    echo '<p><strong>Member Since:</strong> ' . date('F Y', strtotime($user['created_at'])) . '</p>';
    echo '</div>';
    
    // User's listings
    $stmt = $conn->prepare("SELECT * FROM listings WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->bind_param("i", $user['id']);
    $stmt->execute();
    $listings = $stmt->get_result();
    
    echo '<h2>My Listings (' . $listings->num_rows . ')</h2>';
    
    if ($listings->num_rows > 0) {
        while ($listing = $listings->fetch_assoc()) {
            $status = $listing['approved'] ? '<span style="color: green;">✓ Approved</span>' : '<span style="color: orange;">⏳ Pending</span>';
            
            echo '<div class="listing">';
            echo '<h3><a href="?page=listing&listing_id=' . $listing['id'] . '">' . sanitizeOutput($listing['title']) . '</a> ' . $status . '</h3>';
            echo '<p>' . sanitizeOutput(substr($listing['description'], 0, 150)) . '...</p>';
            echo '<small>Submitted: ' . date('M d, Y', strtotime($listing['created_at'])) . '</small>';
            echo '</div>';
        }
    } else {
        echo '<p>You haven\'t submitted any listings yet. <a href="?page=submit">Submit one now</a></p>';
    }
    
    // User's reviews
    $stmt = $conn->prepare("SELECT r.*, l.title as listing_title FROM reviews r JOIN listings l ON r.listing_id = l.id WHERE r.user_id = ? ORDER BY r.created_at DESC LIMIT 10");
    $stmt->bind_param("i", $user['id']);
    $stmt->execute();
    $reviews = $stmt->get_result();
    
    echo '<h2 style="margin-top: 40px;">My Recent Reviews (' . $reviews->num_rows . ')</h2>';
    
    if ($reviews->num_rows > 0) {
        while ($review = $reviews->fetch_assoc()) {
            echo '<div class="comment-box">';
            echo '<strong>' . sanitizeOutput($review['listing_title']) . '</strong> ';
            echo '<span class="rating-stars">';
            for ($i = 1; $i <= 5; $i++) echo $i <= $review['rating'] ? '★' : '☆';
            echo '</span><br>';
            echo '<p>' . sanitizeOutput(substr($review['review_text'], 0, 100)) . '...</p>';
            echo '<small>' . date('M d, Y', strtotime($review['created_at'])) . '</small>';
            echo '</div>';
        }
    }
}

function displayContactPage($conn) {
    echo '<h1>Contact Us</h1>';
    
    echo '<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-bottom: 30px;">';
    
    // Contact Form
    echo '<div>';
    echo '<h2>Send us a Message</h2>';
    echo '<div class="form-container">';
    echo '<form method="POST">';
    echo '<input type="hidden" name="csrf_token" value="' . $_SESSION['csrf_token'] . '">';
    echo '<input type="hidden" name="action" value="contact_submit">';
    
    echo '<div class="form-group"><label>Your Name *</label><input type="text" name="name" required maxlength="100"></div>';
    echo '<div class="form-group"><label>Your Email *</label><input type="email" name="email" required maxlength="255"></div>';
    echo '<div class="form-group"><label>Subject *</label><input type="text" name="subject" required maxlength="255"></div>';
    echo '<div class="form-group"><label>Message *</label><textarea name="message" required rows="6"></textarea></div>';
    
    echo '<button type="submit" class="btn">Send Message</button>';
    echo '</form>';
    echo '</div>';
    echo '</div>';
    
    
}

function displayAboutPage() {
    echo '<h1>About Fiji Web Directory</h1>';
    
    echo '<div class="about-content">';
    echo '<p>The Fiji Web Directory is a comprehensive platform for discovering and connecting with businesses, organizations, and services across Fiji.</p>';
    
    echo '<h2 class="section-title">Our Mission</h2>';
    echo '<p>To promote national web visibility, digital engagement, and easy access to public, private, and non-profit service listings in Fiji.</p>';
    
    echo '<h2 class="section-title">Features</h2>';
    echo '<ul>';
    echo '<li>Browse thousands of Fijian websites by category</li>';
    echo '<li>Read and write reviews to help others make informed decisions</li>';
    echo '<li>Submit your own website for free</li>';
    echo '<li>Connect with businesses through direct contact information</li>';
    echo '<li>Discover featured and popular listings</li>';
    echo '</ul>';
    
    echo '<h2 class="section-title">How It Works</h2>';
    echo '<ol>';
    echo '<li><strong>Browse:</strong> Explore categories to find what you need</li>';
    echo '<li><strong>Review:</strong> Share your experiences to help others</li>';
    echo '<li><strong>Submit:</strong> Add your website to reach more people</li>';
    echo '<li><strong>Connect:</strong> Use provided contact details to get in touch</li>';
    echo '</ol>';
    
    echo '<h2 class="section-title">Contact Us</h2>';
    echo '<p>Have questions or suggestions? <a href="?page=contact">Get in touch with us</a>.</p>';
    echo '</div>';
}

function displayPagination($total, $per_page, $current_page, $base_url) {
    $total_pages = ceil($total / $per_page);
    
    if ($total_pages <= 1) return;
    
    echo '<div class="pagination">';
    
    if ($current_page > 1) {
        echo '<a href="' . $base_url . '&p=' . ($current_page - 1) . '">&laquo; Previous</a>';
    }
    
    for ($i = max(1, $current_page - 3); $i <= min($total_pages, $current_page + 3); $i++) {
        if ($i == $current_page) {
            echo '<span class="current">' . $i . '</span>';
        } else {
            echo '<a href="' . $base_url . '&p=' . $i . '">' . $i . '</a>';
        }
    }
    
    if ($current_page < $total_pages) {
        echo '<a href="' . $base_url . '&p=' . ($current_page + 1) . '">Next &raquo;</a>';
    }
    
    echo '</div>';
}

function displayAdminPanel($conn) {
    $section = $_GET['section'] ?? 'listings';
    
    echo '<div class="admin-nav"><div class="container">';
    echo '<a href="?page=admin&section=listings">Pending Listings</a>';
    echo '<a href="?page=admin&section=reviews">Pending Reviews</a>';
    echo '<a href="?page=admin&section=contact">Contact Messages</a>';
    echo '<a href="?page=admin&section=stats">Statistics</a>';
    echo '</div></div>';
    
    switch ($section) {
        case 'reviews':
            displayPendingReviews($conn);
            break;
        case 'contact':
            displayContactMessages($conn);
            break;
        case 'stats':
            displayStatistics($conn);
            break;
        default:
            displayPendingListings($conn);
    }
}

function displayPendingListings($conn) {
    echo '<h1>Pending Listings</h1>';
    
    $stmt = $conn->prepare("SELECT l.*, c.name as category_name FROM listings l LEFT JOIN categories c ON l.category_id = c.id WHERE l.approved = 0 ORDER BY l.id DESC");
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        echo '<table>';
        echo '<tr><th>Title</th><th>URL</th><th>Category</th><th>Submitted</th><th>Actions</th></tr>';
        
        while ($row = $result->fetch_assoc()) {
            echo '<tr>';
            echo '<td>' . sanitizeOutput($row['title']) . '</td>';
            echo '<td><a href="' . sanitizeOutput($row['url']) . '" target="_blank">' . sanitizeOutput(substr($row['url'], 0, 40)) . '...</a></td>';
            echo '<td>' . sanitizeOutput($row['category_name']) . '</td>';
            echo '<td>' . date('M d, Y', strtotime($row['created_at'])) . '</td>';
            echo '<td>';
            echo '<form method="POST" style="display: inline;"><input type="hidden" name="csrf_token" value="' . $_SESSION['csrf_token'] . '"><input type="hidden" name="action" value="approve_listing"><input type="hidden" name="listing_id" value="' . $row['id'] . '"><button type="submit" class="btn btn-small">Approve</button></form> ';
            echo '<form method="POST" style="display: inline;"><input type="hidden" name="csrf_token" value="' . $_SESSION['csrf_token'] . '"><input type="hidden" name="action" value="delete_listing"><input type="hidden" name="listing_id" value="' . $row['id'] . '"><button type="submit" class="btn btn-small btn-danger" onclick="return confirm(\'Delete?\')">Delete</button></form>';
            echo '</td></tr>';
        }
        echo '</table>';
    } else {
        echo '<p>No pending listings.</p>';
    }
}

function displayPendingReviews($conn) {
    echo '<h1>Pending Reviews</h1>';
    
    $stmt = $conn->prepare("SELECT r.*, l.title as listing_title FROM reviews r JOIN listings l ON r.listing_id = l.id WHERE r.approved = 0 ORDER BY r.id DESC");
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        echo '<table>';
        echo '<tr><th>Listing</th><th>Rating</th><th>Review</th><th>Submitted</th><th>Actions</th></tr>';
        
        while ($row = $result->fetch_assoc()) {
            echo '<tr>';
            echo '<td>' . sanitizeOutput($row['listing_title']) . '</td>';
            echo '<td>' . str_repeat('★', $row['rating']) . '</td>';
            echo '<td>' . sanitizeOutput(substr($row['review_text'], 0, 50)) . '...</td>';
            echo '<td>' . date('M d, Y', strtotime($row['created_at'])) . '</td>';
            echo '<td>';
            echo '<form method="POST" style="display: inline;"><input type="hidden" name="csrf_token" value="' . $_SESSION['csrf_token'] . '"><input type="hidden" name="action" value="approve_review"><input type="hidden" name="review_id" value="' . $row['id'] . '"><button type="submit" class="btn btn-small">Approve</button></form> ';
            echo '<form method="POST" style="display: inline;"><input type="hidden" name="csrf_token" value="' . $_SESSION['csrf_token'] . '"><input type="hidden" name="action" value="delete_review"><input type="hidden" name="review_id" value="' . $row['id'] . '"><button type="submit" class="btn btn-small btn-danger" onclick="return confirm(\'Delete?\')">Delete</button></form>';
            echo '</td></tr>';
        }
        echo '</table>';
    } else {
        echo '<p>No pending reviews.</p>';
    }
}

function displayContactMessages($conn) {
    echo '<h1>Contact Messages</h1>';
    
    $stmt = $conn->prepare("SELECT * FROM contact_messages ORDER BY created_at DESC LIMIT 50");
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        echo '<table>';
        echo '<tr><th>Name</th><th>Email</th><th>Subject</th><th>Date</th><th>Status</th></tr>';
        
        while ($row = $result->fetch_assoc()) {
            echo '<tr>';
            echo '<td>' . sanitizeOutput($row['name']) . '</td>';
            echo '<td>' . sanitizeOutput($row['email']) . '</td>';
            echo '<td>' . sanitizeOutput($row['subject']) . '</td>';
            echo '<td>' . date('M d, Y', strtotime($row['created_at'])) . '</td>';
            echo '<td>' . $row['status'] . '</td>';
            echo '</tr>';
        }
        echo '</table>';
    } else {
        echo '<p>No messages yet.</p>';
    }
}

function displayStatistics($conn) {
    echo '<h1>Directory Statistics</h1>';
    
    $total_listings = $conn->query("SELECT COUNT(*) as cnt FROM listings WHERE approved = 1")->fetch_assoc()['cnt'];
    $pending_listings = $conn->query("SELECT COUNT(*) as cnt FROM listings WHERE approved = 0")->fetch_assoc()['cnt'];
    $total_users = $conn->query("SELECT COUNT(*) as cnt FROM users")->fetch_assoc()['cnt'];
    $total_reviews = $conn->query("SELECT COUNT(*) as cnt FROM reviews WHERE approved = 1")->fetch_assoc()['cnt'];
    $total_views = $conn->query("SELECT SUM(views) as total FROM listings")->fetch_assoc()['total'] ?? 0;
    
    echo '<div class="stats-grid">';
    echo '<div class="stat-card"><h2>' . $total_listings . '</h2><p>Approved Listings</p></div>';
    echo '<div class="stat-card"><h2>' . $pending_listings . '</h2><p>Pending Listings</p></div>';
    echo '<div class="stat-card"><h2>' . $total_users . '</h2><p>Registered Users</p></div>';
    echo '<div class="stat-card"><h2>' . $total_reviews . '</h2><p>Total Reviews</p></div>';
    echo '<div class="stat-card"><h2>' . number_format($total_views) . '</h2><p>Total Views</p></div>';
    echo '</div>';
    
    echo '<h2 class="section-title">Top Rated Listings</h2>';
    $stmt = $conn->prepare("SELECT * FROM listings WHERE approved = 1 AND rating_count > 0 ORDER BY rating_avg DESC LIMIT 10");
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        echo '<table>';
        echo '<tr><th>Title</th><th>Category</th><th>Rating</th><th>Reviews</th><th>Views</th></tr>';
        
        while ($row = $result->fetch_assoc()) {
            echo '<tr>';
            echo '<td><a href="?page=listing&listing_id=' . $row['id'] . '">' . sanitizeOutput($row['title']) . '</a></td>';
            // NOTE: category_id is displayed instead of category name, which is okay for admin panel data overview.
            echo '<td>' . $row['category_id'] . '</td>';
            echo '<td>' . number_format($row['rating_avg'], 1) . ' ★</td>';
            echo '<td>' . $row['rating_count'] . '</td>';
            echo '<td>' . $row['views'] . '</td>';
            echo '</tr>';
        }
        echo '</table>';
    }
}

function displayHomepage($conn) {
    echo '<h1>Browse by Category</h1>';
    
    // Get ALL main categories (parent_id IS NULL or 0)
    $stmt = $conn->prepare("SELECT * FROM categories WHERE parent_id IS NULL OR parent_id = 0 ORDER BY name");
    $stmt->execute();
    $main_categories = $stmt->get_result();
    
    if ($main_categories->num_rows === 0) {
        echo '<div class="message message-error">No categories found in database.</div>';
        return;
    }
    
    echo '<div class="category-grid">';
    
    // Collect all main categories
    $categories = [];
    while ($row = $main_categories->fetch_assoc()) {
        $categories[] = $row;
    }
    
    // Split into two columns
    $half = ceil(count($categories) / 2);
    
    for ($col = 0; $col < 2; $col++) {
        echo '<div>';
        $start = $col * $half;
        $end = min($start + $half, count($categories));
        
        for ($i = $start; $i < $end; $i++) {
            $cat = $categories[$i];
            $icon = !empty($cat['icon']) ? $cat['icon'] . ' ' : '';
            
            // Get total listings count for this main category and all its subcategories
            $stmt_total = $conn->prepare("
                SELECT COUNT(DISTINCT l.id) as total 
                FROM listings l 
                WHERE l.approved = 1 
                AND (l.category_id = ? OR l.category_id IN (SELECT id FROM categories WHERE parent_id = ?))
            ");
            $stmt_total->bind_param("ii", $cat['id'], $cat['id']);
            $stmt_total->execute();
            $total_count = $stmt_total->get_result()->fetch_assoc()['total'];
            
            echo '<div class="category-section">';
            echo '<h2>' . $icon . '<a href="?page=category&id=' . $cat['id'] . '">' . sanitizeOutput($cat['name']) . ' (' . $total_count . ')</a></h2>';
            
            // Get ALL subcategories for this parent category
            $stmt_sub = $conn->prepare("SELECT id, name FROM categories WHERE parent_id = ? ORDER BY name");
            $stmt_sub->bind_param("i", $cat['id']);
            $stmt_sub->execute();
            $subcategories = $stmt_sub->get_result();
            
            if ($subcategories->num_rows > 0) {
                echo '<div class="subcategories">';
                $subs = [];
                
                // Get ALL subcategories with listing counts
                while ($subcat = $subcategories->fetch_assoc()) {
                    // Get listing count for each subcategory
                    $stmt_count = $conn->prepare("SELECT COUNT(*) as cnt FROM listings WHERE category_id = ? AND approved = 1");
                    $stmt_count->bind_param("i", $subcat['id']);
                    $stmt_count->execute();
                    $count = $stmt_count->get_result()->fetch_assoc()['cnt'];
                    
                    // Add subcategory with count in brackets
                    $sub_link = '<a href="?page=category&id=' . $subcat['id'] . '">' . sanitizeOutput($subcat['name']) . ' (' . $count . ')</a>';
                    $subs[] = $sub_link;
                }
                
                // Display all subcategories separated by commas
                echo implode(', ', $subs);
                echo '</div>';
            } else {
                // Show message if no subcategories exist
                echo '<div class="subcategories" style="color: #999; font-style: italic;">No subcategories yet</div>';
            }
            
            echo '</div>';
        }
        echo '</div>';
    }
    
    echo '</div>';
    
    // Statistics section
    $stmt = $conn->prepare("SELECT COUNT(*) as cnt FROM listings WHERE approved = 1");
    $stmt->execute();
    $total_listings = $stmt->get_result()->fetch_assoc()['cnt'];
    
    // Count only MAIN categories (parent_id IS NULL or 0)
    $stmt = $conn->prepare("SELECT COUNT(*) as cnt FROM categories WHERE parent_id IS NULL OR parent_id = 0");
    $stmt->execute();
    $total_categories = $stmt->get_result()->fetch_assoc()['cnt'];
    
    $stmt = $conn->prepare("SELECT COUNT(*) as cnt FROM reviews WHERE approved = 1");
    $stmt->execute();
    $total_reviews = $stmt->get_result()->fetch_assoc()['cnt'];
    
    echo '<div class="stats-box">';
    echo '<p><strong>' . $total_listings . '</strong> websites listed in <strong>' . $total_categories . '</strong> categories | <strong>' . $total_reviews . '</strong> reviews</p>';
    echo '</div>';
}

function displayCategory($conn, $category_id, $page_num, $per_page, $offset) {
    $stmt = $conn->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->bind_param("i", $category_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 0) {
        echo '<p>Category not found.</p>';
        return;
    }
    $category = $result->fetch_assoc();
    
    // Breadcrumbs
    $breadcrumbs = [];
    $current_id = $category_id;
    while ($current_id) {
        $stmt_nav = $conn->prepare("SELECT * FROM categories WHERE id = ?");
        $stmt_nav->bind_param("i", $current_id);
        $stmt_nav->execute();
        $cat_result = $stmt_nav->get_result();
        
        if ($cat_result->num_rows > 0) {
            $cat = $cat_result->fetch_assoc();
            array_unshift($breadcrumbs, '<a href="?page=category&id=' . $cat['id'] . '">' . sanitizeOutput($cat['name']) . '</a>');
            $current_id = $cat['parent_id'];
        } else {
            break;
        }
    }
    
    echo '<div class="breadcrumbs">';
    echo '<a href="?page=home">Home</a> &gt; ' . implode(' &gt; ', $breadcrumbs);
    echo '</div>';
    
    echo '<h1>' . sanitizeOutput($category['name']) . '</h1>';
    if (!empty($category['description'])) {
        echo '<p class="intro-text">' . sanitizeOutput($category['description']) . '</p>';
    }
    
    // Subcategories
    $stmt = $conn->prepare("SELECT * FROM categories WHERE parent_id = ? ORDER BY name");
    $stmt->bind_param("i", $category_id);
    $stmt->execute();
    $subresult = $stmt->get_result();
    
    if ($subresult->num_rows > 0) {
        echo '<h2 class="section-title">Subcategories</h2>';
        echo '<div class="category-grid">';
        
        while ($subcat = $subresult->fetch_assoc()) {
            $stmt_count = $conn->prepare("SELECT COUNT(*) as cnt FROM listings WHERE category_id = ? AND approved = 1");
            $stmt_count->bind_param("i", $subcat['id']);
            $stmt_count->execute();
            $count = $stmt_count->get_result()->fetch_assoc()['cnt'];
            
            echo '<div class="category-section">';
            echo '<h2><a href="?page=category&id=' . $subcat['id'] . '">' . sanitizeOutput($subcat['name']) . '</a> <span class="count">(' . $count . ')</span></h2>';
            echo '</div>';
        }
        
        echo '</div>';
    }
    
    // Listings
    $stmt = $conn->prepare("SELECT COUNT(*) as cnt FROM listings WHERE category_id = ? AND approved = 1");
    $stmt->bind_param("i", $category_id);
    $stmt->execute();
    $total_listings = $stmt->get_result()->fetch_assoc()['cnt'];
    
    $stmt = $conn->prepare("SELECT * FROM listings WHERE category_id = ? AND approved = 1 ORDER BY featured DESC, title LIMIT ? OFFSET ?");
    $stmt->bind_param("iii", $category_id, $per_page, $offset);
    $stmt->execute();
    $listings_result = $stmt->get_result();
    
    if ($listings_result->num_rows > 0) {
        echo '<h2 class="section-title">Listings (' . $total_listings . ')</h2>';
        
        while ($listing = $listings_result->fetch_assoc()) {
            displayListingCard($listing);
        }
        
        displayPagination($total_listings, $per_page, $page_num, "?page=category&id=$category_id");
    } elseif ($subresult->num_rows == 0) {
        echo '<p class="no-results">No listings in this category yet.</p>';
    }
}

function displayListingCard($listing) {
    echo '<div class="listing">';
    
    if ($listing['featured']) {
        echo '<span class="featured-badge">FEATURED</span> ';
    }
    
    echo '<h3><a href="?page=listing&listing_id=' . $listing['id'] . '">' . sanitizeOutput($listing['title']) . '</a></h3>';
    
    if ($listing['rating_count'] > 0) {
        echo '<div class="rating-stars">';
        $rating = round($listing['rating_avg']);
        for ($i = 1; $i <= 5; $i++) {
            echo $i <= $rating ? '★' : '☆';
        }
        echo ' ' . number_format($listing['rating_avg'], 1) . ' (' . $listing['rating_count'] . ' reviews)';
        echo '</div>';
    }
    
    echo '<div class="listing-meta">📍 ' . sanitizeOutput($listing['region']);
    if ($listing['views'] > 0) {
        echo ' | 👁 ' . $listing['views'] . ' views';
    }
    echo '</div>';
    
    echo '<div class="listing-description">' . nl2br(sanitizeOutput(substr($listing['description'], 0, 200))) . '...</div>';
    echo '<div class="listing-url"><a href="' . sanitizeOutput($listing['url']) . '" target="_blank" rel="noopener noreferrer">' . sanitizeOutput($listing['url']) . '</a></div>';
    echo '</div>';
}

// ============================================
// MAIN APPLICATION LOGIC
// ============================================

// Get page parameters
$allowed_pages = ['home', 'category', 'listing', 'search', 'submit', 'login', 'logout', 
                  'register', 'profile', 'contact', 'admin', 'about', 'reviews'];
$page = isset($_GET['page']) && in_array($_GET['page'], $allowed_pages) ? $_GET['page'] : 'home';
$category_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$listing_id = isset($_GET['listing_id']) ? intval($_GET['listing_id']) : 0;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Pagination
$page_num = isset($_GET['p']) ? max(1, intval($_GET['p'])) : 1;
$per_page = 20;
$offset = ($page_num - 1) * $per_page;

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['error'] = 'Security validation failed.';
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '?page=home'));
        exit;
    }
    
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'submit_listing':
            handleSubmitListing($conn);
            break;
        case 'admin_login':
            handleAdminLogin($conn);
            break;
        case 'user_login':
            handleUserLogin($conn);
            break;
        case 'user_register':
            handleUserRegister($conn);
            break;
        case 'submit_review':
            handleSubmitReview($conn);
            break;
        case 'contact_submit':
            handleContactSubmit($conn);
            break;
        case 'approve_listing':
            if (isAdmin()) approveListing($conn, intval($_POST['listing_id']));
            break;
        case 'delete_listing':
            if (isAdmin()) deleteListing($conn, intval($_POST['listing_id']));
            break;
        case 'approve_review':
            if (isAdmin()) approveReview($conn, intval($_POST['review_id']));
            break;
        case 'delete_review':
            if (isAdmin()) deleteReview($conn, intval($_POST['review_id']));
            break;
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Fiji Web Directory - Browse, review and discover Fijian websites and businesses">
    <title>Fiji Web Directory - Your Gateway to Fijian Websites</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Additional styles for new features */
        /* NOTE: You should ideally move all these styles to style.css */
        .rating-stars { color: #ffa500; font-size: 18px; }
        .rating-stars .empty { color: #ddd; }
        .review-card { background: #f9f9f9; padding: 20px; margin: 15px 0; border-radius: 5px; border: 1px solid #e0e0e0; }
        .review-header { display: flex; justify-content: space-between; margin-bottom: 10px; }
        .review-author { font-weight: bold; color: #333; }
        .review-date { color: #999; font-size: 12px; }
        .comment-box { background: #fff; padding: 15px; margin: 10px 0; border-left: 3px solid #0066cc; }
        .user-avatar { width: 40px; height: 40px; border-radius: 50%; background: #0066cc; color: white; display: inline-flex; align-items: center; justify-content: center; font-weight: bold; }
        .featured-badge { background: #ffa500; color: white; padding: 3px 8px; border-radius: 3px; font-size: 11px; font-weight: bold; }
        .social-links a { margin: 0 10px; color: #0066cc; text-decoration: none; }
        .social-links a:hover { color: #0052a3; }
        .tabs { display: flex; border-bottom: 2px solid #e0e0e0; margin-bottom: 20px; }
        .tab { padding: 10px 20px; cursor: pointer; border-bottom: 3px solid transparent; }
        .tab.active { border-bottom-color: #0066cc; font-weight: bold; color: #0066cc; }
        .tab-content { display: none; }
        .tab-content.active { display: block; }
        .admin-nav a { margin-right: 15px; text-decoration: none; color: #333; }
        .admin-nav a:hover { color: #0066cc; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: #f0f0f0; padding: 20px; border-radius: 5px; text-align: center; }
        .stat-card h2 { margin: 0; font-size: 2em; color: #0066cc; }
        .stat-card p { margin: 0; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        table th, table td { padding: 12px; border: 1px solid #ddd; text-align: left; }
        table th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <div class="logo">
                <h1>FIJI Web Directory</h1>
                <p>Browse, Review & Discover</p>
            </div>
            
            <div class="search-bar">
                <form method="GET" action="">
                    <input type="hidden" name="page" value="search">
                    <input type="text" name="search" placeholder="Search..." value="<?php echo sanitizeOutput($search); ?>">
                    <button type="submit">Search</button>
                </form>
            </div>
            
            <nav>
                <a href="?page=home">Home</a>
                <a href="?page=submit">Submit Website</a>
                <a href="?page=contact">Contact</a>
                <a href="?page=about">About</a>
                <?php if (isLoggedIn()): ?>
                    <a href="?page=profile">My Profile</a>
                    <a href="?page=logout">Logout</a>
                <?php elseif (isAdmin()): ?>
                    <a href="?page=admin">Admin Panel</a>
                    <a href="?page=logout">Logout</a>
                <?php else: ?>
                    <a href="?page=login">Login</a>
                    <a href="?page=register">Register</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>
    
    <main>
        <div class="container">
            <?php
            // Display messages
            if (isset($_SESSION['success'])) {
                echo '<div class="message message-success">' . sanitizeOutput($_SESSION['success']) . '</div>';
                unset($_SESSION['success']);
            }
            if (isset($_SESSION['error'])) {
                echo '<div class="message message-error">' . sanitizeOutput($_SESSION['error']) . '</div>';
                unset($_SESSION['error']);
            }
            
            // Page routing
            switch ($page) {
                case 'home':
                    displayHomepage($conn);
                    break;
                case 'category':
                    displayCategory($conn, $category_id, $page_num, $per_page, $offset);
                    break;
                case 'listing':
                    displayListingDetail($conn, $listing_id);
                    break;
                case 'search':
                    displaySearchResults($conn, $search, $page_num, $per_page, $offset);
                    break;
                case 'submit':
                    displaySubmitForm($conn);
                    break;
                case 'login':
                    displayLoginForm();
                    break;
                case 'register':
                    displayRegisterForm();
                    break;
                case 'profile':
                    if (isLoggedIn()) {
                        displayUserProfile($conn);
                    } else {
                        header('Location: ?page=login');
                        exit;
                    }
                    break;
                case 'contact':
                    displayContactPage($conn);
                    break;
                case 'logout':
                    session_destroy();
                    header('Location: ?page=home');
                    exit;
                case 'admin':
                    if (isAdmin()) {
                        displayAdminPanel($conn);
                    } else {
                        header('Location: ?page=login');
                        exit;
                    }
                    break;
                case 'about':
                    displayAboutPage();
                    break;
                default:
                    displayHomepage($conn);
            }
            ?>
        </div>
    </main>
    
    <footer>
        <div class="container">
            <div class="social-links" style="text-align: center; margin-bottom: 15px;">
                <a href="#" target="_blank">📘 Facebook</a>
                <a href="#" target="_blank">🐦 Twitter</a>
                <a href="#" target="_blank">📷 Instagram</a>
            </div>
            <p>&copy; <?php echo date('Y'); ?> Fiji Web Directory. All rights reserved.</p>
            <p>Promoting digital engagement and web visibility across Fiji.</p>
        </div>
    </footer>
    
    <script>
        // Tab switching
        function switchTab(tabName) {
            document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
            document.querySelector('[data-tab="' + tabName + '"]').classList.add('active');
            document.getElementById(tabName).classList.add('active');
        }
        
        // Star rating selection
        function setRating(rating) {
            document.getElementById('rating-input').value = rating;
            updateStarDisplay(rating);
        }
        
        function updateStarDisplay(rating) {
            for (let i = 1; i <= 5; i++) {
                const star = document.getElementById('star-' + i);
                if (star) {
                    star.innerHTML = i <= rating ? '★' : '☆';
                    star.style.color = i <= rating ? '#ffa500' : '#ddd';
                }
            }
        }
        
        // Initialize star display on page load if rating-input has a value
        window.onload = function() {
            const ratingInput = document.getElementById('rating-input');
            if (ratingInput) {
                updateStarDisplay(parseInt(ratingInput.value));
            }
        };
    </script>
</body>
</html>

<?php
// Close database connection at the end of the script
$conn->close();
?>
