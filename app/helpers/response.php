<?php
/**
 * IRONCORE Response & HTTP Utilities
 */

if (!function_exists('jsonResponse')) {
    function jsonResponse(array $data, int $statusCode = 200): void {
        header_remove();
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
}

if (!function_exists('redirect')) {
    function redirect(string $url): void {
        header("Location: {$url}");
        exit;
    }
}

if (!function_exists('setFlash')) {
    function setFlash(string $type, string $message): void {
        $_SESSION['flash'][$type] = $message;
    }
}

if (!function_exists('getFlash')) {
    function getFlash(string $type): ?string {
        if (isset($_SESSION['flash'][$type])) {
            $msg = $_SESSION['flash'][$type];
            unset($_SESSION['flash'][$type]);
            return $msg;
        }
        return null;
    }
}
