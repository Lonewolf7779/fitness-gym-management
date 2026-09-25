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
     * Authenticate user credentials (via email or username) and establish session
     * 
     * @param string $identifier Email or Username
     * @param string $password
     * @return array Result status, message, and role
     */
    public function login(string $identifier, string $password): array {
        $identifier = trim($identifier);

        if (empty($identifier) || empty($password)) {
            return ['success' => false, 'message' => 'Please enter both username/email and password.'];
        }

        // =========================================================================
        // Database Authentication Mode (MySQL Single Source of Truth)
        // =========================================================================
        try {
            $user = $this->getUserModel()->findByIdentifier($identifier);

            // Verify User existence & password hash
            if (!$user || !verifyPassword($password, $user['password_hash'])) {
                return ['success' => false, 'message' => 'Invalid email/username or password credentials.'];
            }

            // Verify User Status (active / inactive / suspended)
            if ($user['status'] !== 'active') {
                $statusMsg = ($user['status'] === 'suspended') 
                    ? 'Your account has been suspended. Please contact gym administration.' 
                    : 'Your account is currently inactive. Please contact support.';
                return ['success' => false, 'message' => $statusMsg];
            }

            // Establish Secure Session (Regenerate ID to prevent session fixation)
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            session_regenerate_id(true);
            $_SESSION['user_id']      = (int) $user['id'];
            $_SESSION['full_name']    = $user['full_name'];
            $_SESSION['username']     = $user['username'] ?? '';
            $_SESSION['email']        = $user['email'];
            $_SESSION['role']         = strtolower($user['role']);
            $_SESSION['logged_in_at'] = time();

            return [
                'success' => true,
                'message' => 'Login successful.',
                'role'    => strtolower($user['role'])
            ];

        } catch (Exception $e) {
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
     * Register a new member in the system
     *
     * @param array $data Form submission payload
     * @return array Result status and message
     */
    public function registerUser(array $data): array {
        $fullName = trim($data['full_name'] ?? '');
        $username = strtolower(trim($data['username'] ?? ''));
        $email    = strtolower(trim($data['email'] ?? ''));
        $phone    = trim($data['phone'] ?? '');
        $password = $data['password'] ?? '';

        if (empty($fullName) || strlen($fullName) < 2) {
            return ['success' => false, 'message' => 'Please provide a valid full name.'];
        }

        if (empty($username) || !preg_match('/^[a-z0-9_.-]{3,30}$/', $username)) {
            return ['success' => false, 'message' => 'Username must be between 3 and 30 characters (alphanumeric, dots, underscores, dashes only).'];
        }

        if (empty($email) || !validateEmail($email)) {
            return ['success' => false, 'message' => 'Please provide a valid email address.'];
        }

        if (empty($password) || strlen($password) < 6) {
            return ['success' => false, 'message' => 'Password must be at least 6 characters long.'];
        }

        try {
            // Check if username is taken
            if ($this->getUserModel()->findByUsername($username)) {
                return ['success' => false, 'message' => 'The chosen username is already taken.'];
            }

            // Check if email is registered
            if ($this->getUserModel()->findByEmail($email)) {
                return ['success' => false, 'message' => 'An account with this email address already exists.'];
            }

            // Hash password
            $passwordHash = hashPassword($password);

            // Create User Record
            $userId = $this->getUserModel()->create([
                'full_name'     => $fullName,
                'username'      => $username,
                'email'         => $email,
                'password_hash' => $passwordHash,
                'role'          => 'member',
                'status'        => 'active'
            ]);

            // Create Member Profile Record
            $this->getMemberModel()->create([
                'user_id'   => $userId,
                'phone'     => !empty($phone) ? $phone : null,
                'join_date' => date('Y-m-d')
            ]);

            // Establish Secure Session for immediate login
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            session_regenerate_id(true);
            $_SESSION['user_id']      = $userId;
            $_SESSION['full_name']    = $fullName;
            $_SESSION['username']     = $username;
            $_SESSION['email']        = $email;
            $_SESSION['role']         = 'member';
            $_SESSION['logged_in_at'] = time();

            return [
                'success' => true,
                'message' => 'Account created successfully! Welcome to IRONCORE.',
                'role'    => 'member'
            ];

        } catch (Exception $e) {
            if (defined('STORAGE_PATH')) {
                error_log("[" . date('Y-m-d H:i:s') . "] Auth Register Error: " . $e->getMessage() . "\n", 3, STORAGE_PATH . '/logs/app.log');
            }
            return [
                'success' => false,
                'message' => 'Registration could not be completed. Please try again or contact support.'
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
