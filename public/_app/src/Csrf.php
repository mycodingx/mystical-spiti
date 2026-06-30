<?php
/**
 * Mystical Expedition - CSRF Token Helper
 *
 * Generates a per-session token, stores in session,
 * verifies with timing-safe comparison.
 */

declare(strict_types=1);

namespace Mystical;

final class Csrf
{
    private const SESSION_KEY = '_csrf_token';
    private const FIELD_NAME  = '_csrf';
    private const HEADER_NAME = 'X-CSRF-Token';

    /**
     * Get the current token, generating one if needed.
     */
    public static function token(): string
    {
        if (empty($_SESSION[self::SESSION_KEY])) {
            $_SESSION[self::SESSION_KEY] = bin2hex(random_bytes(32));
        }
        return $_SESSION[self::SESSION_KEY];
    }

    /**
     * Render a hidden input for use in forms.
     */
    public static function field(): string
    {
        return sprintf(
            '<input type="hidden" name="%s" value="%s">',
            self::FIELD_NAME,
            htmlspecialchars(self::token(), ENT_QUOTES, 'UTF-8')
        );
    }

    /**
     * Verify a submitted token. Clears it on success (one-shot).
     *
     * @param string|null $token Token from form/header (null to look in $_POST)
     */
    public static function verify(?string $token = null): bool
    {
        if ($token === null) {
            $token = $_POST[self::FIELD_NAME]
                ?? $_SERVER['HTTP_' . str_replace('-', '_', self::HEADER_NAME)]
                ?? null;
        }

        $expected = $_SESSION[self::SESSION_KEY] ?? null;

        if (!is_string($token) || !is_string($expected)) {
            return false;
        }

        $valid = hash_equals($expected, $token);

        if ($valid) {
            // Rotate token after successful use
            unset($_SESSION[self::SESSION_KEY]);
        }

        return $valid;
    }

    public static function fieldName(): string
    {
        return self::FIELD_NAME;
    }
}