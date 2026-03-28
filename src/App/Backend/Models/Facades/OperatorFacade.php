<?php

declare(strict_types=1); // Enforce strict type checking

namespace App\Backend\Models\Facades;

use App\Backend\Models\Operator;
use App\Frontend\Services\OperatorService;

/**
 * OperatorFacade
 */
class OperatorFacade
{    
    /**
     * __construct
     *
     * @return void
     */
    public function __construct(
        protected OperatorService $operatorService
    ) {}
    
    /**
     * createByUserId
     *
     * @param  string $userId
     * @return Operator
     */
    public function createByUserId(string $userId): ?Operator
    {
        // Delegates entirely to the service.
        return $this->operatorService->createByUserId($userId);
    }
    
    /**
     * readById
     *
     * @param  string $operatorId
     * @return Operator
     */
    public function readById(string $operatorId): ?Operator
    {
        // Delegates entirely to the service.
        return $this->operatorService->read($operatorId);
    }
    
    /**
     * updateEmail
     *
     * @param  Operator $operator
     * @return Operator
     */
    public function updateEmail(Operator $operator): Operator
    {
        $stored = $this->operatorService->updateEmail($operator);
        // Fallback on null.
        return $stored ?? $operator;
    }
    
    /**
     * updateAuth
     *
     * @param  Operator $operator
     * @return Operator
     */
    public function updateAuth(Operator $operator): Operator
    {
        $stored = $this->operatorService->updateAuth($operator);
        // Fallback on null.
        return $stored ?? $operator;
    }
    
    /**
     * delete
     *
     * @param  Operator $operator
     * @return bool
     */
    public function delete(Operator $operator): bool
    {
        return $this->operatorService->delete($operator);
    }
}
