<?php
/**
 * Auth Middleware - Ensures user is logged in
 */

require_once __DIR__ . '/../helpers/response.php';

class AuthMiddleware {
    public static function handle(): void {
        if (empty($_SESSION['user_id'])) {
            setFlash('error', 'Please log in to access this page.');
            redirect('/login.php');
        }
    }
}
