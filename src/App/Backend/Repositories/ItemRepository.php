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
class ItemRepository extends Repository implements RepositoryInterface
{
    // To avoid possible typing errors, the table name should be set in one place.
    const TABLE_NAME = 'plumeletphp_db.items_tbl';

    /** @var PDO Connection to the DB */
    protected PDO $pdo;

    /**
     * __construct
     *
     * @return void
     */
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

        $sql = self::cleanQuery(<<<'SQL'
            SELECT
                id, name, description, price, currency, created_at, updated_at
                FROM %s
        SQL, self::TABLE_NAME);

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
     * create
     *
     * Create a new item.
     *
     * @param  mixed $model
     * @return string
     */
    public function create(ModelInterface $model): string
    {
        if ($model === null) {
            throw new InvalidArgumentException('You must provide a valid model.');
        }

        $sql = self::cleanQuery(<<<'SQL'
            INSERT INTO %s
                (name, price, currency, description)
            VALUES
                (:name, :price, :currency, :description)
        SQL, self::TABLE_NAME);
        // \App\Util\Handlers\VarDebugHandler::varDump($sql);

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
     * read
     *
     * Get an item by its ID.
     *
     * @param  mixed $id
     * @return ModelInterface
     */
    public function read(string $id): ?ModelInterface
    {
        if ($id === null || $id === '') {
            throw new InvalidArgumentException('You must provide a valid ID.');
        }

        $sql = self::cleanQuery(<<<'SQL'
            SELECT * FROM %s
            WHERE id = :id LIMIT 1
        SQL, self::TABLE_NAME);

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

        return $item ?? null;
    }

    /**
     * update
     *
     * Update an existing item.
     *
     * @param  mixed $model
     * @return ModelInterface
     */
    public function update(ModelInterface $model): ?ModelInterface
    {
        $id = $model->getId();

        if ($id === null || $id === '') {
            throw new InvalidArgumentException('Model must contain a valid ID for update.');
        }

        $sql = self::cleanQuery(<<<'SQL'
            UPDATE %s
            SET name = :name,
                price = :price,
                currency = :currency,
                description = :description
            WHERE id = :id
        SQL, self::TABLE_NAME);

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
     * delete
     * 
     * Remove an item by its ID.
     *
     * @param  mixed $id
     * @return bool
     */
    public function delete(string $id): bool
    {
        if ($id === null || $id === '') {
            throw new InvalidArgumentException('You must provide a valid ID.');
        }

        $sql = self::cleanQuery(<<<'SQL'
            DELETE FROM %s
            WHERE id = :id
        SQL, self::TABLE_NAME);

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);

        // Returns true if the item is deleted.
        return $stmt->rowCount() > 0;
    }

    /**
     * findByName
     * 
     * Retrieve one or more items based on the field name.
     *
     * @param  mixed $name
     * @return array
     */
    public function findByName(string $name): array
    {
        if ($name === null || $name === '') {
            throw new InvalidArgumentException('You must provide a valid name.');
        }

        $sql = self::cleanQuery(<<<'SQL'
            SELECT * FROM %s
            WHERE name LIKE CONCAT('%', :name, '%')
        SQL, self::TABLE_NAME);

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
     * count
     * 
     * Retrieve the total number of items registered in the system.
     *
     * @return int
     */
    public function count(): int
    {

        $sql = self::cleanQuery(<<<'SQL'
            SELECT COUNT(*) FROM %s
        SQL, self::TABLE_NAME);

        $stmt = $this->pdo->prepare($sql);

        return (int) $stmt->fetchColumn();
    }
}
