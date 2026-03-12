<?php

declare(strict_types=1); // Enforce strict type checking

namespace App\Errors;

use RuntimeException;

/**
 * CsrfTokenException
 *
 * Raised when the supplied CSRF token is missing, wrong or expired.
 *
 * Using a specific exception instead of the generic RuntimeException
 * or even the base Exception has two practical benefits:
 *
 *   - The handler can catch *only* CSRF-related failures, leaving
 *       unrelated 500-errors untouched.
 *   - It keeps the API contract clear - the exception type tells the
 *       consumer exactly what went wrong.
 */
final class CsrfTokenException extends RuntimeException
{
    /**
     * CsrfTokenException constructor.
     *
     * @param string      $message   The message that will appear in logs & UI.
     * @param int         $code      HTTP-style error code, default 500.
     * @param \Throwable  $previous  Optional previous exception for chaining.
     */
    public function __construct(
        string $message = 'Invalid or expired CSRF token',
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
