<?php
$password = "admin123";
$hash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';

echo "Password: " . $password . "<br>";
echo "Hash: " . $hash . "<br>";
echo "Password verify: " . (password_verify($password, $hash) ? 'SUCCESS' : 'FAILED') . "<br>";

// Test with your actual database
require_once 'config.php';
require_once 'includes/Database.php';

$db = Database::getInstance();
$conn = $db->getConnection();

$stmt = $conn->prepare("SELECT * FROM admin_users WHERE username = 'admin'");
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();

echo "<br>Database admin: " . ($admin ? 'FOUND' : 'NOT FOUND') . "<br>";
if ($admin) {
    echo "Database hash: " . $admin['password'] . "<br>";
    echo "Database verify: " . (password_verify($password, $admin['password']) ? 'SUCCESS' : 'FAILED') . "<br>";
}
?>