<?php
class Contact {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    public function create($data) {
        return $this->db->execute(
            "INSERT INTO contact_messages (name, email, subject, message, ip_address, user_agent) 
             VALUES (?, ?, ?, ?, ?, ?)",
            [
                $data['name'],
                $data['email'],
                $data['subject'],
                $data['message'],
                $data['ip_address'],
                $data['user_agent']
            ],
            "ssssss"
        );
    }
    
    public function getAll($limit = 50) {
        $result = $this->db->query(
            "SELECT * FROM contact_messages ORDER BY created_at DESC LIMIT ?",
            [$limit],
            "i"
        );
        
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }
    
    public function delete($id) {
        return $this->db->execute(
            "DELETE FROM contact_messages WHERE id = ?",
            [$id],
            "i"
        );
    }
}
?>