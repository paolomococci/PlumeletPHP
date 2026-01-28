# tier one `items`

## I create the table dedicated to `items`

```sql
-- `SHOW TABLES;` lists all tables in the current database.
SHOW TABLES;

-- Create the Users Table (if it does not exist).
CREATE TABLE IF NOT EXISTS items_tbl (
    -- `id SERIAL` creates a column named `id` that automatically increments.
    -- In MySQL/MariaDB, `SERIAL` is an alias for `BIGINT UNSIGNED NOT NULL AUTO_INCREMENT`.
    -- This column will serve as the primary key for the table.
    id SERIAL,
    -- Convention and power of 2 minus 1: 255 is 2^8−1, a common value for byte-oriented storage/compatibility.
    -- `name VARCHAR(255)` stores the item's name as a variable-length string up to 255 characters.
    -- `NOT NULL` ensures that every row must contain a name.
    name VARCHAR(255) NOT NULL,
    -- `description VARCHAR(255)` holds the item’s description address.
    -- `NOT NULL` guarantees that every item record has an description.
    description VARCHAR(1020) NOT NULL,
    -- Unit price.
    -- `price decimal(10, 2)` stores the unit price value.
    -- `DEFAULT 0.0` sets a default value of zero for each item.
    price decimal(10, 2) DEFAULT 0.0,
    -- Currency code (ISO 4217, e.g., USD, GBP, EUR)
    -- `currency CHAR(3) NOT NULL DEFAULT 'EUR'` stores the three-letter currency code that applies to the `price`.
    currency CHAR(3) NOT NULL DEFAULT 'EUR',
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
    -- Unique Name Constraint.
    -- `UNIQUE KEY ux_name (name)` creates a unique index on the `name` column, preventing duplicate name addresses across rows.
    -- The index is named `ux_name` for clarity.
    UNIQUE KEY ux_name (name),
    -- Full text index on Description field.
    -- `FULLTEXT INDEX idx_description_fulltext (description)` creates a full text index.
    -- The full text index is named `idx_description_fulltext` for clarity.
    FULLTEXT INDEX idx_description_fulltext (description)
);

-- Verify Table Contents, check that the table is clean.
-- This query also serves as a quick sanity check that the table was created successfully and is ready for use.
SELECT * FROM items_tbl;

-- Insertion of some dummy records.
INSERT INTO items_tbl (name, description, price, currency) VALUES
(
    'Full English Breakfast',
    'A hearty starter featuring fried eggs, sausages, baked beans, grilled tomatoes, black pudding, mushrooms and toast.',
    7.95,
    'GBP'
),
(
    'Yorkshire Pudding',
    'Light, airy baked pudding made from a simple batter of flour, eggs and milk, traditionally served with roast beef and gravy.',
    5.20,
    'GBP'
),
(
    'Cornish Pasty',
    'A savory pastry filled with beef, potatoes, swede (rutabaga), onions and seasoned with salt and pepper, originating from Cornwall.',
    6.80,
    'GBP'
),
(
    'Bangers and Mash',
    'Classic British dish of pork sausages served with creamy mashed potatoes, onion gravy and a sprinkle of parsley.',
    8.30,
    'GBP'
),
(
    'Fish and Chips',
    'Golden-fried cod or haddock served with thick potato chips, tartar sauce, lemon wedges and a side of mushy peas.',
    9.50,
    'GBP'
),
(
    'Beef Wellington',
    'Filet mignon coated with mushroom duxelles sauce and paté, wrapped in puff pastry, roasted to golden perfection and served with jus.',
    18.70,
    'GBP'
),
(
    'Steak and Kidney Pie',
    'Tender steak, kidney (usually beef), onions and a rich gravy encased in flaky pastry, a staple of Sunday lunches.',
    11.25,
    'GBP'
),
(
    'Corned Beef and Cabbage',
    'Cured beef brisket simmered with cabbage, carrots, potatoes and a hint of mustard, a popular Irish-British comfort food.',
    10.60,
    'GBP'
),
(
    'Scotch Broth',
    'A hearty, traditional Scottish stew made with lamb, kale, barley, carrots, potatoes and aromatic herbs, served hot in a fragrant bowl and accompanied by crusty bread.',
    7.90,
    'GBP'
),
(
    'Irish Stew',
    'A comforting traditional stew of tender lamb shoulder, carrots, onions, potatoes and a fragrant stock, slow-cooked to melt the flavours together, served hot with crusty bread.',
    7.50,
    'EUR'
),
(
    'Welsh Cawl',
    'A hearty Welsh soup featuring lamb or beef, leeks, carrots, potatoes and barley, simmered until the meat is silky and the vegetables are perfectly soft, finished with a sprinkle of fresh thyme.',
    8.10,
    'GBP'
),
(
    'Tourtière',
    'A traditional Quebecois meat pie made with finely minced pork (and sometimes beef or veal), seasoned with cinnamon, cloves and allspice, encased in a flaky pastry crust and baked until golden.',
    8.30,
    'CAD'
),
(
    'Hangi',
    'A traditional Māori earth-oven cooked meal where lamb, pork, sweet potatoes, pumpkin and cabbage are wrapped in leaves, buried in hot stones, and slowly roasted to a tender, smoky flavour, served hot straight from the pit.',
    9.75,
    'NZD'
),
(
    'Lamington',
    'A classic Australian dessert: a square of sponge cake coated in chocolate icing and rolled in desiccated coconut, often served with a dusting of icing sugar.',
    4.90,
    'AUD'
),
(
    'Eton Mess',
    'A delightful dessert of crushed meringue, fresh strawberries and whipped cream, named after Eton College.',
    5.40,
    'GBP'
),
(
    'Sticky Toffee Pudding',
    'Moist sponge cake made with dates, topped with a luscious toffee sauce, served with vanilla ice-cream or custard.',
    6.75,
    'GBP'
),
(
    'Lancashire Hotpot',
    'A slow-cooked casserole of lamb or mutton, onions, carrots and sliced potatoes, baked until golden.',
    9.80,
    'GBP'
),
(
    'Scone with Clotted Cream & Jam',
    'Traditional Scottish scone served warm, accompanied by thick clotted cream and a selection of strawberry and blackberry jam.',
    4.90,
    'GBP'
),
(
    'Scotch Egg',
    'A hard-boiled egg wrapped in sausage meat, coated in breadcrumbs and deep-fried to a crisp golden brown.',
    5.35,
    'GBP'
),
(
    'Cheddar Cheese Platter',
    'Assortment of aged cheddar cheeses served with crackers, fresh fruit, honey and chutney.',
    12.50,
    'GBP'
);

-- Verify Table Contents.
-- This query also serves as a quick sanity check that the table was created successfully and is ready for use.
SELECT id, name, price, currency FROM items_tbl;

-- Full-Text Search.
-- I verify the correct functioning of the full text search.
SELECT id, name, MATCH(description) AGAINST ('dessert' IN NATURAL LANGUAGE MODE) AS score
    FROM items_tbl 
    WHERE MATCH(description) AGAINST ('dessert' IN NATURAL LANGUAGE MODE) 
    ORDER BY score DESC;
SELECT id, name, MATCH(description) AGAINST ('lamb' IN NATURAL LANGUAGE MODE) AS score
    FROM items_tbl 
    WHERE MATCH(description) AGAINST ('lamb' IN NATURAL LANGUAGE MODE) 
    ORDER BY score DESC;

-- Create the procedure p_search_items_from_description
-- Direct parameters without `PREPARE`.
DELIMITER $$
    CREATE PROCEDURE p_search_items_from_description(
        -- the user-supplied search text
        IN p_search VARCHAR(255)
    )
    -- `proc_end` is a label so that the `LEAVE` statement can jump out of the procedure early.
    proc_end: BEGIN
        -- Declaration of the variable `v_search`.
        DECLARE v_search VARCHAR(255);
        -- Defensive coding - trim & sanity check
        -- If the input is NULL or empty, just exit (return no rows)
        IF p_search IS NULL OR TRIM(p_search) = '' THEN
            -- Empty result set.
            SELECT 0 AS id, '' AS name, 0 AS score WHERE FALSE;
            LEAVE proc_end;
        END IF;
        -- Trim the search string to 255 characters (the declared type)
        SET v_search = SUBSTRING(TRIM(p_search), 1, 255);

        -- Main logic - perform full-text search
        SELECT id, name, MATCH(description) AGAINST (v_search IN NATURAL LANGUAGE MODE) AS score
        FROM items_tbl
        WHERE MATCH(description) AGAINST (v_search IN NATURAL LANGUAGE MODE)
        ORDER BY score DESC;
    END$$
DELIMITER ;

-- I verify the status of the procedures.
SHOW PROCEDURE STATUS;

-- Call the procedure with a parameter.
CALL p_search_items_from_description('beef');

-- I clear the table of dummy data used as tests.
TRUNCATE TABLE items_tbl;

-- Verify Table Contents.
-- I check that the table is clean.
SELECT * FROM items_tbl;
```

