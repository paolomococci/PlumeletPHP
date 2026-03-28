<?php

declare(strict_types=1); // Enforce strict type checking

namespace App\Backend\Repositories;

use App\Backend\Connections\PlumeletPhpDb;
use App\Backend\Models\Enums\AuthEnum;
use App\Backend\Models\Operator;
use App\Backend\Models\User;
use App\Backend\Repositories\Repository;
use PDO;
use PDOException;

/**
 * OperatorRepository
 */
class OperatorRepository extends Repository
{

    // To avoid possible typing errors, the table name should be set in one place.
    const OPERATORS_TABLE_NAME = Operator::TABLE_NAME;
    const USERS_TABLE_NAME     = User::TABLE_NAME;

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
     * create
     *
     * @param  Operator $operator
     * @return Operator
     */
    public function create(Operator $operator): ?Operator
    {
        $sql = static::cleanQuery(<<<'SQL'
            INSERT INTO %s (id, email)
            SELECT u.id, u.email FROM %s AS u WHERE id = :id AND email = :email
        SQL, self::OPERATORS_TABLE_NAME, self::USERS_TABLE_NAME);

        try {
            // Start the transaction.
            $this->pdo->beginTransaction();
            // parametrized SQL for create data to the database
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':id'    => $operator->getId(),
                ':email' => $operator->getEmail(),
            ]);
            // If the execution succeeds, commit the changes.
            $this->pdo->commit();
        } catch (PDOException $pe) {
            if ($this->pdo->inTransaction()) {
                // If something goes wrong, roll back the transaction and re-raise the exception.
                $this->pdo->rollBack();
            }
            return null;
        }

        return $this->read((string) $this->pdo->lastInsertId());
    }

    /**
     * read
     *
     * @param  string $id
     * @return Operator
     */
    public function read(string $id): ?Operator
    {
        $sql = static::cleanQuery(<<<'SQL'
            SELECT
                id, email, auth
            FROM %s
            WHERE id = :id LIMIT 1
        SQL, self::OPERATORS_TABLE_NAME);

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id' => $id]);

            // Fetch data.
            $stmt->setFetchMode(PDO::FETCH_ASSOC);
            $data = $stmt->fetch();

            if (! $data) {
                return null;
            }

            $operator = new Operator();
            $operator->setId((string) $data['id'])->setEmail($data['email'])->setAuth(AuthEnum::tryFrom($data['auth']));

            return $operator;
        } catch (PDOException $pe) {
            return null;
        }
    }

    /**
     * updateEmail
     *
     * @param  Operator $operator
     * @return Operator
     */
    public function updateEmail(Operator $operator): ?Operator
    {
        $sql = static::cleanQuery(<<<'SQL'
            UPDATE
                SET email = :email
            FROM %s
            WHERE id = :id
        SQL, self::OPERATORS_TABLE_NAME);

        try {
            // Start the transaction.
            $this->pdo->beginTransaction();
            // parametrized SQL for update data
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':id'    => $operator->getId(),
                ':email' => $operator->getEmail(),
            ]);
            // If the execution succeeds, commit the changes.
            $this->pdo->commit();
        } catch (PDOException $pe) {
            if ($this->pdo->inTransaction()) {
                // If something goes wrong, roll back the transaction and re-raise the exception.
                $this->pdo->rollBack();
            }
            return null;
        }

        return $this->read($operator->getId());
    }

    /**
     * updateAuth
     *
     * @param  Operator $operator
     * @return Operator
     */
    public function updateAuth(Operator $operator): ?Operator
    {
        $sql = static::cleanQuery(<<<'SQL'
            UPDATE
                SET auth = :auth
            FROM %s
            WHERE id = :id
        SQL, self::OPERATORS_TABLE_NAME);

        try {
            // Start the transaction.
            $this->pdo->beginTransaction();
            // parametrized SQL for update data
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':id'   => $operator->getId(),
                ':auth' => $operator->getAuth(),
            ]);
            // If the execution succeeds, commit the changes.
            $this->pdo->commit();
        } catch (PDOException $pe) {
            if ($this->pdo->inTransaction()) {
                // If something goes wrong, roll back the transaction and re-raise the exception.
                $this->pdo->rollBack();
            }
            return null;
        }

        return $this->read($operator->getId());
    }

    /**
     * delete
     *
     * @param  Operator $operator
     * @return bool
     */
    public function delete(Operator $operator): bool
    {
        $sql = static::cleanQuery(
            "DELETE FROM %s WHERE id = :id",
            self::OPERATORS_TABLE_NAME
        );

        try {
            $stmt = $this->pdo->prepare($sql);
            return (bool) $stmt->execute([':id' => $operator->getId()]);
        } catch (PDOException $pe) {
            return false;
        }
    }
}
