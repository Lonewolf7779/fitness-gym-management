<?php
/**
 * Logout Entry Point
 */

require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/controllers/AuthController.php';

$authController = new AuthController();
$authController->logout();
