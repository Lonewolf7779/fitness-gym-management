<?php
/**
 * Admin Middleware - Ensures user has admin role
 */

require_once __DIR__ . '/AuthMiddleware.php';

class AdminMiddleware {
    public static function handle(): void {
        AuthMiddleware::handle();
        if (($_SESSION['role'] ?? '') !== 'admin') {
            setFlash('error', 'Unauthorized access. Admin privileges required.');
            redirect('/index.php');
        }
    }
}
