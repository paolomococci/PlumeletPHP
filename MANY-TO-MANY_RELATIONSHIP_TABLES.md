# many-to-many relationship tables

```sql
SHOW TABLES LIKE '%\_mtm';
```

### why an `ENUM` is often the better choice for a handful of fixed options

**Simplicity**, One column, no extra table or join needed.
**Schema-level validation**, Built-in MySQL/MariaDB constraint, only listed values are allowed.
**Performance**, Indexing is trivial, the enum values are stored as tiny integers internally.
**Readability**, Value is self-contained in the row, you see the unit directly.
**Maintenance**, Adding a new option means altering the column definition, rare for stable lists.
**Migration cost**, Simple `ALTER TABLE warehouse_item_mtm ADD COLUMN unit_measure ENUM(...);`.
**Use-case fit**, When the list is short, stable, and unlikely to grow beyond a few items (e.g., 'kilograms', 'liters', 'cubic_meter', 'pieces', 'pallet_spot').

An `ENUM` keeps the schema compact, enforces the constraint natively, and removes the need for an extra join.

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
    -- Unit of measure for the stored quantity.
    -- Possible values: 'kilograms', 'liters', 'pieces' and 'pallet_spot'.
    unit_measure ENUM('kilograms', 'liters', 'pieces', 'pallet_spot') NOT NULL DEFAULT 'pallet_spot',
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

-- Add the new `cubic_meter` option to the existing ENUM column.
-- The statement re-defines the column so that MySQL/MariaDB knows the extended list of allowed values.
ALTER TABLE warehouse_item_mtm 
    ADD COLUMN unit_measure ENUM('kilograms', 'liters', 'cubic_meter', 'pieces', 'pallet_spot') 
    NOT NULL DEFAULT 'pallet_spot';

-- Drop the `warehouse_item_mtm` table if it already exists (useful for clean re-creation)
DROP TABLE IF EXISTS warehouse_item_mtm;
```

### creating the same relation table without the need for subsequent changes for individual indexes

```sql
--- `warehouse_item_mtm` without requiring subsequent modifications for individual indexes
CREATE TABLE IF NOT EXISTS warehouse_item_mtm (
    -- Foreign key referencing the warehouse (bigint unsigned to match the id column in `warehouses_tbl`)
    fk_warehouse BIGINT UNSIGNED NOT NULL,
    -- Foreign key referencing the item (bigint unsigned to match the id column in items_tbl)
    fk_item BIGINT UNSIGNED NOT NULL,
    -- Optional position description for the item within the warehouse (e.g., shelf number, zone)
    position VARCHAR(255),
    -- Quantity of the item stored in the warehouse (decimal to support fractional units)
    quantity DECIMAL(10, 2),
    -- Unit of measure for the stored quantity.
    -- Possible values: 'kilograms', 'liters', 'pieces' and 'pallet_spot'.
    unit_measure ENUM('kilograms', 'liters', 'cubic_meter', 'pieces', 'pallet_spot') NOT NULL DEFAULT 'pallet_spot',
    -- Timestamp for when the row was first created; defaults to the current time
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    -- Timestamp for when the row was last updated; automatically refreshed on UPDATE
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    -- Composite primary key ensuring that each warehouse/item pair is unique
    PRIMARY KEY (fk_warehouse, fk_item),
    -- Index to speed up lookups that filter only by warehouse
    INDEX idx_warehouse (fk_warehouse),
    -- Index to speed up lookups that filter only by item
    INDEX idx_item (fk_item),
    -- Foreign key constraint linking fk_warehouse to the id column of `warehouses_tbl`
    CONSTRAINT wk_item_fk_warehouse FOREIGN KEY (fk_warehouse) REFERENCES warehouses_tbl(id) ON DELETE CASCADE ON UPDATE CASCADE,
    -- Foreign key constraint linking fk_item to the id column of items_tbl
    CONSTRAINT wk_item_fk_item FOREIGN KEY (fk_item) REFERENCES items_tbl(id) ON DELETE CASCADE ON UPDATE CASCADE
);

