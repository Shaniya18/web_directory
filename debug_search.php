<?php
require_once 'config.php';
require_once 'Database.php';

echo "<h1>🔍 Search Function Debug</h1>";

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    echo "<h2>Database Connection: ✅ Connected</h2>";
    
    // Test 1: Check what's actually in the database
    echo "<h3>1. Checking Actual Database Content</h3>";
    
    $result = $conn->query("SELECT id, title, description, tags FROM listings WHERE approved = 1");
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>Title</th><th>Description</th><th>Tags</th></tr>";
    
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$row['id']}</td>";
        echo "<td><strong>{$row['title']}</strong></td>";
        echo "<td>" . htmlspecialchars(substr($row['description'], 0, 100)) . "...</td>";
        echo "<td style='background: #f0f8ff;'><code>" . htmlspecialchars($row['tags']) . "</code></td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Test 2: Test different search approaches
    echo "<h3>2. Testing Search Approaches</h3>";
    
    $searchTerms = ['education', 'university', 'fiji', 'government', 'travel'];
    
    foreach ($searchTerms as $term) {
        echo "<h4>Searching for: '<span style='color: blue;'>$term</span>'</h4>";
        
        // Approach 1: Direct SQL search
        $searchPattern = "%$term%";
        $stmt = $conn->prepare("
            SELECT COUNT(*) as cnt 
            FROM listings 
            WHERE approved = 1 
            AND (title LIKE ? OR description LIKE ? OR tags LIKE ? OR region LIKE ?)
        ");
        $stmt->bind_param("ssss", $searchPattern, $searchPattern, $searchPattern, $searchPattern);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        echo "<p>Direct SQL search: <strong>{$row['cnt']}</strong> results</p>";
        
        // Approach 2: Test individual fields
        echo "<p>Field-by-field breakdown:</p>";
        echo "<ul>";
        
        $fields = ['title', 'description', 'tags', 'region'];
        foreach ($fields as $field) {
            $stmt = $conn->prepare("SELECT COUNT(*) as cnt FROM listings WHERE approved = 1 AND $field LIKE ?");
            $stmt->bind_param("s", $searchPattern);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            echo "<li>$field: {$row['cnt']} matches</li>";
        }
        echo "</ul>";
        
        // Show actual matches
        $stmt = $conn->prepare("
            SELECT id, title, 
                   CASE 
                     WHEN title LIKE ? THEN 'title' 
                     WHEN description LIKE ? THEN 'description'
                     WHEN tags LIKE ? THEN 'tags'
                     WHEN region LIKE ? THEN 'region'
                   END as matched_field
            FROM listings 
            WHERE approved = 1 
            AND (title LIKE ? OR description LIKE ? OR tags LIKE ? OR region LIKE ?)
        ");
        $stmt->bind_param("ssssssss", $searchPattern, $searchPattern, $searchPattern, $searchPattern, 
                         $searchPattern, $searchPattern, $searchPattern, $searchPattern);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            echo "<p>Matching listings:</p>";
            echo "<ul>";
            while ($row = $result->fetch_assoc()) {
                echo "<li><strong>{$row['title']}</strong> (matched in: {$row['matched_field']})</li>";
            }
            echo "</ul>";
        } else {
            echo "<p style='color: red;'>❌ No matches found</p>";
        }
        
        echo "<hr>";
    }
    
    // Test 3: Check your Listing model's search method
    echo "<h3>3. Testing Your Listing Model Search</h3>";
    
    require_once 'Listing.php';
    $listingModel = new Listing($conn);
    
    $testTerms = ['education', 'university', 'fiji'];
    
    foreach ($testTerms as $term) {
        echo "<h4>Listing Model Search for: '$term'</h4>";
        
        try {
            $results = $listingModel->search($term, 10, 0);
            $count = $listingModel->countSearch($term);
            
            echo "<p>Model search results: <strong>$count</strong> found</p>";
            echo "<p>Actual results array count: " . count($results) . "</p>";
            
            if (count($results) > 0) {
                echo "<p>Found listings:</p>";
                echo "<ul>";
                foreach ($results as $result) {
                    echo "<li><strong>{$result['title']}</strong> - {$result['region']}</li>";
                }
                echo "</ul>";
            } else {
                echo "<p style='color: orange;'>⚠️ Model returned empty array</p>";
            }
        } catch (Exception $e) {
            echo "<p style='color: red;'>❌ Model error: " . $e->getMessage() . "</p>";
        }
    }
    
    // Test 4: Check if there's an issue with the JOIN
    echo "<h3>4. Testing JOIN with Categories</h3>";
    
    $term = 'education';
    $searchPattern = "%$term%";
    
    $stmt = $conn->prepare("
        SELECT l.id, l.title, c.name as category_name 
        FROM listings l 
        LEFT JOIN categories c ON l.category_id = c.id 
        WHERE l.approved = 1 
        AND (l.title LIKE ? OR l.description LIKE ? OR l.tags LIKE ? OR l.region LIKE ? OR c.name LIKE ?)
    ");
    $stmt->bind_param("sssss", $searchPattern, $searchPattern, $searchPattern, $searchPattern, $searchPattern);
    $stmt->execute();
    $result = $stmt->get_result();
    
    echo "<p>JOIN search found: <strong>{$result->num_rows}</strong> results</p>";
    
    if ($result->num_rows > 0) {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID</th><th>Title</th><th>Category</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>{$row['id']}</td>";
            echo "<td>{$row['title']}</td>";
            echo "<td>{$row['category_name']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

echo "<h3>🎯 Next Steps:</h3>";
echo "<ol>";
echo "<li>Run this script to see exactly where the search fails</li>";
echo "<li>Check if the issue is in the SQL query or the PHP code</li>";
echo "<li>Update your Listing model based on the findings</li>";
echo "</ol>";
?>