<?php

declare(strict_types=1); // Enforce strict type checking

namespace App\Util\Interfaces;

use DateTimeImmutable;

interface SystemClockInterface
{
    /**
     * now
     *
     * Returns the current date/time as a DateTimeImmutable instance.
     *
     * @return DateTimeImmutable
     */
    public function now(): DateTimeImmutable;
}
