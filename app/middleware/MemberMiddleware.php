<?php
/**
 * Member Middleware - Ensures user has member role
 */

require_once __DIR__ . '/AuthMiddleware.php';

class MemberMiddleware {
    public static function handle(): void {
        AuthMiddleware::handle();
        if (($_SESSION['role'] ?? '') !== 'member') {
            setFlash('error', 'Unauthorized access. Member privileges required.');
            redirect('/index.php');
        }
    }
}