-- Retrieve all rows from the `warehouse_item_mtm` table to inspect its contents
SELECT * FROM warehouse_item_mtm;
```

## table that establishes a many-to-many relationship between users and items

```sql
-- Create a table that establishes a many-to-many relationship between users and items
--  (the pattern is identical to the `warehouse_item_mtm` example above)
CREATE TABLE IF NOT EXISTS user_item_mtm (
    -- Foreign key referencing the user (BIGINT UNSIGNED to match the id column in users_tbl)
    fk_user BIGINT UNSIGNED NOT NULL,
    -- Foreign key referencing the item (BIGINT UNSIGNED to match the id column in items_tbl)
    fk_item BIGINT UNSIGNED NOT NULL,
    -- Timestamp for when the row was first created; defaults to the current time
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    -- Timestamp for when the row was last updated; automatically refreshed on UPDATE
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    -- Composite primary key ensures that each user/item pair is unique
    PRIMARY KEY (fk_user, fk_item),
    -- Index to speed up lookups that filter only by user
    INDEX idx_user_item (fk_user),
    -- Foreign key constraint linking `fk_user` to the id column of `users_tbl`
    CONSTRAINT us_item_fk_user FOREIGN KEY (fk_user) REFERENCES users_tbl (id) ON DELETE CASCADE ON UPDATE CASCADE,
    -- Foreign key constraint linking `fk_item` to the id column of `items_tbl`
    CONSTRAINT us_item_fk_item FOREIGN KEY (fk_item) REFERENCES items_tbl (id) ON DELETE CASCADE ON UPDATE CASCADE
);

-- Retrieve all rows from the user_item_mtm table to inspect its contents
SELECT * FROM user_item_mtm;

-- Some optimizations to consider ensure that joins are fast:
CREATE INDEX idx_wim_fk_user ON user_item_mtm(fk_user);
CREATE INDEX idx_wim_fk_item ON user_item_mtm(fk_item);

-- Drop the user_item_mtm table if it already exists (useful for clean re-creation)
DROP TABLE IF EXISTS user_item_mtm;
```

### the same relation table without the need for subsequent changes for individual indexes

```sql
--- `user_item_mtm` without requiring subsequent modifications for individual indexes
CREATE TABLE IF NOT EXISTS user_item_mtm (
    -- Foreign key referencing the user (BIGINT UNSIGNED to match the id column in users_tbl)
    fk_user BIGINT UNSIGNED NOT NULL,
    -- Foreign key referencing the item (BIGINT UNSIGNED to match the id column in items_tbl)
    fk_item BIGINT UNSIGNED NOT NULL,
    -- Timestamp for when the row was first created; defaults to the current time
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    -- Timestamp for when the row was last updated; automatically refreshed on UPDATE
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    -- Composite primary key ensures that each user/item pair is unique
    PRIMARY KEY (fk_user, fk_item),
    -- Index to speed up lookups that filter only by user
    INDEX idx_user_item (fk_user),
    -- Index to speed up lookups that filter only by item
    INDEX idx_item_user (fk_item),
    -- Foreign key constraint linking fk_user to the id column of users_tbl
    CONSTRAINT us_item_fk_user FOREIGN KEY (fk_user) REFERENCES users_tbl(id) ON DELETE CASCADE ON UPDATE CASCADE,
    -- Foreign key constraint linking fk_item to the id column of items_tbl
    CONSTRAINT us_item_fk_item FOREIGN KEY (fk_item) REFERENCES items_tbl(id) ON DELETE CASCADE ON UPDATE CASCADE
);

-- List all relational tables in the current database (useful for verifying that user_item_mtm was created)
SHOW TABLES LIKE '%\_mtm';

-- Display any warnings or errors generated during the execution of the previous statements
SHOW WARNINGS;
```
