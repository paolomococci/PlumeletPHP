<?php

declare(strict_types=1); // Enable strict type checking

namespace App\Backend\Repositories\Helpers;

use App\Backend\Models\User;
use InvalidArgumentException;
use PDO;
use PDOException;

trait UserHelper
{
    /**
     * validateName
     *
     * Validates that the name is not empty.
     *
     * @param  string $name
     * @throws InvalidArgumentException
     *
     * @return void
     */
    final public static function validateName(string $name): void
    {
        if ($name === '') {
            throw new InvalidArgumentException(
                'Invalid name for UserRepository::findByName'
            );
        }
    }

    /**
     * validateEmail
     *
     * @param  string $email
     * @throws InvalidArgumentException
     *
     * @return void
     */
    final public static function validateEmail(string $email): void
    {
        if ($email === '') {
            throw new InvalidArgumentException(
                'Invalid e-mail for UserRepository::... value cannot be empty!'
            );
        }

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException(
                sprintf('Invalid e-mail format: "%s"', $email)
            );
        }
    }

    /**
     * runUpdate
     *
     * @param  PDO $pdo
     * @param  string $sql
     * @param  array $params
     *
     * @return bool
     */
    final public static function runUpdate(PDO $pdo, string $sql, array $params): ?bool
    {
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->rowCount() > 0;
        } catch (PDOException $pe) {
            return null;
        }
    }

    /**
     * runSelectRow
     *
     * @param  PDO $pdo
     * @param  string $sql
     * @param  array $params
     *
     * @return array
     */
    final public static function runSelectRow(PDO $pdo, string $sql, array $params): ?array
    {
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $rows = $stmt->rowCount();
            if ($rows < 1) {
                return null;
            }

            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $pe) {
            return null;
        }
    }

    /**
     * runSelectAll
     *
     * Run a SELECT that may return multiple rows.
     *
     * @param  PDO $pdo
     * @param  string $sql
     * @param  array $params
     * @throws \PDOException
     *
     * @return array associative array of rows
     */
    final public static function runSelectAll(PDO $pdo, string $sql, array $params): array
    {
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $pe) {
            throw $pe;
        }
    }

    /**
     * userFromRow
     *
     * Create a User entity from a DB row.
     *
     * @param  array $row
     *
     * @return User
     */
    final public static function userFromRow(array $row): User
    {
        return new User(
            (string) $row['id'],
            $row['name'],
            $row['email'],
            null, // passwordHash? we keep it null
            $row['password_hash'],
            $row['created_at'],
            $row['updated_at']
        );
    }

    /**
     * userFromTuple
     *
     * Create a User entity from a DB tuple.
     *
     * @param  array $tuple
     *
     * @return User
     */
    final public static function userFromTuple(array $tuple): User
    {
        return new User(
            (string) $tuple['id'],
            $tuple['name'],
            $tuple['email'],
            null,
            $tuple['password_hash'],
            $tuple['created_at'],
            $tuple['updated_at'],
            $tuple['token_2fa_hash'],
            $tuple['token_2fa_expires_at'],
            $tuple['token_2fa_used_at'],
            $tuple['token_2fa_attempts']
        );
    }
}
