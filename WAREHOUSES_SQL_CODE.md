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

-- Adding a field to determine the warehouse type.
-- Advantages: Provides visibility into inventory held in non-owned warehouses, 
-- and facilitates batch tracking prior to physical handling. 
-- This allows for the rerouting or splitting of batches before they reach their intended destination.
-- Important: if records already exist in the table, the new field is set to its default value.
ALTER TABLE warehouses_tbl
    ADD COLUMN type ENUM('owned','supplier','currier') NOT NULL DEFAULT 'owned';

-- Checking the `type` field.
-- Key benefits include: eliminating the need for validation checks in stored procedures, and enabling query optimization within the database engine.
ALTER TABLE warehouses_tbl ADD CONSTRAINT chk_warehouse_type CHECK (type IN ('owned','supplier','currier'));

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

### I create the table dedicated to logs that track modifications of warehouse data

```sql
-- I create a log table that tracks the modification/updates of data for already-registered warehouses.
CREATE TABLE IF NOT EXISTS warehouse_update_log_tbl (
    id SERIAL,
    email VARCHAR(255) NOT NULL,
    feedback VARCHAR(255) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- I verify that the log table associated with warehouse data modifications is also clean and functioning.
SELECT * FROM warehouse_update_log_tbl;

-- I view the tables currently present in the database.
SHOW TABLES;
```

## I create the stored procedure for warehouse registration

```sql
-- I create the stored procedure for warehouse registration
DELIMITER $$
  -- Procedure Header
  -- Defines a stored procedure named `sp_insert_warehouse_on_warehouses_tbl`.
CREATE PROCEDURE sp_insert_warehouse_on_warehouses_tbl
(
    -- It accepts three input parameters (`p_name`, `p_address`, `p_email`)
    -- and returns the newly inserted warehouse’s ID via the output parameter `p_new_id`.
    IN  p_name VARCHAR(255),
    IN  p_address VARCHAR(255),
    IN  p_email VARCHAR(255),
    IN  p_type ENUM('owned','supplier','currier'),
    OUT p_new_id BIGINT
)
BEGIN
    -- Variable Declarations
    -- `v_email_exists` tracks whether an email already exists in the table.
    DECLARE v_email_exists TINYINT(1) DEFAULT 0;
    -- `v_address_exists` tracks whether an address already exists in the table.
    DECLARE v_address_exists TINYINT(1) DEFAULT 0;
    -- `v_err_msg` holds a custom error message that can be logged if the procedure encounters an exception.
    DECLARE v_err_msg VARCHAR(255);

      -- General SQL Exception Handler
      -- If any SQL error occurs (including `SIGNAL` statements), this handler logs the error to `warehouse_registration_log_tbl`
      -- and then re-throws the original exception to the caller.
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        INSERT INTO warehouse_registration_log_tbl
            (email, feedback, created_at)
        VALUES
            (p_email, IFNULL(v_err_msg, 'Unknown error'), NOW());
        -- Propagate any error so that the application that called the procedure takes note of it.
        RESIGNAL;
    END;


    -- Data Normalization
    -- Removes leading/trailing whitespace from all incoming values to avoid accidental validation failures.
    SET p_name = TRIM(p_name);
    SET p_address = TRIM(p_address);
    SET p_email = TRIM(p_email);

    -- If the `p_type` parameter is `NULL`, the default value is assigned.
    IF p_type IS NULL THEN
        SET p_type = 'owned';
    END IF;

    -- Field Validation
    -- Checks that all required fields are non-empty and meet format requirements.
    -- Presence Check
    -- Ensures that none of the parameters are blank, otherwise signals a generic missing fields error.
    IF p_name = '' OR p_address = '' OR p_email = '' THEN
        SET v_err_msg = 'Missing required field(s)';
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = v_err_msg;
    END IF;

    -- Name Length Check
    -- Validates that the name is between 2 and 255 characters.
    IF LENGTH(p_name) < 2 OR LENGTH(p_name) > 255 THEN
        SET v_err_msg = 'Invalid name format';
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = v_err_msg;
    END IF;

    -- Address Length Check
    -- Validates that the name is between 5 and 255 characters.
    IF LENGTH(p_address) < 5 OR LENGTH(p_address) > 255 THEN
        SET v_err_msg = 'Invalid address length';
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = v_err_msg;
    END IF;

    -- Address Uniqueness Check
    -- Queries the `warehouses_tbl` table to determine if the supplied address is already in use.
    -- If a duplicate exists, the procedure signals a “duplicate address” error.
    SELECT COUNT(*) INTO v_address_exists
    FROM warehouses_tbl
    WHERE address = p_address;
    IF v_address_exists > 0 THEN
        SET v_err_msg = 'Address already registered';
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = v_err_msg;
    END IF;

    -- Email Uniqueness Check
    -- Queries the `warehouses_tbl` table to determine if the supplied email is already in use.
    -- If a duplicate exists, the procedure signals a “duplicate email” error.
    SELECT COUNT(*) INTO v_email_exists
    FROM warehouses_tbl
    WHERE email = p_email;
    IF v_email_exists > 0 THEN
        SET v_err_msg = 'Email already registered';
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = v_err_msg;
    END IF;

    -- Email Format Check
    -- Uses a regular expression to verify that the email follows a standard RFC-compliant pattern.
    IF p_email NOT REGEXP '^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$' THEN
        SET v_err_msg = 'Invalid email format';
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = v_err_msg;
    END IF;

    -- Insert the New User
    -- Performs the actual insertion once all validations pass.
    INSERT INTO warehouses_tbl (name, address, email, type)
        VALUES (p_name, p_address, p_email, p_type);

    -- Success Log
    -- Records a success entry in the registration log table.
    INSERT INTO warehouse_registration_log_tbl
        (email, feedback, created_at)
        VALUES (p_email, 'SUCCESS', NOW());

    -- Return the New User ID
    -- Assigns the auto-generated primary key of the newly inserted row to the output parameter `p_new_id`, allowing the caller to retrieve it.
    SET p_new_id = LAST_INSERT_ID();
-- End of Procedure & Cleanup
-- Closes the procedure body.
END$$
-- Restores the default delimiter, and finalizes the definition.
DELIMITER ;

-- I verify the status of the procedures.
SHOW PROCEDURE STATUS LIKE 'sp_insert_warehouse%';
```

