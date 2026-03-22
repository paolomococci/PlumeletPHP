<?php

declare(strict_types=1); // Enforce strict type checking

namespace App\Backend\Models\Enums;

/**
 * Backed enum representing the type of a authorization.
 *
 */
enum AuthEnum: string
{
    // Entry level operator type, can only read data without being able to make changes to it.
    case EMPLOYEE = 'employee';
    // Medium level of system operator who can make changes to data, except for those affecting the operators themselves.
    case ADMIN = 'admin';
    // Maximum operator level, able to make any type of modification..
    case CHIEF = 'chief';

    /**
     * isValid
     *
     * @param  mixed $type
     * @return bool
     */
    public static function isValid(string $type): bool
    {
        return self::tryFrom(strtolower($type)) !== null;
    }

    /**
     * Return a human-readable label for the enum case.
     *
     * This is handy when you want to display the type in a UI
     * (e.g. in a <select> element or a table).
     * The function uses PHP 8.0's `match` expression to
     * map the enum case to a friendly string.
     *
     * @return string Human-readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::EMPLOYEE => 'Employee operator',
            self::ADMIN    => 'Admin operator',
            self::CHIEF    => 'Chief operator',
        };
    }
}
