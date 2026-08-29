<?php
/**
 * Trainer Middleware - Ensures user has trainer or admin role
 */

require_once __DIR__ . '/AuthMiddleware.php';

class TrainerMiddleware {
    public static function handle(): void {
        AuthMiddleware::handle();
        $role = $_SESSION['role'] ?? '';
        if ($role !== 'trainer' && $role !== 'admin') {
            setFlash('error', 'Unauthorized access. Trainer privileges required.');
            redirect('/index.php');
        }
    }
}
