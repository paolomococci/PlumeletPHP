<?php
declare (strict_types = 1); // Enforce strict type checking

namespace App\Frontend\Services;

use App\Backend\Models\Interfaces\ModelInterface;
use App\Backend\Repositories\ItemRepository;
use App\Frontend\Services\Interfaces\ServiceInterface;

/**
 * ItemService
 *
 * Following SOLID principles,
 * the business logic should be kept separate,
 * the database interaction should be delegated
 * to the repository, and the appropriate response
 * should be returned to the controller.
 *
 * Truthfully, for the time being,
 * this class only acts as a bridge between the
 * controller and the repository;
 * we'll see what the future holds.
 *
 */
class ItemService implements ServiceInterface
{

    public function __construct(protected ItemRepository $itemRepository)
    {}

    public function index(): array
    {
        return $this->itemRepository->index();
    }

    public function create(ModelInterface $model): String
    {
        return $this->itemRepository->create($model);
    }

    public function read(String $id): ?ModelInterface
    {
        return $this->itemRepository->read($id);
    }

    public function update(ModelInterface $model): ?ModelInterface
    {
        return $this->itemRepository->update($model);
    }

    public function delete(String $id): bool
    {
        return $this->itemRepository->delete($id);
    }

    public function findByName(String $name): array
    {
        return $this->itemRepository->findByName($name);
    }

    public function count(): int
    {
        return $this->itemRepository->count();
    }
}
