<?php
declare (strict_types = 1); // Enforce strict type checking

namespace App\Frontend\Services\Interfaces;

use App\Backend\Models\Interfaces\ModelInterface;

interface ServiceInterface
{
    public function index(): array;
    public function create(ModelInterface $model): string;
    public function read(string $id): ?ModelInterface;
    public function update(ModelInterface $model): ?ModelInterface;
    public function delete(string $id): bool;
    public function findByName(string $name): array;
    public function count(): int;
}
