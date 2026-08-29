<?php
/**
 * Progress Model
 */

require_once __DIR__ . '/../config/database.php';

class Progress {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getLogsByMember(int $memberId): array {
        $stmt = $this->db->prepare("SELECT * FROM progress_logs WHERE member_id = :member_id ORDER BY log_date ASC");
        $stmt->execute(['member_id' => $memberId]);
        return $stmt->fetchAll();
    }
}
