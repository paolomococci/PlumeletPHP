<?php

declare (strict_types = 1); // Enforce strict type checking

namespace App\Errors;

use Psr\Container\NotFoundExceptionInterface;

/**
 * Exception thrown when a class or a value is internal server error http status code 500.
 */
class InternalServerError extends \Exception implements NotFoundExceptionInterface
{
}
