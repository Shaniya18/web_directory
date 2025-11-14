<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Controller Constructor Check</h2>";

$controllers = [
    'UserController.php',
    'HomeController.php',
    'AdminController.php',
    'CategoryController.php', 
    'ListingController.php',
    'SearchController.php',
    'ContactController.php'
];

foreach ($controllers as $controller) {
    $path = __DIR__ . '/controllers/' . $controller;
    echo "<h3>Checking: $controller</h3>";
    
    if (!file_exists($path)) {
        echo "❌ File not found<br>";
        continue;
    }
    
    $content = file_get_contents($path);
    
    // Check for getConnection() in model instantiation
    if (preg_match('/new\s+\\\?\\w+\(\s*\\$this->db->getConnection\(\)\s*\)/', $content)) {
        echo "❌ CONTAINS getConnection() - NEEDS FIXING!<br>";
        
        // Show the problematic line
        preg_match('/new\s+\\\?\\w+\(\s*\\$this->db->getConnection\(\)\s*\)/', $content, $matches);
        echo "Problematic line: " . $matches[0] . "<br>";
    } else {
        echo "✅ No getConnection() found<br>";
    }
    
    // Check for $this->conn usage
    if (strpos($content, '$this->conn') !== false) {
        echo "❌ Contains \$this->conn<br>";
    }
}
?>