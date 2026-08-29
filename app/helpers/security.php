<?php
/**
 * IRONCORE Security Helper Functions
 */

if (!function_exists('e')) {
    /**
     * Escape HTML output to prevent XSS
     */
    function e(?string $string): string {
        return htmlspecialchars($string ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

if (!function_exists('generateCsrfToken')) {
    /**
     * Generate or return active CSRF token
     */
    function generateCsrfToken(): string {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}

if (!function_exists('validateCsrfToken')) {
    /**
     * Validate submitted CSRF token
     */
    function validateCsrfToken(?string $token): bool {
        if (empty($_SESSION['csrf_token']) || empty($token)) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }
}

if (!function_exists('hashPassword')) {
    /**
     * Hash password using BCRYPT / Argon2id
     */
    function hashPassword(string $password): string {
        return password_hash($password, PASSWORD_DEFAULT);
    }
}

if (!function_exists('verifyPassword')) {
    /**
     * Verify password hash
     */
    function verifyPassword(string $password, string $hash): bool {
        return password_verify($password, $hash);
    }
}
