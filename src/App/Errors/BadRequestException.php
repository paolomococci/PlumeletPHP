<?php

declare(strict_types=1); // Enforce strict type checking

namespace App\Errors;

use RuntimeException;

/**
 * BadRequestException
 */
final class BadRequestException extends RuntimeException
{
    /** 
     * 
     * @var array string
     * 
     */
    private array $errors;

    /**
     * __construct
     *
     * @param  mixed $errors
     * @param  mixed $message
     * @param  mixed $code
     * @return void
     */
    public function __construct(array $errors = [], string $message = 'Bad request', int $code = 400)
    {
        parent::__construct($message, $code);
        $this->errors = $errors;
    }

    /**
     * getErrors
     *
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
