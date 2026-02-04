<?php
declare (strict_types = 1); // Enforce strict type checking

namespace App\Backend\Models\Interfaces;

use DateTimeImmutable;

interface ModelInterface
{
    /**
     * getId
     *
     * @return string
     */
    public function getId(): string;
    
    /**
     * setId
     *
     * @param  string $id
     * @return void
     */
    public function setId(string $id): void;

    /**
     * getCreatedAt
     *
     * @return DateTimeImmutable
     */
    public function getCreatedAt(): DateTimeImmutable;

    /**
     * getUpdatedAt
     *
     * @return DateTimeImmutable
     */
    public function getUpdatedAt(): DateTimeImmutable;
}
