# tier one `batches`

**For now, this batch management approach is just a draft to develop some features. Later, I might radically change my approach.**

## I create the table dedicated to `batches`

```sql
-- lists all tables in the current database.
SHOW TABLES;

-- Table dedicated to batches.
CREATE TABLE IF NOT EXISTS batches_tbl (
    -- This column will serve as the primary key for the table.
    id SERIAL,
    -- Foreign key referencing the warehouse (bigint unsigned to match the id column in `warehouses_tbl`)
    fk_warehouse BIGINT UNSIGNED NOT NULL,
    -- Foreign key referencing the item (bigint unsigned to match the id column in items_tbl)
    fk_item BIGINT UNSIGNED NOT NULL,
    -- Date on which the batch was created or received.
    batch_date DATE NOT NULL,
    -- Sequence number.
    batch_seq INT UNSIGNED NOT NULL,
    -- Expiry date of the batch.
    expire_at DATE NOT NULL,
    -- Record Creation Timestamp.
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    -- Record Creation Timestamp.
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_batch (fk_warehouse, fk_item, batch_date, batch_seq),
    CONSTRAINT fk_batch_warehouse FOREIGN KEY (fk_warehouse)
        REFERENCES warehouses_tbl(id) ON DELETE CASCADE,
    CONSTRAINT fk_batch_item FOREIGN KEY (fk_item)
        REFERENCES items_tbl(id) ON DELETE CASCADE,
    -- Primary Key Constraint
    -- Declares `id` as the table’s primary key, ensuring uniqueness and indexing.
    PRIMARY KEY (id)
);

-- Verify Table Contents, check that the table is clean.
-- This query also serves as a quick sanity check that the table was created successfully and is ready for use.
SELECT * FROM batches_tbl;

-- Trigger to uniquely generate a batch.
DELIMITER $$

CREATE TRIGGER trg_wi_before_ins
BEFORE INSERT ON warehouse_item_mtm
FOR EACH ROW
BEGIN
    DECLARE v_next_seq INT;
    DECLARE v_batch_id BIGINT UNSIGNED;

    -- Compute the next `batch_seq` based on the provided (warehouse, item, date).
    SELECT IFNULL(MAX(batch_seq), 0) + 1
      INTO v_next_seq
      FROM batches_tbl
     WHERE fk_warehouse = NEW.fk_warehouse
       AND fk_item      = NEW.fk_item
       AND batch_date   = CURDATE()
       -- Lock the rows that are being changed.
     FOR UPDATE;

    -- Add the new batch to the system.
    INSERT INTO batches_tbl
        (fk_warehouse, fk_item, batch_date, batch_seq, expire_at)
    VALUES
        (NEW.fk_warehouse,
         NEW.fk_item,
         CURDATE(),
         v_next_seq,
         -- For now, record the new batch, setting the expiration date to 30 days.
         DATE_ADD(CURDATE(), INTERVAL 30 DAY));

    -- Get the ID value that was just inserted.
    SET v_batch_id = LAST_INSERT_ID();

    -- Relate the warehouse_item_mtm record to the newly created batch record.
    SET NEW.fk_batch = v_batch_id;
-- End of Procedure & Cleanup
-- Closes the procedure body.
END$$
-- Restores the default delimiter, and finalizes the definition.
DELIMITER ;
```
