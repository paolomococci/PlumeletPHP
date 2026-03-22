<?php

declare(strict_types=1); // Enable strict type checking

namespace App\Frontend\Services;

use App\Backend\Models\Interfaces\ModelInterface;
use App\Backend\Models\User;
use App\Backend\Repositories\UserRepository;
use App\Frontend\Services\UserService;

/**
 * LoginService
 */
final class LoginService
{
    public function __construct(
        protected UserRepository $userRepository,
        protected UserService $userService
    ) {}

    /**
     * register
     *
     * @param  ModelInterface $model
     *
     * @return string
     */
    public function register(ModelInterface $model): string
    {
        return $this->userRepository->create($model);
    }

    /**
     * findByEmail
     *
     * @param  string $email
     *
     * @return User
     */
    public function findByEmail(string $email): ?User
    {
        return $this->userRepository->findByEmail($email);
    }

    /**
     * generateTokenTwoFaHash
     *
     * @param  string $email
     *
     * @return string
     */
    public function generateTokenTwoFaHash(string $email): ?string
    {
        return $this->userRepository->updateHashedTwoFaToken($email);
    }

    /**
     * resetPassword
     *
     * @param  string $email
     * @param  string $passphrase
     * @param  string $password
     *
     * @return void
     */
    public function resetPassword(string $email, string $password, string $passphrase): bool
    {
        return $this->userRepository->resetPassword($email, $password, $passphrase);
    }

    /**
     * authenticate
     *
     * TODO:
     *
     * @param  string $email
     * @param  string $password
     *
     * @throws \BadMethodCallException method not implemented yet.
     *
     * @return null|User
     */
    public function authenticate(string $email, string $password): ?User
    {
        throw new \BadMethodCallException('Not implemented.');
    }

    /**
     * changePassword
     *
     * TODO:
     *
     * @param  string $userId
     * @param  string $oldPassword
     * @param  string $newPassword
     *
     * @throws \BadMethodCallException method not implemented yet
     *
     * @return bool
     */
    public function changePassword(string $userId, string $oldPassword, string $newPassword): bool
    {
        throw new \BadMethodCallException('Not implemented.');
    }
}
