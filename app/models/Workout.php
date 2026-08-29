<?php
/**
 * Workout Model
 */

require_once __DIR__ . '/../config/database.php';

class Workout {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getPlansByMember(int $memberId): array {
        $stmt = $this->db->prepare("
            SELECT wp.*, u.full_name as trainer_name 
            FROM workout_plans wp
            LEFT JOIN trainers t ON wp.trainer_id = t.id
            LEFT JOIN users u ON t.user_id = u.id
            WHERE wp.member_id = :member_id
            ORDER BY wp.created_at DESC
        ");
        $stmt->execute(['member_id' => $memberId]);
        return $stmt->fetchAll();
    }
}
