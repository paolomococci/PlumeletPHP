<?php

declare(strict_types=1); // Enforce strict type checking

namespace App\Backend\Models;

use App\Backend\Models\Interfaces\ModelInterface;
use DateTimeImmutable;
use DateTimeZone;
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
        private ?string $updated_at = null,
        private ?string $token_2fa_hash = null,
        private ?string $token_2fa_expires_at = null,
        private ?string $token_2fa_used_at = null,
        private ?int $token_2fa_attempts = null
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
     * @return string
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

    /**
     * getHashedTwoFaToken
     *
     * @return string
     */
    public function getHashedTwoFaToken(): string
    {
        return $this->token_2fa_hash ?? '';
    }

    /**
     * isTokenTwoFaExpired
     *
     * @return bool
     */
    public function isTokenTwoFaExpired(): bool
    {
        if ($this->token_2fa_expires_at === null) {
            return true;
        }

        $now = new DateTimeImmutable('now', new DateTimeZone('UTC'));
        return ($this->token_2fa_expires_at < $now);
    }

    /**
     * getTokenTwoFaUsedAt
     *
     * @return null|DateTimeImmutable
     */
    public function getTokenTwoFaUsedAt(): ?DateTimeImmutable
    {
        if ($this->token_2fa_used_at === null) {
        }
        return ($this->token_2fa_used_at === null) ? null : static::toDateTimeImmutable($this->token_2fa_used_at);
    }

    /**
     * getTokenTwoFaAttempts
     *
     * @return int
     */
    public function getTokenTwoFaAttempts(): int
    {
        return ($this->token_2fa_attempts === null) ? 0 : $this->token_2fa_attempts;
    }

    /* setters */

    /**
     * setId
     *
     * @param  string $id
     * @return void
     */
    public function setId(string $id): void
    {
        $this->id = static::checkSerial($id);
    }

    /**
     * setName
     *
     * @param  string $name
     * @return void
     */
    public function setName(string $name): void
    {
        $this->name = static::checkVarchar(text: $name, length: 255);
    }

    /**
     * setEmail
     *
     * @param  string $email
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
     * @param  string $plainPassword
     * @return void
     */
    public function setPlainPassword(string $plainPassword): void
    {
        $this->password_plain = $plainPassword;
    }

    /**
     * setHashedPassword
     *
     * @param  string $plainPassword
     * @return void
     */
    public function setHashedPassword(string $plainPassword): void
    {
        $this->password_hash = static::passwordHashWrapper($plainPassword);
    }

    /**
     * checkPassword
     *
     * @param  string $plainPassword
     * @return bool
     */
    public function checkPassword(string $plainPassword): bool
    {
        return password_verify($plainPassword, $this->password_hash);
    }

    /**
     * withName
     *
     * @param  string $name
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

    /**
     * passwordHashWrapper
     *
     * @param  string $plainPassword
     * @return string
     */
    private function passwordHashWrapper(string $plainPassword): string
    {
        return password_hash($plainPassword, PASSWORD_BCRYPT);
    }

    /**
     * settHashedTwoFaToken
     *
     * @return string
     */
    public function setHashedTwoFaToken(): string
    {
        $passphrase           = bin2hex(random_bytes(16));
        $this->token_2fa_hash = password_hash($passphrase, PASSWORD_BCRYPT);
        return $passphrase;
    }

    /**
     * checkHashedTwoFaToken
     *
     * @param  string $passphrase
     * @return bool
     */
    public function checkHashedTwoFaToken(string $passphrase): bool
    {
        return password_verify($passphrase, $this->token_2fa_hash);
    }

    /**
     * setTokenTwoFaAttempts
     *
     * @param  int $id
     * @return void
     */
    public function setTokenTwoFaAttempts(int $token_2fa_attempts): void
    {
        $this->token_2fa_attempts = (filter_var($token_2fa_attempts, FILTER_VALIDATE_INT) !== false) ? filter_var($token_2fa_attempts, FILTER_VALIDATE_INT) : 1;
    }
}
