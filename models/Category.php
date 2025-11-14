<?php
class Category {
    private $db;
    
    public function __construct($conn) {
        $this->db = $conn;
    }
    
    public function getAllWithSubcategories() {
        $result = $this->db->query("
            SELECT c1.* 
            FROM categories c1 
            WHERE c1.parent_id IS NULL OR c1.parent_id = 0 
            ORDER BY c1.name
        ");
        
        $categories = [];
        while ($category = $result->fetch_assoc()) {
            $category['subcategories'] = $this->getSubcategories($category['id']);
            
            // COUNT LISTINGS IN THIS MAIN CATEGORY + ALL ITS SUBCATEGORIES
            $category['listing_count'] = $this->countListingsInMainCategory($category['id']);
            
            $categories[] = $category;
        }
        
        return $categories;
    }
    
    public function getSubcategories($parentId) {
        $result = $this->db->query("
            SELECT c.* 
            FROM categories c 
            WHERE c.parent_id = ? 
            ORDER BY c.name
        ", [$parentId], "i");
        
        $subcategories = [];
        while ($subcat = $result->fetch_assoc()) {
            // For subcategories, count ONLY direct listings in that subcategory
            $subcat['listing_count'] = $this->countDirectListings($subcat['id']);
            $subcategories[] = $subcat;
        }
        
        return $subcategories;
    }
    
    public function countDirectListings($categoryId) {
        $result = $this->db->query("
            SELECT COUNT(*) as total 
            FROM listings 
            WHERE approved = 1 AND category_id = ?
        ", [$categoryId], "i");
        
        $row = $result->fetch_assoc();
        return $row['total'];
    }
    
    public function countListingsInMainCategory($mainCategoryId) {
        $result = $this->db->query("
            SELECT COUNT(*) as total 
            FROM listings l 
            WHERE l.approved = 1 
            AND (
                l.category_id = ? 
                OR l.category_id IN (
                    SELECT id FROM categories WHERE parent_id = ?
                )
            )
        ", [$mainCategoryId, $mainCategoryId], "ii");
        
        $row = $result->fetch_assoc();
        return $row['total'];
    }
    
    public function find($id) {
        $result = $this->db->query("SELECT * FROM categories WHERE id = ?", [$id], "i");
        return $result->fetch_assoc();
    }
    
    public function getTotalMainCategories() {
        $result = $this->db->query("SELECT COUNT(*) as cnt FROM categories WHERE parent_id IS NULL OR parent_id = 0");
        $row = $result->fetch_assoc();
        return $row['cnt'];
    }
    
    public function getAll() {
        $result = $this->db->query("
            SELECT c1.id, c1.name, c2.name as parent_name 
            FROM categories c1 
            LEFT JOIN categories c2 ON c1.parent_id = c2.id 
            ORDER BY c2.name, c1.name
        ");
        return $result->fetch_all(MYSQLI_ASSOC);
    }
}
?>