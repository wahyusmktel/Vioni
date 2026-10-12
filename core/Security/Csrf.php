<?php

declare(strict_types=1);

namespace Vioni\Security;

/**
 * Cross-Site Request Forgery (CSRF) Defense Engine.
 * Generates cryptographically secure tokens and validates incoming requests
 * using timing-attack resistant comparisons (hash_equals).
 */
class Csrf
{
    private const SESSION_KEY = '_vioni_csrf';

    /**
     * Start session safely if not already active.
     */
    private static function startSession(): void
    {
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            session_start();
        }
    }

    /**
     * Get existing CSRF token or generate a new cryptographically secure token.
     */
    public static function token(): string
    {
        self::startSession();

        if (empty($_SESSION[self::SESSION_KEY]) || !is_string($_SESSION[self::SESSION_KEY])) {
            // 32 bytes = 256 bits of entropy -> 64 hex characters
            $_SESSION[self::SESSION_KEY] = bin2hex(random_bytes(32));
        }

        return $_SESSION[self::SESSION_KEY];
    }

    /**
     * Regenerate CSRF token (useful after login/logout to prevent session fixation).
     */
    public static function regenerate(): string
    {
        self::startSession();
        $_SESSION[self::SESSION_KEY] = bin2hex(random_bytes(32));
        return $_SESSION[self::SESSION_KEY];
    }

    /**
     * Validate an incoming CSRF token with timing-attack mitigation.
     */
    public static function validate(?string $token): bool
    {
        if ($token === null || $token === '') {
            return false;
        }

        self::startSession();
        $storedToken = $_SESSION[self::SESSION_KEY] ?? null;

        if (!is_string($storedToken) || $storedToken === '') {
            return false;
        }

        // Timing-attack resistant string comparison
        return hash_equals($storedToken, $token);
    }

    /**
     * Generate an HTML hidden input tag for forms.
     */
    public static function field(): string
    {
        $token = htmlspecialchars(self::token(), ENT_QUOTES, 'UTF-8');
        return '<input type="hidden" name="_csrf" value="' . $token . '">';
    }

    /**
     * Generate an HTML meta tag for JavaScript AJAX/Fetch.
     */
    public static function meta(): string
    {
        $token = htmlspecialchars(self::token(), ENT_QUOTES, 'UTF-8');
        return '<meta name="csrf-token" content="' . $token . '">';
    }
}
