<?php
declare (strict_types = 1); // Enforce strict type checking

namespace App\Backend\Models;

use App\Backend\Models\Interfaces\ModelInterface;
use App\Backend\Models\Model;
use DateTimeImmutable;

/**
 * Item
 */
final class Item extends Model implements ModelInterface
{
    // To avoid possible typing errors, the table name should be set in one place.
    const TABLE_NAME = 'plumeletphp_db.items_tbl';

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
        private ?string $id,
        private string $name,
        private string $description,
        private float $price,
        private string $currency,
        private ?string $created_at,
        private ?string $updated_at
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
     * getDescription
     *
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * getCurrency
     *
     * @return string
     */
    public function getCurrency(): string
    {
        return $this->currency ?? '';
    }

    /**
     * getPrice
     *
     * @return float
     */
    public function getPrice(): float
    {
        return static::checkPrice(price: $this->price, digits: 2);
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
     * @param  mixed $name
     * @return void
     */
    public function setId(string $id): void
    {
        $this->id = static::checkSerial(serial: $id);
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
     * setDescription
     *
     * @param  mixed $description
     * @return void
     */
    public function setDescription(string $description): void
    {

        $this->description = static::checkVarchar(text: $description, length: 1020);
    }

    /**
     * setCurrency
     *
     * @param  mixed $name
     * @return void
     */
    public function setCurrency(string $currency): void
    {
        $this->currency = (! is_null($currency) ? static::checkVarchar(text : $currency, length: 255): '');
    }

    /**
     * setPrice
     *
     * @param  mixed $price
     * @return void
     */
    public function setPrice(float $price): void
    {
        $this->price = static::checkPrice(price: $price, digits: 2);
    }
}
