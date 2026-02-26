<?php

declare(strict_types=1); // Enforce strict type checking

namespace App\Util\Handlers;

use App\Util\Interfaces\SystemClockInterface;
use DateTimeImmutable;

/**
 * SystemClock
 */
final class SystemClockHandler implements SystemClockInterface
{
    /**
     * now
     *
     * @return DateTimeImmutable
     */
    public function now(): DateTimeImmutable
    {
        return new DateTimeImmutable();
    }
}
