<?php

declare(strict_types=1); // Enforce strict type checking

namespace App\Frontend\Services\Interfaces;

use App\Backend\Models\Interfaces\ModelInterface;

interface ServiceInterface
{
    /**
     * index
     *
     * @return array
     */
    public function index(): array;

    /**
     * create
     *
     * @param  mixed $model
     * @return string
     */
    public function create(ModelInterface $model): string;

    /**
     * read
     *
     * @param  mixed $id
     * @return ModelInterface
     */
    public function read(string $id): ?ModelInterface;

    /**
     * update
     *
     * @param  mixed $model
     * @return ModelInterface
     */
    public function update(ModelInterface $model): ?ModelInterface;

    /**
     * delete
     *
     * @param  mixed $id
     * @return bool
     */
    public function delete(string $id): bool;

    /**
     * findByName
     *
     * @param  mixed $name
     * @return array
     */
    public function findByName(string $name): array;

    /**
     * count
     *
     * @return int
     */
    public function count(): int;
}
