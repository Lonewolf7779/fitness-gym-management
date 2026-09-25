<?php
/**
 * Auth Controller - Handles Authentication HTTP Requests & Views
 */

require_once __DIR__ . '/../services/AuthService.php';
require_once __DIR__ . '/../helpers/security.php';
require_once __DIR__ . '/../helpers/validation.php';
require_once __DIR__ . '/../helpers/response.php';

class AuthController {
    private ?AuthService $authService = null;

    private function getAuthService(): AuthService {
        if ($this->authService === null) {
            $this->authService = new AuthService();
        }
        return $this->authService;
    }

    /**
     * Process User Login Request
     */
    public function processLogin(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/login.php');
        }

        // Validate CSRF Security Token
        if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
            setFlash('error', 'Invalid security token. Please refresh and try again.');
            redirect('/login.php');
        }

        $identifier = sanitizeInput($_POST['identity'] ?? $_POST['email'] ?? $_POST['username'] ?? '');
        $password   = $_POST['password'] ?? '';

        // Execute Authentication Service Logic
        $result = $this->getAuthService()->login($identifier, $password);

        if ($result['success']) {
            setFlash('success', 'Welcome back to IRONCORE!');

            // Server-determined role redirection
            switch ($result['role']) {
                case 'admin':
                    redirect('/admin/index.php');
                    break;
                case 'trainer':
                    redirect('/trainer/index.php');
                    break;
                case 'member':
                default:
                    redirect('/member/index.php');
                    break;
            }
        } else {
            setFlash('error', $result['message']);
            redirect('/login.php');
        }
    }

    /**
     * Process User Registration Request
     */
    public function processRegister(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/register.php');
        }

        // Validate CSRF Security Token
        if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
            setFlash('error', 'Invalid security token. Please refresh and try again.');
            redirect('/register.php');
        }

        $fullName = sanitizeInput($_POST['full_name'] ?? '');
        $username = sanitizeInput($_POST['username'] ?? '');
        $email    = sanitizeInput($_POST['email'] ?? '');
        $phone    = sanitizeInput($_POST['phone'] ?? '');
        $password = $_POST['password'] ?? '';

        $result = $this->getAuthService()->registerUser([
            'full_name' => $fullName,
            'username'  => $username,
            'email'     => $email,
            'phone'     => $phone,
            'password'  => $password
        ]);

        if ($result['success']) {
            setFlash('success', $result['message']);
            redirect('/member/index.php');
        } else {
            setFlash('error', $result['message']);
            redirect('/register.php');
        }
    }

    /**
     * Process User Logout Request
     */
    public function logout(): void {
        $this->getAuthService()->logout();
        
        // Start fresh session to pass logout flash message
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        setFlash('success', 'You have been logged out successfully.');
        redirect('/login.php');
    }
}
