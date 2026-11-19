<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Class Csrf
 * 
 * Manages Cross-Site Request Forgery (CSRF) token generation and validation.
 * Uses session storage and cryptographically secure hash_equals() comparison.
 * 
 * @package App\Core
 */
class Csrf
{
    private const SESSION_KEY = '_csrf_token';

    /**
     * Generate or return existing CSRF token stored in session.
     *
     * @return string
     */
    public static function generateToken(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION[self::SESSION_KEY])) {
            $_SESSION[self::SESSION_KEY] = bin2hex(random_bytes(32));
        }

        return $_SESSION[self::SESSION_KEY];
    }

    /**
     * Validate submitted CSRF token using timing attack resistant comparison.
     *
     * @param string|null $token
     * @return bool
     */
    public static function validateToken(?string $token): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($token) || empty($_SESSION[self::SESSION_KEY])) {
            return false;
        }

        return hash_equals($_SESSION[self::SESSION_KEY], $token);
    }

    /**
     * Helper to render hidden HTML input field with CSRF token.
     *
     * @return string
     */
    public static function field(): string
    {
        $token = self::generateToken();
        return '<input type="hidden" name="_csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }
}
