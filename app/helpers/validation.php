<?php
/**
 * IRONCORE Input Validation Helper Functions
 */

if (!function_exists('sanitizeInput')) {
    function sanitizeInput(string $data): string {
        return trim(stripslashes(strip_tags($data)));
    }
}

if (!function_exists('validateEmail')) {
    function validateEmail(string $email): bool {
        return (bool) filter_var($email, FILTER_VALIDATE_EMAIL);
    }
}

if (!function_exists('validatePhone')) {
    function validatePhone(string $phone): bool {
        return (bool) preg_match('/^[0-9+\-\s()]{7,20}$/', $phone);
    }
}

if (!function_exists('validateMinLength')) {
    function validateMinLength(string $input, int $min): bool {
        return mb_strlen($input) >= $min;
    }
}

if (!function_exists('validateRequired')) {
    function validateRequired(array $fields, array $inputData): array {
        $errors = [];
        foreach ($fields as $field) {
            if (empty($inputData[$field]) || trim($inputData[$field]) === '') {
                $errors[$field] = ucfirst(str_replace('_', ' ', $field)) . ' is required.';
            }
        }
        return $errors;
    }
}
