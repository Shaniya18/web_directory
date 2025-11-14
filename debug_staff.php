<?php
// debug_staff.php - Complete staff login debug
require_once 'config.php';
require_once 'includes/Database.php';
require_once 'includes/SessionManager.php';
require_once 'models/User.php';

SessionManager::init();

echo "<h2>Staff Login Debug</h2>";

// Reset rate limiting
$_SESSION['staff_login_attempts'] = 0;
echo "<p>Rate limiting reset</p>";

// Test database connection
try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    echo "<p>✅ Database connected successfully</p>";
} catch (Exception $e) {
    echo "<p>❌ Database connection failed: " . $e->getMessage() . "</p>";
    exit;
}

// Test User model
$userModel = new User($db);

// Test 1: Check if we can find admin user
echo "<h3>Test 1: Find Admin User</h3>";
$admin = $userModel->getAdminByUsername('admin');
if ($admin) {
    echo "<p>✅ Admin user found:</p>";
    echo "<pre>Username: " . $admin['username'] . "</pre>";
    echo "<pre>Password Hash: " . $admin['password'] . "</pre>";
    echo "<pre>Created: " . $admin['created_at'] . "</pre>";
} else {
    echo "<p>❌ Admin user NOT found in database</p>";
    exit;
}

// Test 2: Test password verification with different passwords
echo "<h3>Test 2: Password Verification</h3>";
$test_passwords = [
    'admin123',
    'password123', 
    'simple123',
    '123456',
    'admin'
];

foreach ($test_passwords as $test_pwd) {
    $result = password_verify($test_pwd, $admin['password']);
    echo "<p>Password '$test_pwd': " . ($result ? '✅ WORKS' : '❌ FAILS') . "</p>";
}

// Test 3: Generate new hash and test it
echo "<h3>Test 3: Generate New Hash</h3>";
$new_password = "fiji2024";
$new_hash = password_hash($new_password, PASSWORD_DEFAULT);
echo "<p>New password: " . $new_password . "</p>";
echo "<p>New hash: " . $new_hash . "</p>";
echo "<p>Verify new hash: " . (password_verify($new_password, $new_hash) ? '✅ SUCCESS' : '❌ FAILED') . "</p>";

// Test 4: Update database with new password
echo "<h3>Test 4: Update Database</h3>";
$update_result = $db->execute(
    "UPDATE admin_users SET password = ? WHERE username = 'admin'",
    [$new_hash],
    "s"
);

if ($update_result) {
    echo "<p>✅ Database updated successfully</p>";
    
    // Verify the update worked
    $updated_admin = $userModel->getAdminByUsername('admin');
    $verify_result = password_verify($new_password, $updated_admin['password']);
    echo "<p>Verify updated password: " . ($verify_result ? '✅ SUCCESS' : '❌ FAILED') . "</p>";
    
    if ($verify_result) {
        echo "<h3 style='color: green;'>🎉 SUCCESS! Use these credentials:</h3>";
        echo "<p><strong>Staff ID:</strong> admin</p>";
        echo "<p><strong>Access Code:</strong> fiji2024</p>";
    }
} else {
    echo "<p>❌ Database update failed</p>";
}

// Test 5: Check session and CSRF
echo "<h3>Test 5: Session & CSRF</h3>";
echo "<p>CSRF Token: " . ($_SESSION['csrf_token'] ?? 'NOT SET') . "</p>";
echo "<p>Session ID: " . session_id() . "</p>";

echo "<hr><h3>Next Steps:</h3>";
echo "<p>1. Try logging in with the credentials above</p>";
echo "<p>2. If it works, the issue was the password hash</p>";
echo "<p>3. If it still fails, check your error logs</p>";
?>