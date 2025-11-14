<?php
class Listing {
    private $db;
    
    public function __construct($conn) {
        $this->db = $conn;
    }
    
    public function find($id) {
        $result = $this->db->query(
            "SELECT l.*, c.name as category_name, u.username as owner_username 
             FROM listings l 
             LEFT JOIN categories c ON l.category_id = c.id 
             LEFT JOIN users u ON l.user_id = u.id 
             WHERE l.id = ? AND l.approved = 1",
            [$id],
            "i"
        );
        
        return $result ? $result->fetch_assoc() : null;
    }
    
    public function getByCategory($categoryId, $limit = 20, $offset = 0) {
        $result = $this->db->query(
            "SELECT l.*, c.name as category_name 
             FROM listings l
             LEFT JOIN categories c ON l.category_id = c.id
             WHERE l.category_id = ? AND l.approved = 1 
             ORDER BY l.featured DESC, l.title 
             LIMIT ? OFFSET ?",
            [$categoryId, $limit, $offset],
            "iii"
        );
        
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }
    
    public function countByCategory($categoryId) {
        $result = $this->db->query(
            "SELECT COUNT(*) as cnt FROM listings 
             WHERE category_id = ? AND approved = 1",
            [$categoryId],
            "i"
        );
        
        $row = $result->fetch_assoc();
        return $row['cnt'];
    }
    
    public function search($term, $limit = 20, $offset = 0) {
        $searchTerm = "%$term%";
        $result = $this->db->query(
            "SELECT l.*, c.name as category_name 
             FROM listings l 
             LEFT JOIN categories c ON l.category_id = c.id 
             WHERE l.approved = 1 
             AND (l.title LIKE ? OR l.description LIKE ? OR l.tags LIKE ?) 
             ORDER BY l.featured DESC, l.title 
             LIMIT ? OFFSET ?",
            [$searchTerm, $searchTerm, $searchTerm, $limit, $offset],
            "sssii"
        );
        
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }
    
    public function countSearch($term) {
        $searchTerm = "%$term%";
        $result = $this->db->query(
            "SELECT COUNT(*) as cnt 
             FROM listings l 
             LEFT JOIN categories c ON l.category_id = c.id 
             WHERE l.approved = 1 
             AND (l.title LIKE ? OR l.description LIKE ? OR l.tags LIKE ?)",
            [$searchTerm, $searchTerm, $searchTerm],
            "sss"
        );
        
        $row = $result->fetch_assoc();
        return $row['cnt'];
    }
    
    public function create($data) {
        $affected = $this->db->execute(
            "INSERT INTO listings 
             (title, url, description, category_id, region, contact_email, 
              phone, address, tags, user_id, approved) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0)",
            [
                $data['title'],
                $data['url'],
                $data['description'],
                $data['category_id'],
                $data['region'],
                $data['contact_email'],
                $data['phone'],
                $data['address'],
                $data['tags'],
                $data['user_id']
            ],
            "sssisssssi"
        );
        
        return $affected ? $this->db->lastInsertId() : false;
    }
    
    public function incrementViews($id) {
        return $this->db->execute(
            "UPDATE listings SET views = views + 1 WHERE id = ?",
            [$id],
            "i"
        );
    }
    
    public function getByUser($userId) {
        $result = $this->db->query(
            "SELECT l.*, c.name as category_name 
             FROM listings l 
             LEFT JOIN categories c ON l.category_id = c.id 
             WHERE l.user_id = ? ORDER BY l.created_at DESC",
            [$userId],
            "i"
        );
        
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }
    
    public function getPending() {
        $result = $this->db->query(
            "SELECT l.*, c.name as category_name 
             FROM listings l 
             LEFT JOIN categories c ON l.category_id = c.id 
             WHERE l.approved = 0 
             ORDER BY l.id DESC"
        );
        
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }
    
    public function getFeatured($limit = 10) {
        $result = $this->db->query(
            "SELECT l.*, c.name as category_name 
             FROM listings l
             LEFT JOIN categories c ON l.category_id = c.id
             WHERE l.approved = 1 AND l.featured = 1
             ORDER BY l.views DESC
             LIMIT ?",
            [$limit],
            "i"
        );
        
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }
    
    public function getTotalApproved() {
        $result = $this->db->query("SELECT COUNT(*) as cnt FROM listings WHERE approved = 1");
        $row = $result->fetch_assoc();
        return $row['cnt'];
    }
    
    public function approve($id) {
        return $this->db->execute(
            "UPDATE listings SET approved = 1 WHERE id = ?",
            [$id],
            "i"
        );
    }
    
    public function delete($id) {
        return $this->db->execute("DELETE FROM listings WHERE id = ?", [$id], "i");
    }
}
?>