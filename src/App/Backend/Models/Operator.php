<?php

declare (strict_types = 1); // Enforce strict type checking

namespace App\Backend\Models;

use App\Backend\Models\Enums\AuthEnum;
use App\Backend\Models\Helpers\OperatorHelper;
use InvalidArgumentException;

/**
 * Operator
 *
 */
final class Operator
{
    use OperatorHelper;

    // To avoid possible typing errors, the table name should be set in one place.
    const TABLE_NAME = 'plumeletphp_db.operators_tbl';

    /**
     * __construct
     *
     * @return void
     */
    public function __construct(
        // The identifier will be identical to that of the user, in practice a foreign key.
        private ?string     $id = null,
        private ?string     $email = null,
        private ?AuthEnum   $auth = null
    ) {}

    /**
     * -------------------- getters -----------------
     */

    /**
     * getTableName
     *
     * @return string
     */
    public static function getTableName(): string
    {
        return self::TABLE_NAME;
    }

    /**
     * getId
     *
     * @return null|string
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * getEmail
     *
     * @return null|string
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * getAuth
     *
     * @return null|AuthEnum
     */
    public function getAuth(): ?AuthEnum
    {
        return $this->auth;
    }

    /**
     * getRole
     *
     * Role is the value of the authorization enum.
     *
     * @return null|string
     */
    public function getRole(): ?string
    {
        return $this->auth?->value;
    }

    /**
     * -------------------- setters -----------------
     */

    /**
     * setId
     *
     * @param  string $id
     * @return self
     */
    public function setId(string $id): self
    {
        $this->id = $id;
        return $this;
    }

    /**
     * setEmail
     *
     * @param  string $email
     * @return self
     */
    public function setEmail(string $email): self
    {
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Invalid email address.');
        }

        $this->email = $email;
        return $this;
    }

    /**
     * setAuth
     *
     * @param  AuthEnum $auth
     * @return self
     */
    public function setAuth(AuthEnum $auth): self
    {
        $this->auth = $auth;
        return $this;
    }

    /**
     * setRole
     *
     * @param  string $role
     * @return self
     */
    public function setRole(string $role): self
    {
        $this->auth = AuthEnum::tryFrom($role);
        return $this;
    }

    /**
     * -------------------- from object to array ----
     */

    /**
     * toArray
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id'    => $this->getId(),
            'email' => $this->getEmail(),
            'auth'  => $this->getRole(),
        ];
    }

    /**
     * -------------------- from array to object ----
     */

    /**
     * fromArray
     *
     * @param  array $data
     * @return Operator
     */
    public static function fromArray(array $data): Operator
    {
        $operator = new Operator();
        $operator->setId($data['id'] ?? null)
            ->setEmail($data['email'] ?? null)
            ->setAuth($data['auth'] ?? null);
        return $operator;
    }
}
