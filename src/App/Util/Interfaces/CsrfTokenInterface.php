<?php

declare(strict_types=1); // Enforce strict type checking

namespace App\Util\Interfaces;

interface CsrfTokenInterface
{
    /**
     * generateToken
     *
     * @return string
     */
    public function generateToken(): string;

    /**
     * getToken
     *
     * @return string
     */
    public function getToken(): string;

    /**
     * validateToken
     *
     * @param  mixed $token
     * @return bool
     */
    public function validateToken(string $token): bool;

    /**
     * isTokenExpired
     *
     * @return bool
     */
    public function isTokenExpired(): bool;
}
