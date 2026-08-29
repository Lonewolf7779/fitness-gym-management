<?php
/**
 * Auth Service - Business Logic for Authentication & Session Authorization
 */

require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Member.php';
require_once __DIR__ . '/../helpers/security.php';
require_once __DIR__ . '/../helpers/validation.php';

class AuthService {
    private ?User $userModel = null;
    private ?Member $memberModel = null;

    private function getUserModel(): User {
        if ($this->userModel === null) {
            $this->userModel = new User();
        }
        return $this->userModel;
    }

    private function getMemberModel(): Member {
        if ($this->memberModel === null) {
            $this->memberModel = new Member();
        }
        return $this->memberModel;
    }

    /**
     * Authenticate user credentials and establish session
     * 
     * @param string $email
     * @param string $password
     * @return array Result status and message
     */
    public function login(string $email, string $password): array {
        $email = trim($email);

        if (empty($email) || empty($password)) {
            return ['success' => false, 'message' => 'Please enter both email and password.'];
        }

        if (!validateEmail($email)) {
            return ['success' => false, 'message' => 'Please enter a valid email address format.'];
        }

        try {
            $user = $this->getUserModel()->findByEmail($email);

            // Verify User existence & password hash
            if (!$user || !verifyPassword($password, $user['password_hash'])) {
                return ['success' => false, 'message' => 'Invalid email or password credentials.'];
            }

            // Verify User Status (active / inactive / suspended)
            if ($user['status'] !== 'active') {
                $statusMsg = ($user['status'] === 'suspended') 
                    ? 'Your account has been suspended. Please contact gym administration.' 
                    : 'Your account is currently inactive. Please contact support.';
                return ['success' => false, 'message' => $statusMsg];
            }

            // Establish Secure Session (Regenerate ID to prevent session fixation)
            session_regenerate_id(true);
            $_SESSION['user_id']   = (int) $user['id'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['email']     = $user['email'];
            $_SESSION['role']      = strtolower($user['role']);
            $_SESSION['logged_in_at'] = time();

            return [
                'success' => true,
                'message' => 'Login successful.',
                'role'    => strtolower($user['role'])
            ];

        } catch (Exception $e) {
            // Log error internally
            if (defined('STORAGE_PATH')) {
                error_log("[" . date('Y-m-d H:i:s') . "] Auth Login Error: " . $e->getMessage() . "\n", 3, STORAGE_PATH . '/logs/app.log');
            }
            return [
                'success' => false,
                'message' => 'Unable to authenticate at this time. Please verify database connection.'
            ];
        }
    }

    /**
     * Terminate user session and clear all authentication tokens
     */
    public function logout(): void {
        $_SESSION = array();

        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(), 
                '', 
                time() - 42000,
                $params["path"], 
                $params["domain"],
                $params["secure"], 
                $params["httponly"]
            );
        }

        session_destroy();
    }
}
