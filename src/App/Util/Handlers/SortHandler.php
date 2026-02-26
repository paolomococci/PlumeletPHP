<?php

declare(strict_types=1); // Enforce strict type checking

namespace App\Util\Handlers;

/**
 * Sort
 */
final class SortHandler
{
    /**
     * __construct
     *
     * @param  array<string,'asc'|'desc'> $fields
     * @return void
     */
    public function __construct(public readonly array $fields = []) {}
}
