<?php
declare (strict_types = 1); // Enforce strict type checking

namespace App\Backend\Repositories;

use App\Backend\Connections\PlumeletPhpDb;
use App\Backend\Models\Interfaces\ModelInterface;
use App\Backend\Models\Item;
use App\Backend\Repositories\Interfaces\RepositoryInterface;
use App\Errors\InternalServerError;
use InvalidArgumentException;
use PDO;

/**
 * ItemRepository
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
class ItemRepository implements RepositoryInterface
{
    const TABLE = 'plumeletphp_db.items_tbl';

    protected PDO $pdo;

    public function __construct()
    {
        $this->pdo = PlumeletPhpDb::getPdo();
    }

    /**
     * Retrieve all the items.
     */
    public function index(): array
    {

        $sql = <<<'SQL'
            SELECT
                id, name, description, price, currency, created_at, updated_at
                FROM plumeletphp_db.items_tbl
        SQL;

        $stmt = $this->pdo->query($sql);

        // When the query fails, $stmt will be false.
        if ($stmt === false) {
            throw new InternalServerError('Unable to fetch items from ItemRepository::index function.');
        }

        $items = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $item = new Item(
                (string) $row['id'],
                $row['name'],
                $row['description'],
                (float) $row['price'],
                $row['currency'],
                $row['created_at'],
                $row['updated_at']
            );
            $items[] = $item;
        }

        return $items;
    }

    /**
     * CCreate a new item.
     */
    public function create(ModelInterface $model): string
    {
        if ($model === null) {
            throw new InvalidArgumentException('You must provide a valid model.');
        }

        $sql = <<<'SQL'
            INSERT INTO plumeletphp_db.items_tbl
                (name, price, currency, description)
            VALUES
                (:name, :price, :currency, :description)
        SQL;

        // parametrized SQL for create data to the database
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':name'        => $model->getName(),
            ':price'       => $model->getPrice(),
            ':currency'    => $model->getCurrency(),
            ':description' => $model->getDescription(),
        ]);

        return $this->pdo->lastInsertId();
    }

    /**
     * Get an item by its ID.
     */
    public function read(string $id): ?ModelInterface
    {
        if ($id === null || $id === '') {
            throw new InvalidArgumentException('You must provide a valid ID.');
        }

        $sql = <<<'SQL'
            SELECT * FROM plumeletphp_db.items_tbl
            WHERE id = :id LIMIT 1
        SQL;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);

        // If the query fails, the value of $stmt will be false.
        if ($stmt === false) {
            throw new InternalServerError('Unable to fetch items from ItemRepository::read function.');
        }

        $stmt->setFetchMode(PDO::FETCH_ASSOC);

        $data = $stmt->fetchAll()[0];
        $item = new Item(
            (string) $data['id'],
            $data['name'],
            $data['description'],
            (float) $data['price'],
            $data['currency'],
            $data['created_at'],
            $data['updated_at'],
        );

        // fetchById() Always returns an array as a result.
        return $item ?? null;
    }

    /**
     * Update an existing item.
     */
    public function update(ModelInterface $model): ?ModelInterface
    {
        $id = $model->getId();

        if ($id === null || $id === '') {
            throw new InvalidArgumentException('Model must contain a valid ID for update.');
        }

        $sql = <<<'SQL'
            UPDATE plumeletphp_db.items_tbl
            SET name = :name,
                price = :price,
                currency = :currency,
                description = :description
            WHERE id = :id
        SQL;

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':id'          => $id,
                ':name'        => $model->getName(),
                ':price'       => $model->getPrice(),
                ':currency'    => $model->getCurrency(),
                ':description' => $model->getDescription(),
            ]);

            // If no rows were affected, `rowCount()` will return 0.
            if ($stmt->rowCount() > 0) {
                self::read($model->getId());
            }
        } catch (\PDOException $e) {
            // Log the error before re-throwing.
            error_log('UPDATE failed: ' . $e->getMessage());
            throw new InternalServerError('Unable to update item.');
        } finally {
            return null;
        }
    }

    /**
     * Remove an item by its ID.
     */
    public function delete(string $id): bool
    {
        if ($id === null || $id === '') {
            throw new InvalidArgumentException('You must provide a valid ID.');
        }

        $sql = <<<'SQL'
            DELETE FROM plumeletphp_db.items_tbl
            WHERE id = :id
        SQL;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);

        // Returns true if the item is deleted.
        return $stmt->rowCount() > 0;
    }

    /**
     * Retrieve one or more items based on the field name.
     */
    public function findByName(string $name): array
    {
        if ($name === null || $name === '') {
            throw new InvalidArgumentException('You must provide a valid name.');
        }

        $sql = <<<'SQL'
            SELECT * FROM plumeletphp_db.items_tbl
            WHERE name LIKE CONCAT('%', :name, '%')
        SQL;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':name' => $name]);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);

        $items = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $item = new Item(
                (string) $row['id'],
                $row['name'],
                $row['description'],
                (float) $row['price'],
                $row['currency'],
                $row['created_at'],
                $row['updated_at']
            );
            $items[] = $item;
        }

        return $items;
    }

    /**
     * Retrieve the total number of items registered in the system.
     */
    public function count(): int
    {

        $sql = <<<'SQL'
            SELECT COUNT(*) FROM plumeletphp_db.items_tbl
        SQL;

        $stmt = $this->pdo->prepare($sql);

        return (int) $stmt->fetchColumn();
    }
}
