<?php
/**
 * Login Entry Point
 */

require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/controllers/AuthController.php';

$authController = new AuthController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $authController->processLogin();
} else {
    // If already authenticated, redirect to role portal
    if (!empty($_SESSION['user_id']) && !empty($_SESSION['role'])) {
        switch ($_SESSION['role']) {
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
    }
    require_once __DIR__ . '/../app/views/auth/login.php';
}
