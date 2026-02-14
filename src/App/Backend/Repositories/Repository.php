<?php

declare (strict_types = 1); // Enforce strict type checking

namespace App\Backend\Repositories;

abstract class Repository
{

    /**
     * cleanQuery
     *
     * Using the spread operator or variadic operator in PHP 8.
     *
     * @param  mixed $query
     * @param  mixed $params
     * @return string
     */
    protected static function cleanQuery(string $query, ...$params): string
    {
        $cleaned = preg_replace('/\s+/', ' ', trim($query));
        return sprintf($cleaned, ...$params);
    }
}
