<?php
echo "<h1>Checking Home.php Content</h1>";

$homeFile = __DIR__ . '/views/home.php';
if (file_exists($homeFile)) {
    $content = file_get_contents($homeFile);
    echo "<p>File size: " . strlen($content) . " bytes</p>";
    echo "<h3>File Content:</h3>";
    echo "<pre>" . htmlspecialchars($content) . "</pre>";
    
    // Check if it has PHP tags
    if (strpos($content, '<?php') === false) {
        echo "<p style='color: red;'>✗ NO PHP TAGS FOUND - file might be empty or corrupted</p>";
    }
} else {
    echo "<p style='color: red;'>File not found: $homeFile</p>";
}
?>