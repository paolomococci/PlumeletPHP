<?php
declare (strict_types = 1); // Enforce strict type checking

namespace App\Backend\Repositories;

use App\Backend\Connections\PlumeletPhpDb;
use App\Backend\Models\Interfaces\ModelInterface;
use App\Backend\Models\User;
use App\Backend\Repositories\Interfaces\RepositoryInterface;
use App\Errors\InternalServerError;
use InvalidArgumentException;
use PDO;
use RuntimeException;

/**
 * UserRepository
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
class UserRepository extends Repository implements RepositoryInterface
{
    // To avoid possible typing errors, the table name should be set in one place.
    const TABLE_NAME = 'plumeletphp_db.users_tbl';

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
     * Retrieve all users.
     *
     * @return array
     */
    public function index(): array
    {
        $sql = static::cleanQuery(<<<'SQL'
            SELECT id, name, email, password_hash, created_at, updated_at
            FROM %s
        SQL, self::TABLE_NAME);

        $stmt = $this->pdo->query($sql);
        if ($stmt === false) {
            throw new InternalServerError(
                'Failed to fetch users from UserRepository::index'
            );
        }

        $users = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $users[] = new User(
                (string) $row['id'],
                $row['name'],
                $row['email'],
                null,
                null,
                $row['created_at'],
                $row['updated_at']
            );
        }

        return $users;
    }

    /**
     * create
     *
     * Create a new user.
     *
     * @param  mixed $model
     * @return string
     */
    public function create(ModelInterface $model): string
    {
        if (! $model instanceof User) {
            throw new InvalidArgumentException('The model provided is not a User.');
        }

        // Set the password hash.
        $model->setHashedPassword($model->getPlainPassword());

        $sql = static::cleanQuery(<<<'SQL'
            INSERT INTO %s (name, email, password_hash)
            VALUES (:name, :email, :password_hash)
        SQL, self::TABLE_NAME);

        // parametrized SQL for create data to the database
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':name'          => $model->getName(),
            ':email'         => $model->getEmail(),
            ':password_hash' => $model->getHashedPassword(),
        ]);

        return $this->pdo->lastInsertId();
    }

    /**
     * read
     *
     * Get an user by its ID.
     *
     * @param  mixed $id
     * @return ModelInterface
     */
    public function read(string $id): ?ModelInterface
    {
        if ($id === '') {
            throw new InvalidArgumentException('Invalid ID for UserRepository::read');
        }

        $sql = static::cleanQuery(<<<'SQL'
            SELECT id, name, email, password_hash, created_at, updated_at
            FROM %s
            WHERE id = :id
        SQL, self::TABLE_NAME);

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (! $row) {
            return null;
        }

        return new User(
            (string) $row['id'],
            $row['name'],
            $row['email'],
            null,
            null,
            $row['created_at'],
            $row['updated_at']
        );
    }

    /**
     * update TODO: I need to enter the user's old password verification before updating it!
     *
     * Update an existing user.
     *
     * In this case, the number of parameters to pass to
     * the query may vary depending on whether or not the
     * user chooses to change their password.
     *
     * @param  mixed $model
     * @return ModelInterface
     */
    public function update(ModelInterface $model): ?ModelInterface
    {
        if (! $model instanceof User) {
            throw new InvalidArgumentException('The model provided is not a User.');
        }

        // Prepare the always-present parameters.
        $params = [
            ':id'    => $model->getId(),
            ':name'  => $model->getName(),
            ':email' => $model->getEmail(),
        ];

        // It builds the list of columns to be set.
        $setParts = [
            'name      = :name',
            'email     = :email',
        ];

        // If a password was provided, it calculates the hash and adds it.
        if (! $model->isPlainPasswordEmpty()) {
            // Set the password hash.
            $model->setHashedPassword($model->getPlainPassword());
            $setParts[]               = 'password_hash = :password_hash';
            $params[':password_hash'] = $model->getHashedPassword();
        }

        // It creates the query for the update.
        $sql = static::cleanQuery(sprintf(
            'UPDATE %s SET %s WHERE id = :id',
            self::TABLE_NAME,
            implode(', ', $setParts)
        ));

        // Start the transaction.
        $this->pdo->beginTransaction();

        // Proceed with caution.
        try {
            // It executes the query.
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            // If the execution succeeds, commit the changes.
            $this->pdo->commit();
            // If something goes wrong, roll back the transaction and re-raise the exception.
        } catch (\Throwable $th) {
            $this->pdo->rollBack();
            throw $th;
        }

        return $stmt->rowCount() > 0 ? $model : null;
    }

    /**
     * delete
     *
     * Remove an user by its ID.
     *
     * @param  mixed $id
     * @return bool
     */
    public function delete(string $id): bool
    {
        if ($id === '') {
            throw new InvalidArgumentException('Invalid ID for UserRepository::delete');
        }

        $sql = static::cleanQuery(
            "DELETE FROM %s WHERE id = :id",
            self::TABLE_NAME
        );

        $stmt = $this->pdo->prepare($sql);
        return (bool) $stmt->execute([':id' => $id]);
    }

    /**
     * findByName
     *
     * Retrieve one or more users based on the field name.
     *
     * @param  mixed $name
     * @return array
     */
    public function findByName(string $name): array
    {
        if ($name === '') {
            throw new InvalidArgumentException('Invalid name for UserRepository::findByName');
        }

        $sql = static::cleanQuery(<<<'SQL'
            SELECT * FROM %s
            WHERE name LIKE CONCAT('%', :name, '%')
        SQL, self::TABLE_NAME);

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':name' => "%$name%"]);

        $users = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $users[] = new User(
                (string) $row['id'],
                $row['name'],
                $row['email'],
                null,
                $row['password_hash'],
                $row['created_at'],
                $row['updated_at']
            );
        }

        return $users;
    }

    /**
     * count
     *
     * Retrieve the total number of users registered in the system.
     *
     * @return int
     */
    public function count(): int
    {
        $sql = static::cleanQuery("SELECT COUNT(*) FROM %s", self::TABLE_NAME);

        $stmt = $this->pdo->prepare($sql);
        if (! $stmt->execute()) {
            // The following code is designed to handle any potential errors.
            $error = $stmt->errorInfo();
            throw new RuntimeException('Error query COUNT: ' . $error[2]);
        }

        return (int) $stmt->fetchColumn();
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
