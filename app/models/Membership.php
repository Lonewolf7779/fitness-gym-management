<?php
/**
 * Membership Plan Model
 */

require_once __DIR__ . '/../config/database.php';

class Membership {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getActivePlans(): array {
        $stmt = $this->db->query("SELECT * FROM membership_plans WHERE status = 'active' ORDER BY price ASC");
        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM membership_plans WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $plan = $stmt->fetch();
        return $plan ?: null;
    }
}
