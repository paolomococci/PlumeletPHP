<?php
declare (strict_types = 1); // Enforce strict type checking

namespace App\Backend\Models;

use App\Backend\Models\Enums\WarehouseType;
use App\Backend\Models\Interfaces\ModelInterface;
use DateTimeImmutable;
use InvalidArgumentException;

/**
 * Warehouse
 */
final class Warehouse extends Model implements ModelInterface
{
    private string $id;
    private string $name;
    private string $address;
    private string $email;
    private string $type;
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
     * getAddress
     *
     * @return string
     */
    public function getAddress(): string
    {
        return $this->address;
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
     * getType
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
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
     * setAddress
     *
     * @param  mixed $address
     * @return void
     */
    public function setAddress(string $address): void
    {
        $this->address = self::checkVarchar(text: $address, length: 255);
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
     * setType
     *
     * @param  mixed $type
     * @return void
     */
    public function setType(string $type): void
    {
        $this->type = WarehouseType::tryFrom($type)->value;
    }
}
