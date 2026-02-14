<?php
declare (strict_types = 1); // Enforce strict type checking

namespace App\Frontend\Services\Interfaces;

use App\Backend\Models\Interfaces\ModelInterface;

interface ServiceInterface
{
    public function index(): array;
    public function create(ModelInterface $model): String;
    public function read(String $id): ?ModelInterface;
    public function update(ModelInterface $model): ?ModelInterface;
    public function delete(String $id): bool;
    public function findByName(String $name): array;
    public function count(): int;
}
