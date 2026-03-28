<?php

declare(strict_types=1); // Enforce strict type checking

namespace App\Backend\Models\Factories;

use App\Backend\Models\Operator;
use App\Errors\InvalidOperatorException;

/**
 * OperatorFactory
 */
final class OperatorFactory
{    
    /**
     * create
     *
     * @param  null|string $userId
     * @return Operator
     */
    public static function create(?string $userId): Operator
    {
        if ($userId === null) {
            throw new InvalidOperatorException('Missing user id.');
        }

        $operator = new Operator();

        // Activate the facade only to read the Entity.
        $operator->setFacade();
        $operator->setId($userId)->read();

        // If operator has not been registered, it is created from the user identifier.
        if ($operator->getEmail() === null && $operator->getAuth() === null) {
            $operator->create();
        }

        // Deactivate the facade: now the object is light.
        $operator->unsetFacade();

        // Now check if it is still valid.
        if ($operator->getEmail() === null && $operator->getAuth() === null) {
            throw new InvalidOperatorException('Operator not found.');
        }

        return $operator;
    }
}
