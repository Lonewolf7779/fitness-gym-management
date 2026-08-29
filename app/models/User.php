<?php
/**
 * User Model - Repositories for Database Operations
 */

require_once __DIR__ . '/../config/database.php';

class User {
    private ?PDO $db = null;

    private function getDb(): PDO {
        if ($this->db === null) {
            $this->db = Database::getInstance();
        }
        return $this->db;
    }

    /**
     * Find user record by email address using PDO Prepared Statements
     */
    public function findByEmail(string $email): ?array {
        $stmt = $this->getDb()->prepare("
            SELECT id, full_name, email, password_hash, role, avatar, status, created_at 
            FROM users 
            WHERE email = :email 
            LIMIT 1
        ");
        $stmt->execute(['email' => strtolower(trim($email))]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    /**
     * Find user record by ID using PDO Prepared Statements
     */
    public function findById(int $id): ?array {
        $stmt = $this->getDb()->prepare("
            SELECT id, full_name, email, role, avatar, status, created_at 
            FROM users 
            WHERE id = :id 
            LIMIT 1
        ");
        $stmt->execute(['id' => $id]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    /**
     * Create new user record
     */
    public function create(array $data): int {
        $stmt = $this->getDb()->prepare("
            INSERT INTO users (full_name, email, password_hash, role, status)
            VALUES (:full_name, :email, :password_hash, :role, :status)
        ");
        $stmt->execute([
            'full_name'     => $data['full_name'],
            'email'         => strtolower(trim($data['email'])),
            'password_hash' => $data['password_hash'],
            'role'          => $data['role'] ?? 'member',
            'status'        => $data['status'] ?? 'active',
        ]);
        return (int) $this->getDb()->lastInsertId();
    }
}
