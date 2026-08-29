<?php
/**
 * Attendance Model
 */

require_once __DIR__ . '/../config/database.php';

class Attendance {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getTodayCount(): int {
        $today = date('Y-m-d');
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM attendance WHERE date = :today");
        $stmt->execute(['today' => $today]);
        return (int) $stmt->fetchColumn();
    }

    public function getRecentByMember(int $memberId, int $limit = 10): array {
        $stmt = $this->db->prepare("SELECT * FROM attendance WHERE member_id = :member_id ORDER BY date DESC, check_in_time DESC LIMIT :limit");
        $stmt->bindValue(':member_id', $memberId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
