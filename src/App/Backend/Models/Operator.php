<?php

declare(strict_types=1); // Enforce strict type checking

namespace App\Backend\Models;

use App\Backend\Models\Enums\AuthEnum;

/**
 * Operator
 *
 */
final class Operator
{
    /**
     * __construct
     *
     * Parameters are declared as public typed properties
     * a PHP 8.0 feature. This automatically creates
     * corresponding public properties on the class.
     *
     * @param string $id
     * @param string $email
     * @param AuthEnum $auth
     *
     * @return void
     */
    public function __construct(
        // The identifier will be identical to that of the user, in practice a foreign key.
        public string $id,
        public string $email,
        public AuthEnum $auth,
    ) {}
}
