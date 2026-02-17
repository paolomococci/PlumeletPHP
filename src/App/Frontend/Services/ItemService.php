<?php
declare (strict_types = 1); // Enforce strict type checking

namespace App\Frontend\Services;

use App\Backend\Models\Interfaces\ModelInterface;
use App\Backend\Models\Item;
use App\Backend\Repositories\ItemRepository;
use App\Frontend\Services\Interfaces\ServiceInterface;
use App\Util\Search;

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

    /**
     * __construct
     *
     * @param  mixed $itemRepository
     *
     * @return void
     *
     * A concise constructor syntax is achieved by using PHP 8.0+ property promotion,
     * which automatically declares and initializes class properties.
     *
     */
    public function __construct(
        protected ItemRepository $itemRepository,
        protected Search $search
    ) {}

    /**
     * index
     *
     * @return array
     */
    public function index(): array
    {
        return $this->itemRepository->index();
    }

    /**
     * create
     *
     * @param  mixed $model
     * @return string
     */
    public function create(ModelInterface $model): string
    {
        return $this->itemRepository->create($model);
    }

    /**
     * read
     *
     * @param  mixed $id
     * @return ModelInterface
     */
    public function read(string $id): ?ModelInterface
    {
        return $this->itemRepository->read($id);
    }

    /**
     * update
     *
     * @param  mixed $model
     * @return ModelInterface
     */
    public function update(ModelInterface $model): ?ModelInterface
    {
        return $this->itemRepository->update($model);
    }

    /**
     * delete
     *
     * @param  mixed $id
     * @return bool
     */
    public function delete(string $id): bool
    {
        return $this->itemRepository->delete($id);
    }

    /**
     * findByName
     *
     * @param  mixed $name
     * @return array
     */
    public function findByName(string $name): array
    {
        return $this->itemRepository->findByName($name);
    }

    /**
     * count
     *
     * @return int
     */
    public function count(): int
    {
        return $this->itemRepository->count();
    }

    /**
     * paginate
     *
     * @param  mixed $page
     * @param  mixed $perPage
     * @return array
     */
    public function paginate(int $page, int $perPage): array
    {
        return $this->itemRepository->findAllPaginated($page, $perPage);
    }

    /**
     * searchByName
     *
     * @param  mixed $name
     * @param  mixed $page
     * @param  mixed $perPage
     * @return array
     */
    public function searchByName(string $name, int $page = 1, int $perPage = 5): array
    {
        return $this->search->byName(Item::class, $name, $page, $perPage);
    }

    /**
     * countByName
     *
     * @return int
     */
    public function countByName(string $name): int
    {
        return $this->search->countByName(Item::class, $name);
    }
}
