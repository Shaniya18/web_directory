<?php
echo "<h1>Checking PHP Syntax in Home.php</h1>";

$homeFile = __DIR__ . '/views/home.php';

// Check syntax using PHP lint
$output = shell_exec('php -l ' . escapeshellarg($homeFile) . ' 2>&1');
echo "<p>Syntax check: " . htmlspecialchars($output) . "</p>";

// Also check the exact content
$content = file_get_contents($homeFile);
echo "<h3>File Content (first 500 chars):</h3>";
echo "<pre>" . htmlspecialchars(substr($content, 0, 500)) . "</pre>";

// Test if there are any PHP opening tags
if (strpos($content, '<?php') === false) {
    echo "<p style='color: red;'>❌ NO PHP OPENING TAG FOUND!</p>";
} else {
    echo "<p style='color: green;'>✅ PHP opening tag found</p>";
}

// Test if there are any PHP closing tags  
if (strpos($content, '?>') === false) {
    echo "<p style='color: red;'>❌ NO PHP CLOSING TAG FOUND!</p>";
} else {
    echo "<p style='color: green;'>✅ PHP closing tag found</p>";
}

// Check for common issues
echo "<h3>Checking for Common Issues:</h3>";

// Check for extra opening PHP tags
$phpOpenCount = substr_count($content, '<?php');
if ($phpOpenCount > 1) {
    echo "<p style='color: red;'>❌ MULTIPLE PHP OPENING TAGS FOUND: " . $phpOpenCount . "</p>";
} else {
    echo "<p style='color: green;'>✅ Correct number of PHP opening tags: " . $phpOpenCount . "</p>";
}

// Check for short open tags
if (strpos($content, '<?=') !== false) {
    echo "<p style='color: red;'>❌ SHORT OPEN TAGS FOUND (<?=) - these might not work</p>";
}

// Check for the critical components
$checks = [
    'ob_start()' => strpos($content, 'ob_start()') !== false,
    'ob_get_clean()' => strpos($content, 'ob_get_clean()') !== false,
    '$content = ' => strpos($content, '$content = ') !== false
];

foreach ($checks as $check => $result) {
    echo "<p>" . ($result ? "✅" : "❌") . " $check</p>";
}

// Test including the file directly with error reporting
echo "<h3>Testing File Inclusion:</h3>";
error_reporting(E_ALL);
ini_set('display_errors', 1);

ob_start();
$categories = [['id' => 1, 'name' => 'Test', 'listing_count' => 5, 'subcategories' => []]];
$stats = ['total_listings' => 1, 'total_categories' => 1, 'total_reviews' => 0];

try {
    include $homeFile;
    $output = ob_get_clean();
    echo "<p style='color: green;'>✅ File included successfully</p>";
    echo "<p>Output length: " . strlen($output) . " bytes</p>";
    
    if (strlen($output) > 0) {
        echo "<div style='border: 2px solid green; padding: 10px; background: #f0fff0;'>";
        echo $output;
        echo "</div>";
    } else {
        echo "<p style='color: red;'>❌ File included but output is STILL 0 bytes!</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Exception during include: " . $e->getMessage() . "</p>";
}
?>