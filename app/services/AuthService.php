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
        $email = strtolower(trim($email));

        if (empty($email) || empty($password)) {
            return ['success' => false, 'message' => 'Please enter both email and password.'];
        }

        if (!validateEmail($email)) {
            return ['success' => false, 'message' => 'Please enter a valid email address format.'];
        }

        // =========================================================================
        // Development-only authentication mode. Must never be enabled in production.
        // Enabled ONLY when APP_ENV=local AND AUTH_MODE=dev
        // =========================================================================
        if (defined('APP_ENV') && APP_ENV === 'local' && defined('AUTH_MODE') && AUTH_MODE === 'dev') {
            return $this->authenticateDevUser($email, $password);
        }

        // =========================================================================
        // Database Authentication Mode (Production & Normal MySQL Architecture)
        // =========================================================================
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
            $_SESSION['user_id']      = (int) $user['id'];
            $_SESSION['full_name']    = $user['full_name'];
            $_SESSION['email']        = $user['email'];
            $_SESSION['role']         = strtolower($user['role']);
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
     * Temporary Development Authentication Handler (Local Testing Without MySQL)
     * Development-only authentication mode. Must never be enabled in production.
     */
    private function authenticateDevUser(string $email, string $password): array {
        $devUsers = [
            'admin@ironcore.com' => [
                'id'        => 1,
                'full_name' => 'System Administrator',
                'email'     => 'admin@ironcore.com',
                'password'  => 'Admin@123',
                'role'      => 'admin',
                'status'    => 'active'
            ],
            'marcus@ironcore.com' => [
                'id'        => 2,
                'full_name' => 'Marcus Vance',
                'email'     => 'marcus@ironcore.com',
                'password'  => 'Trainer@123',
                'role'      => 'trainer',
                'status'    => 'active'
            ],
            'alex@gmail.com' => [
                'id'        => 4,
                'full_name' => 'Alex Rivera',
                'email'     => 'alex@gmail.com',
                'password'  => 'Member@123',
                'role'      => 'member',
                'status'    => 'active'
            ],
            'suspended@gmail.com' => [
                'id'        => 5,
                'full_name' => 'David Black',
                'email'     => 'suspended@gmail.com',
                'password'  => 'Member@123',
                'role'      => 'member',
                'status'    => 'suspended'
            ],
            'inactive@gmail.com' => [
                'id'        => 6,
                'full_name' => 'Sarah Connor',
                'email'     => 'inactive@gmail.com',
                'password'  => 'Member@123',
                'role'      => 'member',
                'status'    => 'inactive'
            ]
        ];

        if (!array_key_exists($email, $devUsers)) {
            return ['success' => false, 'message' => 'Invalid email or password credentials.'];
        }

        $user = $devUsers[$email];

        if ($password !== $user['password']) {
            return ['success' => false, 'message' => 'Invalid email or password credentials.'];
        }

        if ($user['status'] !== 'active') {
            $statusMsg = ($user['status'] === 'suspended') 
                ? 'Your account has been suspended. Please contact gym administration.' 
                : 'Your account is currently inactive. Please contact support.';
            return ['success' => false, 'message' => $statusMsg];
        }

        // Establish Standard Session Structure
        session_regenerate_id(true);
        $_SESSION['user_id']      = (int) $user['id'];
        $_SESSION['full_name']    = $user['full_name'];
        $_SESSION['email']        = $user['email'];
        $_SESSION['role']         = strtolower($user['role']);
        $_SESSION['logged_in_at'] = time();

        return [
            'success' => true,
            'message' => 'Login successful (Dev Mode).',
            'role'    => strtolower($user['role'])
        ];
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