I'm testing the procedure I just created with some fictitious data:

```sql
-- I run a test with dummy data.
CALL sp_insert_warehouse_on_warehouses_tbl(
    'Fake Arcane Red Logistics',
    '1 Mystic Square, Enigma City',
    'fake-arcane.archival@warehouse.local',
    'owned',
    @new_id
);

-- I run a test with data fake.
CALL sp_insert_warehouse_on_warehouses_tbl(
    'Fake Delicacies Logistics Center',
    '5 Iron Way, Oxygen',
    'fake-delicacies.central@warehouse.local',
    'supplier',
    @new_id
);

-- I run a test with some imaginative data I invented.
CALL sp_insert_warehouse_on_warehouses_tbl(
    'Fake Crimson River Logistics',
    '7 Red Sandbar, Ember',
    'fake-crimson.river@warehouse.local',
    'currier',
    @new_id
);

-- Another test of dummy data entry.
CALL sp_insert_warehouse_on_warehouses_tbl(
    'Fake Aurora Northern Hub',
    '12 Frostbite Way, Glaciville',
    'fake-aurora.north@warehouse.local',
    'currier',
    @new_id
);

--  Dummy data entry.
CALL sp_insert_warehouse_on_warehouses_tbl(
    'Fake Harbor Warehouse', 
    '1 Harbor Road, Inexistent Harbor', 
    'fake-harbor@example.local', 
    'supplier',
    @new_id
);

--  Inserting fake data by passing NULL to the `p_type` parameter.
CALL sp_insert_warehouse_on_warehouses_tbl(
    'Fake Central Warehouse', 
    '10 Rome Road, Inexistent Cove', 
    'fake-central@example.local', 
    NULL, -- use default 'owned' value.
    @new_id
);

-- I verify that the newly inserted data has been correctly registered.
SELECT @new_id AS new_warehouse_id;
SELECT * FROM warehouses_tbl;
SELECT * FROM warehouse_registration_log_tbl;
```

## I create the stored procedure dedicated to updating warehouse data

