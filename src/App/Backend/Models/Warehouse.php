<?php
declare (strict_types = 1); // Enforce strict type checking

namespace App\Backend\Models;

use App\Backend\Models\Interfaces\ModelInterface;
use DateTimeImmutable;

/**
 * Warehouse
 */
final class Warehouse extends Model implements ModelInterface
{
    private string $id;
    private string $name;
    private string $address;
    private string $position;
    private string $unit_measure;
    private float $quantity;
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
     * getUnitMeasure
     *
     * @return string
     */
    public function getUnitMeasure(): string
    {
        return $this->unit_measure;
    }

    /**
     * getPosition
     *
     * @return string
     */
    public function getPosition(): string
    {
        return $this->position;
    }

    /**
     * getQuantity
     *
     * @return float
     */
    public function getQuantity(): float
    {
        return self::checkPrice(price: $this->quantity, digits: 2);
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
}
