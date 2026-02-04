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
    private string $id;
    private string $name;
    private string $email;
    private string $password_plain;
    private string $password_hash;
    private string $created_at;
    private string $updated_at;

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
        return self::toDateTimeImmutable($this->created_at);
    }

    /**
     * getUpdatedAt
     *
     * @return DateTimeImmutable
     */
    public function getUpdatedAt(): DateTimeImmutable
    {
        return self::toDateTimeImmutable($this->updated_at);
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
        $this->id = self::checkSerial($id);
    }

    /**
     * setName
     *
     * @param  mixed $name
     * @return void
     */
    public function setName(string $name): void
    {
        $this->name = self::checkVarchar(text: $name, length: 255);
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
            $this->email = self::checkEmail(email: $email, length: 255);
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
        $this->password_hash = self::passwordHashWrapper($plainPassword);
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
            email: $this->email,
            name: $name,
            createdAt: $this->created_at,
        );
    }

    private function passwordHashWrapper(string $plainPassword): string {
        return password_hash($plainPassword, PASSWORD_BCRYPT);
    }
}
