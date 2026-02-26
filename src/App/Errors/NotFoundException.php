<?php

declare(strict_types=1); // Enforce strict type checking

namespace App\Errors;

use RuntimeException;

/**
 * NotFoundException
 */
final class NotFoundException extends RuntimeException
{
    /**
     * __construct
     *
     * @param  mixed $message
     * @param  mixed $code
     * @return void
     */
    public function __construct(string $message = 'Resource not found', int $code = 404)
    {
        parent::__construct($message, $code);
    }
}
