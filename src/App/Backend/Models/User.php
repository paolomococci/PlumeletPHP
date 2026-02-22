<?php
declare (strict_types = 1); // Enforce strict type checking

namespace App\Backend\Models;

use App\Backend\Models\Interfaces\ModelInterface;
use DateTimeImmutable;
use InvalidArgumentException;

/**
 * User
 */
final class User extends Model implements ModelInterface
{
    // To avoid possible typing errors, the table name should be set in one place.
    const TABLE_NAME = 'plumeletphp_db.users_tbl';
    /**
     * __construct
     *
     * @return void
     *
     * A concise constructor syntax is achieved by using PHP 8.0+ property promotion,
     * which automatically declares and initializes class properties.
     *
     */
    public function __construct(
        private ?string $id = null,
        private ?string $name = null,
        private ?string $email = null,
        private ?string $password_plain = null,
        private ?string $password_hash = null,
        private ?string $created_at = null,
        private ?string $updated_at = null
    ) {}

    /**
     * getTableName
     *
     * @return string
     */
    public static function getTableName(): string
    {
        return self::TABLE_NAME;
    }

    /* getters */

    /**
     * getId
     *
     * @return int
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * getName
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * getEmail
     *
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * getPlainPassword
     *
     * @return string
     */
    public function getPlainPassword(): string
    {
        return $this->password_plain ?? '';
    }

    /**
     * getPlainPassword
     *
     * @return string
     */
    public function isPlainPasswordEmpty(): bool
    {
        return empty($this->password_plain);
    }

    /**
     * getHashedPassword
     *
     * @return string
     */
    public function getHashedPassword(): string
    {
        return $this->password_hash ?? '';
    }

    /**
     * getCreatedAt
     *
     * @return DateTimeImmutable
     */
    public function getCreatedAt(): DateTimeImmutable
    {
        return static::toDateTimeImmutable($this->created_at);
    }

    /**
     * getUpdatedAt
     *
     * @return DateTimeImmutable
     */
    public function getUpdatedAt(): DateTimeImmutable
    {
        return static::toDateTimeImmutable($this->updated_at);
    }

    /* setters */

    /**
     * setId
     *
     * @param  mixed $id
     * @return void
     */
    public function setId(mixed $id): void
    {
        $this->id = static::checkSerial($id);
    }

    /**
     * setName
     *
     * @param  mixed $name
     * @return void
     */
    public function setName(string $name): void
    {
        $this->name = static::checkVarchar(text: $name, length: 255);
    }

    /**
     * setEmail
     *
     * @param  mixed $email
     * @return void
     */
    public function setEmail(string $email): void
    {
        try {
            $this->email = static::checkEmail(email: $email, length: 255);
        } catch (InvalidArgumentException $iae) {
            echo $iae->getMessage();
        }
    }

    /**
     * setPlainPassword
     *
     * @param  mixed $plainPassword
     * @return void
     */
    public function setPlainPassword(string $plainPassword): void
    {
        $this->password_plain = $plainPassword;
    }

    /**
     * setHashedPassword
     *
     * @param  mixed $plainPassword
     * @return void
     */
    public function setHashedPassword(string $plainPassword): void
    {
        $this->password_hash = static::passwordHashWrapper($plainPassword);
    }

    /**
     * checkPassword
     *
     * @param  mixed $plainPassword
     * @param  mixed $storedHash
     * @return bool
     */
    public function checkPassword(string $plainPassword, string $storedHash): bool
    {
        return password_verify($plainPassword, $storedHash);
    }

    /**
     * withName
     *
     * @param  mixed $name
     * @return self
     */
    public function withName(string $name): self
    {
        return new self(
            id: $this->id,
            name: $name,
            email: $this->email,
            password_plain: '',
            password_hash: '',
            created_at: $this->created_at,
            updated_at: ''
        );
    }

    private function passwordHashWrapper(string $plainPassword): string
    {
        return password_hash($plainPassword, PASSWORD_BCRYPT);
    }
}
