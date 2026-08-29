<?php
/**
 * Member Model
 */

require_once __DIR__ . '/../config/database.php';

class Member {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function create(array $data): int {
        $stmt = $this->db->prepare("
            INSERT INTO members (user_id, phone, emergency_contact, gender, dob, address, join_date)
            VALUES (:user_id, :phone, :emergency_contact, :gender, :dob, :address, :join_date)
        ");
        $stmt->execute([
            'user_id'           => $data['user_id'],
            'phone'             => $data['phone'] ?? null,
            'emergency_contact' => $data['emergency_contact'] ?? null,
            'gender'            => $data['gender'] ?? null,
            'dob'               => $data['dob'] ?? null,
            'address'           => $data['address'] ?? null,
            'join_date'         => $data['join_date'] ?? date('Y-m-d'),
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function findByUserId(int $userId): ?array {
        $stmt = $this->db->prepare("
            SELECT m.*, u.full_name, u.email, u.avatar, u.status 
            FROM members m
            JOIN users u ON m.user_id = u.id
            WHERE m.user_id = :user_id LIMIT 1
        ");
        $stmt->execute(['user_id' => $userId]);
        $member = $stmt->fetch();
        return $member ?: null;
    }

    public function getActiveCount(): int {
        $stmt = $this->db->query("SELECT COUNT(*) FROM members m JOIN users u ON m.user_id = u.id WHERE u.status = 'active'");
        return (int) $stmt->fetchColumn();
    }
}
