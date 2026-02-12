<?php
declare (strict_types = 1); // Enforce strict type checking

namespace App\Backend\Models;

use App\Backend\Models\Interfaces\ModelInterface;
use DateTimeImmutable;

/**
 * Item
 */
final class Item extends Model implements ModelInterface
{
    private string $id;
    private string $name;
    private string $description;
    private float $price;
    private string $currency;
    private string $created_at;
    private string $updated_at;

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
        return self::checkPrice(price: $this->price, digits: 2);
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
     * @param  mixed $name
     * @return void
     */
    public function setId(string $id): void
    {
        $this->id = self::checkSerial(serial: $id);
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
     * setDescription
     *
     * @param  mixed $description
     * @return void
     */
    public function setDescription(string $description): void
    {

        $this->description = self::checkVarchar(text: $description, length: 1020);
    }

    /**
     * setCurrency
     *
     * @param  mixed $name
     * @return void
     */
    public function setCurrency(string $currency): void
    {
        $this->currency = (!is_null($currency) ? self::checkVarchar(text: $currency, length: 255) : '');
    }

    /**
     * setPrice
     *
     * @param  mixed $price
     * @return void
     */
    public function setPrice(float $price): void
    {
        $this->price = self::checkPrice(price: $price, digits: 2);
    }
}
