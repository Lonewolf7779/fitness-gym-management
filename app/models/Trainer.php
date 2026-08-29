<?php
/**
 * Trainer Model
 */

require_once __DIR__ . '/../config/database.php';

class Trainer {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getAll(): array {
        $stmt = $this->db->query("
            SELECT t.*, u.full_name, u.email, u.avatar 
            FROM trainers t
            JOIN users u ON t.user_id = u.id
            WHERE u.status = 'active'
        ");
        return $stmt->fetchAll();
    }

    public function getActiveCount(): int {
        $stmt = $this->db->query("SELECT COUNT(*) FROM trainers t JOIN users u ON t.user_id = u.id WHERE u.status = 'active'");
        return (int) $stmt->fetchColumn();
    }
}
