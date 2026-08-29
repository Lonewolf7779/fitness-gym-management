<?php
/**
 * Auth Controller
 */

require_once __DIR__ . '/../services/AuthService.php';
require_once __DIR__ . '/../helpers/security.php';
require_once __DIR__ . '/../helpers/validation.php';
require_once __DIR__ . '/../helpers/response.php';

class AuthController {
    private AuthService $authService;

    public function __construct() {
        $this->authService = new AuthService();
    }

    public function processLogin(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/login.php');
        }

        if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
            setFlash('error', 'Invalid security token. Please try again.');
            redirect('/login.php');
        }

        $email    = sanitizeInput($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            setFlash('error', 'Please enter both email and password.');
            redirect('/login.php');
        }

        $result = $this->authService->login($email, $password);

        if ($result['success']) {
            setFlash('success', 'Welcome back!');
            switch ($result['role']) {
                case 'admin':
                    redirect('/admin/index.php');
                    break;
                case 'trainer':
                    redirect('/trainer/index.php');
                    break;
                default:
                    redirect('/member/index.php');
                    break;
            }
        } else {
            setFlash('error', $result['message']);
            redirect('/login.php');
        }
    }

    public function processRegister(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/register.php');
        }

        if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
            setFlash('error', 'Invalid security token. Please try again.');
            redirect('/register.php');
        }

        $fullName = sanitizeInput($_POST['full_name'] ?? '');
        $email    = sanitizeInput($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $phone    = sanitizeInput($_POST['phone'] ?? '');

        $errors = validateRequired(['full_name', 'email', 'password'], $_POST);
        if (!validateEmail($email)) {
            $errors['email'] = 'Please provide a valid email address.';
        }
        if (!validateMinLength($password, 6)) {
            $errors['password'] = 'Password must be at least 6 characters long.';
        }

        if (!empty($errors)) {
            setFlash('error', reset($errors));
            redirect('/register.php');
        }

        $result = $this->authService->registerMember([
            'full_name' => $fullName,
            'email'     => $email,
            'password'  => $password,
            'phone'     => $phone
        ]);

        if ($result['success']) {
            setFlash('success', $result['message']);
            redirect('/login.php');
        } else {
            setFlash('error', $result['message']);
            redirect('/register.php');
        }
    }

    public function logout(): void {
        $this->authService->logout();
        setFlash('success', 'You have been logged out successfully.');
        redirect('/index.php');
    }
}
