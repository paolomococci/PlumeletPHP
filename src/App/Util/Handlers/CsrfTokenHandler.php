<?php

declare(strict_types=1); // Enforce strict type checking

namespace App\Util\Handlers;

use App\Util\Interfaces\CsrfTokenInterface;

/**
 * CsrfTokenHandler
 *
 * Simple CSRF-token manager with a 30-minute lifetime.
 */
final class CsrfTokenHandler implements CsrfTokenInterface
{
    /** Number of random bytes to generate. The resulting hex string will be 2 * this value. */
    private const TOKEN_LENGTH = 32;

    /** Token validity period in seconds (30 minutes). */
    private const TOKEN_LIFETIME = 1800;

    /** Session key where the token and its creation time are stored. */
    private const SESSION_KEY = 'csrf_token';

    /**
     * generateToken
     *
     * Creates a cryptographically secure random token,
     * stores it in the session together with the creation timestamp,
     * and returns the token value.
     *
     * @return string  The freshly generated CSRF token.
     */
    public function generateToken(): string
    {
        // Generate a secure random string and convert it to hexadecimal.
        $token = bin2hex(random_bytes(self::TOKEN_LENGTH));

        // Persist the token and its creation time in the PHP session.
        $_SESSION[self::SESSION_KEY] = [
            'value'      => $token,
            'created_at' => time(),
        ];

        // Return the token so the caller can use it (e.g., embed it in a form).
        return $token;
    }

    /**
     * getToken
     *
     * Returns the current CSRF token if it exists and is still valid.
     * Otherwise, generates a new token, stores it in the session, and returns it.
     *
     * @return string  The valid CSRF token.
     */
    public function getToken(): string
    {
        // If there is no token or the existing one has expired, generate a new one.
        if (! isset($_SESSION['csrf_token']) || $this->isTokenExpired()) {
            return $this->generateToken();
        }

        // The token exists and is still within its lifetime; return it.
        return $_SESSION['csrf_token']['value'];
    }

    /**
     * validateToken
     *
     * Checks that the supplied token matches the one stored in the session
     * and that it hasn't expired yet. Uses hash_equals() to avoid timing attacks.
     *
     * @param string $token  The token submitted by the user (e.g., from a form).
     * @return bool          True if the token is valid and not expired; false otherwise.
     */
    public function validateToken(string $token): bool
    {
        // If the session doesn't hold a token at all, validation fails.
        if (! isset($_SESSION['csrf_token'])) {
            return false;
        }

        // Perform a timing-safe comparison between the stored and supplied tokens.
        return hash_equals($_SESSION[self::SESSION_KEY]['value'], $token);
    }

    /**
     * isTokenExpired
     *
     * Determines whether the token currently stored in the session
     * has exceeded its lifetime.
     *
     * @return bool  True if the token is older than TOKEN_LIFETIME seconds; false otherwise.
     */
    public function isTokenExpired(): bool
    {
        // Grab the creation timestamp; default to 0 if it isn't set.
        $created = $_SESSION[self::SESSION_KEY]['created_at'] ?? 0;

        // Compare elapsed time with the configured lifetime.
        return (time() - $created) >= self::TOKEN_LIFETIME;
    }
}