```sql
-- I create the stored procedure dedicated to updating warehouse data
DELIMITER $$

-- Procedure Header
-- Defines a stored procedure that updates an existing warehouse.
-- The warehouse is identified by the primary-key value passed in `p_id`
-- and the new data (`p_name`, `p_address`, `p_email`) are supplied as input parameters.
CREATE PROCEDURE sp_update_warehouse_on_warehouses_tbl
(
    -- ID of the warehouse to update.
    IN  p_id BIGINT,
    -- New name.
    IN  p_name VARCHAR(255),
    -- New address.
    IN  p_address VARCHAR(255),
    -- New e-mail address.
    IN  p_email VARCHAR(255)
)
BEGIN
    -- Variable Declarations.
    DECLARE v_id_exists TINYINT(1) DEFAULT 0;
    DECLARE v_email_exists TINYINT(1) DEFAULT 0;
    DECLARE v_address_exists TINYINT(1) DEFAULT 0;
    DECLARE v_err_msg VARCHAR(255);

    -- General SQL Exception Handler
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        INSERT INTO warehouse_update_log_tbl
            (email, feedback, created_at)
        VALUES
            (p_email,
             IFNULL(v_err_msg, 'Unknown error'),
             NOW());
        -- Propagate the original error!
        RESIGNAL;
    END;

    -- Data Normalization.
    SET p_name = TRIM(p_name);
    SET p_address = TRIM(p_address);
    SET p_email = TRIM(p_email);

    -- Check missing required fields.
    IF p_id IS NULL OR p_name = '' OR p_address = '' OR p_email = '' THEN
        SET v_err_msg = 'Missing required field(s)';
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = v_err_msg;
    END IF;

    -- Check Name length.
    IF CHAR_LENGTH(p_name) < 2 OR CHAR_LENGTH(p_name) > 255 THEN
        SET v_err_msg = 'Invalid name format';
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = v_err_msg;
    END IF;

    -- Check Address length.
    IF CHAR_LENGTH(p_address) < 5 OR CHAR_LENGTH(p_address) > 255 THEN
        SET v_err_msg = 'Invalid address length';
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = v_err_msg;
    END IF;

    -- Check Email format.
    IF p_email NOT REGEXP '^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\\.[A-Za-z]{2,}$' THEN
        SET v_err_msg = 'Invalid email format';
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = v_err_msg;
    END IF;

    -- Existence Check: Is the warehouse `id` present?
    SELECT COUNT(*) INTO v_id_exists
    FROM warehouses_tbl
    WHERE id = p_id;

    IF v_id_exists = 0 THEN
        SET v_err_msg = CONCAT('Warehouse ID ', p_id, ' not found');
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = v_err_msg;
    END IF;

    -- Validation Email uniqueness (excluding current row).
    SELECT COUNT(*) INTO v_email_exists
    FROM warehouses_tbl
    WHERE email = p_email AND id <> p_id;

    IF v_email_exists > 0 THEN
        SET v_err_msg = 'Email already registered';
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = v_err_msg;
    END IF;

    -- Validation Address uniqueness (excluding current row).
    SELECT COUNT(*) INTO v_address_exists
    FROM warehouses_tbl
    WHERE address = p_address AND id <> p_id;

    IF v_address_exists > 0 THEN
        SET v_err_msg = 'Address already registered';
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = v_err_msg;
    END IF;

    -- Update the warehouse record.
    UPDATE warehouses_tbl
        SET name = p_name,
           address= p_address,
           email = p_email
        WHERE id = p_id;

    -- Log success.
    -- If no rows were actually changed we still record that the operation was executed (ROW_COUNT() will be 0).
    IF ROW_COUNT() > 0 THEN
        INSERT INTO warehouse_update_log_tbl
            (email, feedback, created_at)
        VALUES
            (p_email,
             CONCAT('SUCCESS: Updated ', ROW_COUNT(), ' row(s).'),
             NOW());
    ELSE
        INSERT INTO warehouse_update_log_tbl
            (email, feedback, created_at)
        VALUES
            (p_email,
             'SUCCESS: No changes needed (row already up-to-date)',
             NOW());
    END IF;
END$$

DELIMITER ;

-- I verify the status of the procedures.
SHOW PROCEDURE STATUS LIKE 'sp_update_warehouse%';
```

Update stored procedure that can be used as follows:

```sql
SELECT * FROM warehouses_tbl WHERE id = 1;

-- I run a test with dummy data with transaction.
START TRANSACTION;
    CALL sp_update_warehouse_on_warehouses_tbl(
        1,
        'Fake Red Water Logistics',
        '3 Mystic Square, Enigma City',
        'fake-red.archival@warehouse.local'
    );
COMMIT;

-- I verify that the updated data has been correctly stored.
SELECT * FROM warehouses_tbl WHERE id = 1;
SELECT * FROM warehouse_update_log_tbl;
```

### I create a stored procedure wrapper that can update an existing record or insert a new one

