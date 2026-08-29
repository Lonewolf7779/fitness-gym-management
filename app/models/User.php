<?php
/**
 * User Model
 */

require_once __DIR__ . '/../config/database.php';

class User {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function findByEmail(string $email): ?array {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public function findById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT id, full_name, email, role, avatar, status, created_at FROM users WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public function create(array $data): int {
        $stmt = $this->db->prepare("
            INSERT INTO users (full_name, email, password_hash, role, status)
            VALUES (:full_name, :email, :password_hash, :role, :status)
        ");
        $stmt->execute([
            'full_name'     => $data['full_name'],
            'email'         => $data['email'],
            'password_hash' => $data['password_hash'],
            'role'          => $data['role'] ?? 'member',
            'status'        => $data['status'] ?? 'active',
        ]);
        return (int) $this->db->lastInsertId();
    }
}
