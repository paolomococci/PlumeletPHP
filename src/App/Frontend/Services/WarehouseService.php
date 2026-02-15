<?php
declare (strict_types = 1); // Enforce strict type checking

namespace App\Frontend\Services;

use App\Backend\Models\Interfaces\ModelInterface;
use App\Backend\Repositories\WarehouseRepository;
use App\Frontend\Services\Interfaces\ServiceInterface;

/**
 * WarehouseService
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
class WarehouseService implements ServiceInterface
{

    /**
     * __construct
     *
     * @param  mixed $warehouseRepository
     *
     * @return void
     *
     * A concise constructor syntax is achieved by using PHP 8.0+ property promotion,
     * which automatically declares and initializes class properties.
     *
     */
    public function __construct(protected WarehouseRepository $warehouseRepository)
    {}

    /**
     * index
     *
     * @return array
     */
    public function index(): array
    {
        return $this->warehouseRepository->index();
    }

    /**
     * create
     *
     * @param  mixed $model
     * @return string
     */
    public function create(ModelInterface $model): string
    {
        return $this->warehouseRepository->create($model);
    }

    /**
     * read
     *
     * @param  mixed $id
     * @return ModelInterface
     */
    public function read(string $id): ?ModelInterface
    {
        return $this->warehouseRepository->read($id);
    }

    /**
     * update
     *
     * @param  mixed $model
     * @return ModelInterface
     */
    public function update(ModelInterface $model): ?ModelInterface
    {
        return $this->warehouseRepository->update($model);
    }

    /**
     * delete
     *
     * @param  mixed $id
     * @return bool
     */
    public function delete(string $id): bool
    {
        return $this->warehouseRepository->delete($id);
    }

    /**
     * findByName
     *
     * @param  mixed $name
     * @return array
     */
    public function findByName(string $name): array
    {
        return $this->warehouseRepository->findByName($name);
    }

    /**
     * count
     *
     * @return int
     */
    public function count(): int
    {
        return $this->warehouseRepository->count();
    }
}
