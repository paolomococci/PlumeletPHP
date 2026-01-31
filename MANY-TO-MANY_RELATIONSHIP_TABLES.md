# many-to-many relationship tables

```sql
SHOW TABLES LIKE '%\_mtm';
```

## table that establishes a many-to-many relationship between warehouses and items

```sql
-- Create a table that establishes a many-to-many relationship between warehouses and items
CREATE TABLE IF NOT EXISTS warehouse_item_mtm (
    -- Foreign key referencing the warehouse (bigint unsigned to match the id column in warehouses_tbl)
    fk_warehouse BIGINT UNSIGNED NOT NULL,
    -- Foreign key referencing the item (bigint unsigned to match the id column in `items_tbl`)
    fk_item BIGINT UNSIGNED NOT NULL,
    -- Optional position description for the item within the warehouse (e.g., shelf number, zone)
    position VARCHAR(255),
    -- Quantity of the item stored in the warehouse (decimal to support fractional units)
    quantity DECIMAL(10, 2),
    -- Timestamp for when the row was first created; defaults to the current time
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    -- Timestamp for when the row was last updated; automatically refreshed on UPDATE
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    -- Composite primary key ensuring that each warehouse/item pair is unique
    PRIMARY KEY (fk_warehouse, fk_item),
    -- Index to speed up lookups that filter only by warehouse
    INDEX idx_warehouse_item (fk_warehouse),
    -- Foreign key constraint linking fk_warehouse to the id column of warehouses_tbl
    CONSTRAINT wk_item_fk_warehouse FOREIGN KEY (fk_warehouse) REFERENCES warehouses_tbl (id) ON DELETE CASCADE ON UPDATE CASCADE,
    -- Foreign key constraint linking `fk_item` to the id column of `items_tbl`
    CONSTRAINT wk_item_fk_item FOREIGN KEY (fk_item) REFERENCES items_tbl (id) ON DELETE CASCADE ON UPDATE CASCADE
);

-- Retrieve all rows from the `warehouse_item_mtm` table to inspect its contents
SELECT * FROM warehouse_item_mtm;

-- Some optimizations to consider ensure that joins are fast:
CREATE INDEX idx_wim_fk_warehouse ON warehouse_item_mtm(fk_warehouse);
CREATE INDEX idx_wim_fk_item ON warehouse_item_mtm(fk_item);

-- Drop the `warehouse_item_mtm` table if it already exists (useful for clean re-creation)
DROP TABLE IF EXISTS warehouse_item_mtm;
```
