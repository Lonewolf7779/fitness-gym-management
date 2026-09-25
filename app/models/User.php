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
     * Find user record by email or username using PDO Prepared Statements
     */
    public function findByIdentifier(string $identifier): ?array {
        $clean = trim($identifier);
        $stmt = $this->getDb()->prepare("
            SELECT id, full_name, username, email, password_hash, role, avatar, status, created_at 
            FROM users 
            WHERE email = :email_val OR username = :uname_val 
            LIMIT 1
        ");
        $stmt->execute([
            'email_val' => strtolower($clean),
            'uname_val' => $clean
        ]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    /**
     * Find user record by email address using PDO Prepared Statements
     */
    public function findByEmail(string $email): ?array {
        $stmt = $this->getDb()->prepare("
            SELECT id, full_name, username, email, password_hash, role, avatar, status, created_at 
            FROM users 
            WHERE email = :email 
            LIMIT 1
        ");
        $stmt->execute(['email' => strtolower(trim($email))]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    /**
     * Find user record by username using PDO Prepared Statements
     */
    public function findByUsername(string $username): ?array {
        $stmt = $this->getDb()->prepare("
            SELECT id, full_name, username, email, password_hash, role, avatar, status, created_at 
            FROM users 
            WHERE username = :username 
            LIMIT 1
        ");
        $stmt->execute(['username' => trim($username)]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    /**
     * Find user record by ID using PDO Prepared Statements
     */
    public function findById(int $id): ?array {
        $stmt = $this->getDb()->prepare("
            SELECT id, full_name, username, email, role, avatar, status, created_at 
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
        $username = trim($data['username'] ?? '');
        if ($username === '') {
            $emailParts = explode('@', $data['email'] ?? 'user');
            $username = strtolower(preg_replace('/[^a-zA-Z0-9_.]/', '', $emailParts[0])) . '_' . substr(bin2hex(random_bytes(2)), 0, 4);
        }

        $stmt = $this->getDb()->prepare("
            INSERT INTO users (full_name, username, email, password_hash, role, status)
            VALUES (:full_name, :username, :email, :password_hash, :role, :status)
        ");
        $stmt->execute([
            'full_name'     => $data['full_name'],
            'username'      => $username,
            'email'         => strtolower(trim($data['email'])),
            'password_hash' => $data['password_hash'],
            'role'          => $data['role'] ?? 'member',
            'status'        => $data['status'] ?? 'active',
        ]);
        return (int) $this->getDb()->lastInsertId();
    }
}
