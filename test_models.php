<?php
require_once 'config.php';
require_once 'includes/Database.php';
require_once 'models/User.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Model Test</h2>";

try {
    $db = Database::getInstance();
    echo "✅ Database instance created<br>";
    
    $userModel = new User($db);
    echo "✅ User model created with Database instance<br>";
    
    // Test a simple query
    $result = $userModel->findByEmail('test@test.com');
    echo "✅ Query executed successfully<br>";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}
?>