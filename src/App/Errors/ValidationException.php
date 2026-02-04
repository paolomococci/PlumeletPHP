<?php

declare (strict_types = 1); // Enforce strict type checking

namespace App\Errors;

use InvalidArgumentException;

/**
 * ValidationException
 *
 * Recommended use:
 *
 * Single error:
 * throw new ValidationException('Email is invalid');
 *
 * Multiple errors:
 * throw new ValidationException([
 *      'email' => 'Invalid format',
 *      'password' => 'Too short',
 * ]);
 *
 */
class ValidationException extends InvalidArgumentException
{
    /**
     * @var array<string,string> Detailed errors (field => message)
     */
    public readonly array $errors;

    /**
     * __construct
     *
     * @param  mixed $errors
     * @param  mixed $code
     * @param  mixed $previous
     * @return void
     */
    public function __construct(string | array $errors, int $code = 0,  ? \Throwable $previous = null)
    {
        $message = is_array($errors) ? 'Validation failed' : (string) $errors;
        parent::__construct($message, $code, $previous);
        $this->errors = is_array($errors) ? $errors : ['_error' => (string) $errors];
    }
}
