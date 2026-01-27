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
    -- `name VARCHAR(255)` stores the item's name as a variable‑length string up to 255 characters.
    -- `NOT NULL` ensures that every row must contain a name.
    name VARCHAR(255) NOT NULL,
    -- `description VARCHAR(255)` holds the item’s description address.
    -- `NOT NULL` guarantees that every item record has an description.
    description VARCHAR(1020) NOT NULL,
    -- Unit price.
    -- `price decimal(10, 2)` stores the unit price value.
    -- `DEFAULT 0.0` sets a default value of zero for each item.
    price decimal(10, 2) DEFAULT 0.0,
    -- Currency code (ISO 4217, e.g., USD, GBP, EUR)
    -- `currency CHAR(3) NOT NULL DEFAULT 'EUR'` stores the three‑letter currency code that applies to the `price`.
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
    'Golden‑fried cod or haddock served with thick potato chips, tartar sauce, lemon wedges and a side of mushy peas.',
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
    'Cured beef brisket simmered with cabbage, carrots, potatoes and a hint of mustard, a popular Irish‑British comfort food.',
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
    'A comforting traditional stew of tender lamb shoulder, carrots, onions, potatoes and a fragrant stock, slow‑cooked to melt the flavours together, served hot with crusty bread.',
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
    'A traditional Māori earth‑oven cooked meal where lamb, pork, sweet potatoes, pumpkin and cabbage are wrapped in leaves, buried in hot stones, and slowly roasted to a tender, smoky flavour, served hot straight from the pit.',
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
    'Moist sponge cake made with dates, topped with a luscious toffee sauce, served with vanilla ice‑cream or custard.',
    6.75,
    'GBP'
),
(
    'Lancashire Hotpot',
    'A slow‑cooked casserole of lamb or mutton, onions, carrots and sliced potatoes, baked until golden.',
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
    'A hard‑boiled egg wrapped in sausage meat, coated in breadcrumbs and deep‑fried to a crisp golden brown.',
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

-- I clear the table of dummy data used as tests.
TRUNCATE TABLE items_tbl;

-- Verify Table Contents.
-- I check that the table is clean.
SELECT * FROM items_tbl;
```
