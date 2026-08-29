<?php
/**
 * Auth Service - Handles user login, registration & session authorization
 */

require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Member.php';
require_once __DIR__ . '/../helpers/security.php';

class AuthService {
    private User $userModel;
    private Member $memberModel;

    public function __construct() {
        $this->userModel = new User();
        $this->memberModel = new Member();
    }

    public function login(string $email, string $password): array {
        $user = $this->userModel->findByEmail($email);
        if (!$user) {
            return ['success' => false, 'message' => 'Invalid email or password credentials.'];
        }

        if ($user['status'] !== 'active') {
            return ['success' => false, 'message' => 'Your account is suspended or inactive. Please contact support.'];
        }

        if (!verifyPassword($password, $user['password_hash'])) {
            return ['success' => false, 'message' => 'Invalid email or password credentials.'];
        }

        // Set Session Data securely
        session_regenerate_id(true);
        $_SESSION['user_id']   = $user['id'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['email']     = $user['email'];
        $_SESSION['role']      = $user['role'];

        return [
            'success' => true,
            'message' => 'Login successful.',
            'role'    => $user['role']
        ];
    }

    public function registerMember(array $data): array {
        if ($this->userModel->findByEmail($data['email'])) {
            return ['success' => false, 'message' => 'Email address is already registered.'];
        }

        $userId = $this->userModel->create([
            'full_name'     => $data['full_name'],
            'email'         => $data['email'],
            'password_hash' => hashPassword($password = $data['password']),
            'role'          => 'member',
            'status'        => 'active'
        ]);

        $this->memberModel->create([
            'user_id' => $userId,
            'phone'   => $data['phone'] ?? null
        ]);

        return ['success' => true, 'message' => 'Registration successful! Please login to continue.'];
    }

    public function logout(): void {
        $_SESSION = array();
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
    }
}
