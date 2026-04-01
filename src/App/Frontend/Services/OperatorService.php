<?php

declare(strict_types=1); // Enforce strict type checking

namespace App\Frontend\Services;

use App\Backend\Models\Operator;
use App\Backend\Repositories\OperatorRepository;
use App\Frontend\Services\UserService;

class OperatorService
{
    /**
     * __construct
     *
     * @return void
     */
    public function __construct(
        protected OperatorRepository $operatorRepository,
        protected UserService $userService
    ) {}

    /**
     * createByUserId
     *
     * @param  string $id
     * @return null|Operator
     */
    public function createByUserId(string $id): ?Operator
    {
        $user = $this->userService->read($id);
        if ($user === null) {
            return null;
        }

        $operator = new Operator(
            $user->getId(),
            $user->getEmail()
        );

        return $this->operatorRepository->create($operator);
    }
    
    /**
     * createWithRole
     *
     * @param  Operator $operator
     * @return null|Operator
     */
    public function createWithRole(Operator $operator): ?Operator {
        return $this->operatorRepository->createWithRole($operator);
    }

    /**
     * readById
     *
     * @param  string $id
     * @return null|Operator
     */
    public function read(string $id): ?Operator
    {
        return $this->operatorRepository->read($id);
    }

    /**
     * updateEmail
     *
     * @param  Operator $operator
     * @return Operator
     */
    public function updateEmail(Operator $operator): ?Operator
    {
        return $this->operatorRepository->updateEmail($operator);
    }

    /**
     * updateAuth
     *
     * @param  Operator $operator
     * @return Operator
     */
    public function updateAuth(Operator $operator): ?Operator
    {
        return $this->operatorRepository->updateAuth($operator);
    }

    /**
     * delete
     *
     * @param  Operator $operator
     * @return bool
     */
    public function delete(Operator $operator): bool
    {
        return $this->operatorRepository->delete($operator);
    }
}
