<?php
declare (strict_types = 1); // Enforce strict type checking

namespace App\Backend\Repositories;

use App\Backend\Connections\PlumeletPhpDb;
use App\Backend\Models\Interfaces\ModelInterface;
use App\Backend\Models\Warehouse;
use App\Backend\Repositories\Interfaces\RepositoryInterface;
use App\Errors\InternalServerError;
use InvalidArgumentException;
use PDO;

/**
 * WarehouseRepository
 *
 * In adherence to SOLID principles,
 * this module is responsible for database interactions,
 * which, in the present implementation,
 * are achieved through parameterized queries.
 *
 * In alternative scenarios,
 * interaction with external APIs may be required.
 *
 * The parameterized queries employed in this context
 * are specifically developed for compatibility with MySQL/MariaDB.
 * Furthermore, by leveraging the principle of polymorphism,
 * the `RepositoryInterface` can be injected into the `ItemService` class,
 * thereby facilitating the dynamic selection and utilization of repository
 * classes tailored to specific RDBMS implementations.
 *
 */
class WarehouseRepository extends Repository implements RepositoryInterface
{
    // To avoid possible typing errors, the table name should be set in one place.
    const TABLE_NAME = 'plumeletphp_db.warehouses_tbl';

    /** @var PDO Connection to the DB */
    protected PDO $pdo;

    public function __construct()
    {
        $this->pdo = PlumeletPhpDb::getPdo();
    }

    /**
     * index
     *
     * Retrieve all the items.
     *
     * @return array
     */
    public function index(): array
    {
        $sql = static::cleanQuery(<<<'SQL'
            SELECT id, name, address, email, type, created_at, updated_at
            FROM %s
        SQL, self::TABLE_NAME);

        $stmt = $this->pdo->query($sql);
        if ($stmt === false) {
            throw new InternalServerError(
                'Unable to retrieve warehouses from WarehouseRepository::index'
            );
        }

        $warehouses = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $warehouses[] = new Warehouse(
                (string) $row['id'],
                $row['name'],
                $row['address'],
                $row['email'],
                $row['type'],
                $row['created_at'],
                $row['updated_at']
            );
        }

        return $warehouses;
    }

    /**
     * create
     *
     * Create a new warehouse.
     *
     * @param  mixed $model
     * @return string
     */
    public function create(ModelInterface $model): string
    {
        if (! $model instanceof Warehouse) {
            throw new InvalidArgumentException('The provided model is not a Warehouse.');
        }

        $sql = static::cleanQuery(<<<'SQL'
            INSERT INTO %s
                (name, address, email, type)
            VALUES
                (:name, :address, :email, :type)
        SQL, self::TABLE_NAME);

        // parametrized SQL for create data to the database
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':name'    => $model->getName(),
            ':address' => $model->getAddress(),
            ':email'   => $model->getEmail(),
            ':type'    => $model->getType(),
        ]);

        return $this->pdo->lastInsertId();
    }

    /**
     * read
     *
     * Get an warehouse by its ID.
     *
     * @param  mixed $id
     * @return ModelInterface
     */
    public function read(string $id): ?ModelInterface
    {
        if ($id === '') {
            throw new InvalidArgumentException('Invalid ID for WarehouseRepository::read');
        }
        $sql = static::cleanQuery(<<<'SQL'
            SELECT id, name, address, email, type, created_at, updated_at
            FROM %s
            WHERE id = :id
        SQL, self::TABLE_NAME);

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (! $row) {
            return null;
        }

        return new Warehouse(
            (string) $row['id'],
            $row['name'],
            $row['address'],
            $row['email'],
            $row['type'],
            $row['created_at'],
            $row['updated_at']
        );
    }

    /**
     * update
     *
     * Update an existing warehouse.
     *
     * @param  mixed $model
     * @return ModelInterface
     */
    public function update(ModelInterface $model): ?ModelInterface
    {
        if (! $model instanceof Warehouse) {
            throw new InvalidArgumentException('The provided model is not a Warehouse.');
        }

        $sql = static::cleanQuery(<<<'SQL'
            UPDATE %s
                SET
                    name      = :name,
                    address   = :address,
                    email     = :email,
                    type      = :type
                WHERE id = :id
        SQL, self::TABLE_NAME);

        $params = [
            ':name'    => $model->getName(),
            ':address' => $model->getAddress(),
            ':email'   => $model->getEmail(),
            ':type'    => $model->getType(),
            ':id'      => $model->getId(),
        ];

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        // The repository returns the updated record.
        return $this->findById((string) $model->getId());
    }

    /**
     * delete
     *
     * Remove a warehouse by its ID.
     *
     * @param  mixed $id
     * @return bool
     */
    public function delete(string $id): bool
    {
        $sql = static::cleanQuery(
            "DELETE FROM %s WHERE id = :id",
            self::TABLE_NAME
        );

        return (bool) $this->pdo->prepare($sql)->execute([':id' => $id]);
    }

    /**
     * findByName
     *
     * Retrieve one or more warehouses based on the field name.
     *
     * @param  mixed $name
     * @return array
     */
    public function findByName(string $name): array
    {
        $sql = static::cleanQuery(<<<'SQL'
            SELECT * FROM %s
            WHERE name LIKE CONCAT('%', :name, '%')
        SQL, self::TABLE_NAME);

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':name' => '%' . $name . '%']);

        $warehouses = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $warehouses[] = new Warehouse(
                (string) $row['id'],
                $row['name'],
                $row['address'],
                $row['email'],
                $row['type'],
                $row['created_at'],
                $row['updated_at']
            );
        }

        return $warehouses;
    }

    /**
     * count
     *
     * Retrieve the total number of warehouses registered in the system.
     *
     * @return int
     */
    public function count(): int
    {
        $sql = static::cleanQuery("SELECT COUNT(*) FROM %s", self::TABLE_NAME);

        $stmt = $this->pdo->query($sql);
        if ($stmt === false) {
            throw new InternalServerError(
                'Unable to calculate the number of warehouses from WarehouseRepository::count.'
            );
        }

        return (int) ($stmt->fetchColumn() ?? 0);
    }

    /**
     * findById
     *
     * Alias ​​for searching by ID.
     *
     * @param  mixed $id
     * @return ModelInterface
     */
    public function findById(string $id): ?ModelInterface
    {
        return $this->read($id);
    }
}