### I create the table dedicated to logs for item registration

```sql
-- I create a log table that records the creation/registration of items.
CREATE TABLE IF NOT EXISTS item_registration_log_tbl (
    id SERIAL,
    name VARCHAR(255) NOT NULL,
    feedback VARCHAR(255) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- I verify that the log table is also clean.
SELECT * FROM item_registration_log_tbl;
```

### I create the table dedicated to logs that track modifications of item data

```sql
-- I create a log table that tracks the modification/updates of data for already-registered items.
CREATE TABLE IF NOT EXISTS item_update_log_tbl (
    id SERIAL,
    name VARCHAR(255) NOT NULL,
    feedback VARCHAR(255) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- I verify that the log table associated with item data modifications is also clean and functioning.
SELECT * FROM item_update_log_tbl;

-- I view the tables currently present in the database.
SHOW TABLES;
```

## I create the stored procedure for item registration

```sql
DELIMITER $$
  -- Procedure Header
  -- Defines a stored procedure named `sp_insert_item_on_items_tbl`.
  CREATE PROCEDURE sp_insert_item_on_items_tbl (
      -- It accepts four input parameters (`p_name`, `p_description`, `p_price`, `p_currency`) and returns the newly inserted item’s ID via the output parameter `p_new_id`.
      IN  p_name VARCHAR(255),
      IN  p_description VARCHAR(1020),
      IN  p_price DECIMAL(10,2),
      IN  p_currency CHAR(3),
      OUT p_new_id BIGINT
  )
  BEGIN
      -- Variable Declarations
      -- `v_name_exists` tracks whether an name already exists in the table.
      DECLARE v_name_exists INT DEFAULT 0;
      -- `v_err_msg` holds a custom error message that can be logged if the procedure encounters an exception.
      DECLARE v_err_msg VARCHAR(255);

      -- General SQL Exception Handler
      -- If any SQL error occurs (including `SIGNAL` statements), this handler logs the error to `item_registration_log_tbl` and then re-throws the original exception to the caller.
      DECLARE EXIT HANDLER FOR SQLEXCEPTION
      BEGIN
          INSERT INTO item_registration_log_tbl(name, feedback, created_at)
          VALUES (p_name, IFNULL(v_err_msg, 'Unknown error'), NOW());
          RESIGNAL;
      END;

      -- Data Normalization
      -- Removes leading/trailing whitespace from all incoming values to avoid accidental validation failures.
      SET p_name = TRIM(p_name);
      SET p_description = TRIM(p_description);

      -- Field Validation (Block 1)
      -- Checks that all required fields are non-empty and meet format requirements.
      -- Presence Check
      -- Ensures that none of the parameters are blank, otherwise signals a generic missing fields error.
      IF p_name = '' OR p_description = '' OR p_price IS NULL THEN
          SET v_err_msg = 'Missing required field(s)';
          SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = v_err_msg;
      END IF;

      -- Name Length Check
      -- Validates that the name is between 2 and 255 characters.
      IF LENGTH(p_name) < 2 OR LENGTH(p_name) > 255 THEN
          SET v_err_msg = 'Invalid name format';
          SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = v_err_msg;
      END IF;

      -- Description Check
      -- The description field can contain up to 1020 characters.
      IF CHAR_LENGTH(p_description) > 1020 THEN
        SET v_err_msg = 'Description too long (max 1020 chars)';
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = v_err_msg;
      END IF;

    -- Price Check
    -- Price value (must be a positive decimal).
      IF p_price < 0 THEN
        SET v_err_msg = 'Price must be non-negative';
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = v_err_msg;
      END IF;

      -- Description Uniqueness Check (Block 2)
      -- Queries the `items_tbl` table to determine if the supplied name is already in use.
      SELECT COUNT(*) INTO v_name_exists
        FROM items_tbl
        WHERE name = p_name;

      -- If a duplicate exists, the procedure signals a “duplicate name” error.
      IF v_name_exists > 0 THEN
        SET v_err_msg = 'Name already registered';
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = v_err_msg;
      END IF;

      -- Insert the New User (Block 3)
      -- Performs the actual insertion once all validations pass.
      INSERT INTO items_tbl(name, description, price, currency)
        VALUES (p_name, p_description, p_price, COALESCE(p_currency, 'EUR'));

      -- Success Log
      -- Records a success entry in the registration log table.
      INSERT INTO item_registration_log_tbl(name, feedback, created_at)
        VALUES (p_name, 'SUCCESS', NOW());

      -- Return the New User ID
      -- Assigns the auto-generated primary key of the newly inserted row to the output parameter `p_new_id`, allowing the caller to retrieve it.
      SET p_new_id = LAST_INSERT_ID();
  -- End of Procedure & Cleanup
  -- Closes the procedure body, restores the default delimiter, and finalizes the definition.
  END$$
DELIMITER ;

-- I verify the status of the procedures.
SHOW PROCEDURE STATUS;

-- I perform an initial check on the newly created stored procedure.
SHOW CREATE PROCEDURE sp_insert_item_on_items_tbl;

-- I run a test with dummy data.
-- The following call should return a duplicate error.
CALL sp_insert_item_on_items_tbl(
    'Cheddar Cheese Platter',
    'Assortment of aged cheddar cheeses served with crackers, fresh fruit, honey and chutney.',
    11.20,
    'GBP',
    @new_id
);
-- The following two calls should complete successfully.
CALL sp_insert_item_on_items_tbl(
    'New Cheddar Cheese Dish',
    'Assortment of aged cheddar cheeses served with crackers, fresh fruit, honey and chutney.',
    11.20,
    'GBP',
    @new_id
);
CALL sp_insert_item_on_items_tbl(
    'New Dish One',
    'Short description',
    3.20,
    NULL,
    @new_id
);

-- I verify that the newly inserted data has been correctly registered.
SELECT @new_id AS item_id;

-- I check that there have been no problems.
SHOW WARNINGS;

-- I verify the contents of the `items_tbl` table.
SELECT id, name, price, currency FROM items_tbl;
