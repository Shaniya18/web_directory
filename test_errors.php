<?php
require_once 'config.php';
setSecurityHeaders();

// Test 1: Database error
try {
    $db = new mysqli('wrong_host', 'wrong_user', 'wrong_pass', 'wrong_db');
} catch (Exception $e) {
    // Should show generic message, not technical details
    echo "Generic error message shown to user";
}

// Test 2: PHP error  
undefined_function(); // This will cause error but not show details

echo "If you see this, errors are being handled properly!";
?>