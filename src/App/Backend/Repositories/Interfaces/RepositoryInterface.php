<?php
declare (strict_types = 1); // Enforce strict type checking

namespace App\Backend\Repositories\Interfaces;

use App\Backend\Models\Interfaces\ModelInterface;

/**
 * RepositoryInterface
 */
interface RepositoryInterface
{
    /**
     * Retrieve all the items.
     */
    public function index(): array;

    /**
     * Create a new item.
     */
    public function create(ModelInterface $model): string;

    /**
     * Get an item by its ID.
     */
    public function read(string $id): ?ModelInterface;

    /**
     * Modify an existing item.
     */
    public function update(ModelInterface $model): ?ModelInterface;

    /**
     * Remove an item by its ID.
     */
    public function delete(string $id): bool;

    /**
     * Retrieve items matching the name, including items with names that are similar.
     */
    public function findByName(string $name): array;

    /**
     * Retrieve the total number of items registered in the system.
     */
    public function count(): int;
}
