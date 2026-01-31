# tier one `warehouses`

## I create the table dedicated to `warehouses`

```sql
-- `SHOW TABLES;` lists all tables in the current database.
SHOW TABLES;

-- Create the Warehouse Table (if it does not exist).
CREATE TABLE IF NOT EXISTS warehouses_tbl (
    -- `id SERIAL` creates a column named `id` that automatically increments.
    -- In MySQL/MariaDB, `SERIAL` is an alias for `BIGINT UNSIGNED NOT NULL AUTO_INCREMENT`.
    -- This column will serve as the primary key for the table.
    id SERIAL,
    -- Record Name of warehouse.
    -- `NOT NULL` ensures that every row must contain a name.
    name VARCHAR(255) NOT NULL,
    -- Record Address of warehouse.
    -- Physical address of the warehouse, must be non-null
    address VARCHAR(255) NOT NULL,
    -- Record Email of warehouse.
    -- `email VARCHAR(255)` holds the warehouse’s email address.
    -- `NOT NULL` guarantees that every warehouse record has an email.
    email VARCHAR(255) NOT NULL,
    -- Record Creation Timestamp.
    -- `created_at DATETIME` records when the row was first inserted.
    -- `NOT NULL DEFAULT CURRENT_TIMESTAMP` automatically sets the current timestamp at insertion time.
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    -- Record Update Timestamp.
    -- `updated_at DATETIME` tracks the last time the row was modified.
    -- `NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP` sets the value to the current timestamp on insert and updates it automatically whenever the row changes.
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    -- Primary Key Constraint
    -- Declares `id` as the table’s primary key, ensuring uniqueness and indexing.
    PRIMARY KEY (id),
    -- Unique Email Constraint.
    -- `UNIQUE KEY ux_email (email)` creates a unique index on the `email` column, preventing duplicate email addresses across rows.
    -- The index is named `ux_email` for clarity.
    UNIQUE KEY ux_email (email),
    -- Ensure each address is unique across the table.
    -- `UNIQUE KEY ux_address (address)` creates a unique index on the `address` column, preventing duplicate address across rows.
    -- The index is named `ux_address` for clarity.
    UNIQUE KEY ux_address (address)
);

-- Verify Table Contents, check that the table is clean.
-- This query also serves as a quick sanity check that the table was created successfully and is ready for use.
SELECT * FROM warehouses_tbl;
```

### I create the table dedicated to logs for warehouse registration

```sql
-- I create a log table that records the creation/registration of warehouses.
CREATE TABLE IF NOT EXISTS warehouse_registration_log_tbl (
    id SERIAL,
    email VARCHAR(255) NOT NULL,
    feedback VARCHAR(255) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- I verify that the log table is also clean.
SELECT * FROM warehouse_registration_log_tbl;
```
