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
    require_once __DIR__ . '/../app/views/auth/login.php';
}
