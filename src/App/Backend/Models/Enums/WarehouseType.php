<?php
declare (strict_types = 1); // Enforce strict type checking

namespace App\Backend\Models\Enums;

/**
 * Backed enum representing the type of a warehouse.
 *
 * The enum’s values match the `type` column in the `warehouses_tbl` table.
 */
enum WarehouseType: string {
    // Warehouse is owned by the company itself.
    case OWNED = 'owned';
    // Warehouse belongs to a supplier.
    case SUPPLIER = 'supplier';
    // Warehouse belongs to a courier.
    case CURRIER = 'currier';

    /**
     * Return a human‑readable label for the enum case.
     *
     * This is handy when you want to display the type in a UI 
     * (e.g. in a <select> element or a table).  
     * The function uses PHP 8.0's `match` expression to
     * map the enum case to a friendly string.
     *
     * @return string Human‑readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::OWNED    => 'Owned Warehouse',
            self::SUPPLIER => 'Supplier Warehouse',
            self::CURRIER  => 'Courier Warehouse',
        };
    }
}