```sql
-- I'm checking if stored procedures exist for updating data.
SHOW PROCEDURE STATUS LIKE 'sp_update_%';

-- I create the wrapper stored procedure dedicated to updating or warehouse data registration.
DELIMITER $$

-- Procedure Header
-- 1. If a record exists with the indicated ID: 
--  - update the fields with the provided values; if a value is NULL, keep the current value.
-- 2. If the record is not found:
--  - when all required parameters are provided, a new entry is added;
--  - otherwise, an error will be logged in table `warehouse_update_log_tbl` and an exception will be raised.
CREATE PROCEDURE sp_update_or_insert_warehouse_data_on_warehouses_tbl
(
    -- The ID of the warehouse that needs updating.
    IN p_id BIGINT,
    -- New name: If NULL, the current value will be used.
    IN p_name VARCHAR(255),
    -- New address: If NULL, the current value will be used.
    IN p_address VARCHAR(255),
    -- New e-mail address: If NULL, the current value will be used.
    IN p_email VARCHAR(255)
)
BEGIN
    -- 1. Confirm the existence of the record.
    DECLARE v_record_exists TINYINT(1) DEFAULT 0;
    DECLARE v_current_name VARCHAR(255);
    DECLARE v_current_address VARCHAR(255);
    DECLARE v_current_email VARCHAR(255);
    -- This is used only when a new record needs to be inserted.
    DECLARE v_new_id BIGINT;
    -- Error notification message.
    DECLARE v_error_msg VARCHAR(1024);

    SELECT COUNT(*) INTO v_record_exists
    FROM warehouses_tbl
    WHERE id = p_id;

    IF v_record_exists = 1 THEN
        -- A record was found. Proceed with the update after retrieving the current values.
        SELECT name, address, email
          INTO v_current_name, v_current_address, v_current_email
          FROM warehouses_tbl
          WHERE id = p_id;

        -- Provide the values. If a value is NULL, the existing data in the record will be preserved.
        SET @upd_name = COALESCE(p_name, v_current_name);
        SET @upd_address = COALESCE(p_address, v_current_address);
        SET @upd_email = COALESCE(p_email, v_current_email);

        -- Call the procedure to update the record.
        CALL sp_update_warehouse_on_warehouses_tbl(
            p_id,
            @upd_name,
            @upd_address,
            @upd_email
        );

    ELSE
        -- 2. A record does not exist, and all mandatory fields have been supplied.
        IF p_name IS NOT NULL AND p_address IS NOT NULL AND p_email IS NOT NULL THEN
            -- It proceeds with the insertion.
            CALL sp_insert_warehouse_on_warehouses_tbl(
                p_name,
                p_address,
                p_email,
                v_new_id
            );
        ELSE
            -- Mandatory parameters are missing, logging and raise an error.
            INSERT INTO warehouse_update_log_tbl (email, feedback, created_at)
            VALUES (
                COALESCE(p_email, 'missing'),
                CONCAT('FAILURE: Insert failed, missing required field(s) for ID ', p_id),
                NOW()
            );

            -- Build the error message in a local variable.
            SET v_error_msg = CONCAT(
                  'Warehouse ID ', p_id,
                  ' not found and mandatory parameters are NULL.'
            );

            -- Raise a compatible error that propagates back to the caller.
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = v_error_msg;

        END IF;
    END IF;
-- End the procedure.
END$$

-- Restore the default delimiter.
DELIMITER ;

-- I verify the status of the procedures.
SHOW PROCEDURE STATUS LIKE 'sp_update_or_insert_warehouse_data_on_warehouses_tbl';
```

In the following examples I will test the wrapper procedure I just created:

```sql
-- Fetching the last record in the table.
SELECT * FROM warehouses_tbl ORDER BY id DESC LIMIT 1;

-- Trying to update a record that's not found results in a new record being created.
CALL sp_update_or_insert_warehouse_data_on_warehouses_tbl(
    5,
    'Fake Ice Water Logistics',
    '113 Ice Square, Enigma City',
    'fake-ice.archival@warehouse.local'
);

-- Updating the data for an existing record.
CALL sp_update_or_insert_warehouse_data_on_warehouses_tbl(
    1,
    'Fake Green Water Logistics',
    '23 Mystic Square, Enigma City',
    'fake-green.archival@warehouse.local'
);

-- Modifying an existing record by setting all fields to NULL effectively results in no changes.
CALL sp_update_or_insert_warehouse_data_on_warehouses_tbl(
    5,
    NULL,
    NULL,
    NULL
);

-- An error is expected, as I'm trying to update a record that doesn't exist, using all NULL values.
CALL sp_update_or_insert_warehouse_data_on_warehouses_tbl(
    6,
    NULL,
    NULL,
    NULL
);

-- I am confirming the recently made changes.
SELECT * FROM warehouses_tbl WHERE id = 1;
SELECT * FROM warehouse_update_log_tbl;
```

### a straightforward verification exercise to initialize some variables

```sql
SELECT 1, name, address, email
    INTO
        @v_record_exists,
        @v_current_name,
        @v_current_address,
        @v_current_email
    FROM warehouses_tbl WHERE id = 1 LIMIT 1;

SELECT @v_record_exists, @v_current_name, @v_current_address, @v_current_email;
```
