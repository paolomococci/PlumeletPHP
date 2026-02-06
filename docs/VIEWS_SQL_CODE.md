# # tier one `views`

**I need to clarify that the item values can be in different currencies, so the following views must be modified to take this detail into account.**

*Therefore, I reiterate that for the time being, queries, views, and other elements can undergo drastic changes.*

## some examples of views

```sql
-- List every item that lives in a given warehouse, 
-- together with its quantity and basic item data.
-- I'm creating the view `vw_every_item_that_lives_in_a_given_warehouse`.
CREATE OR REPLACE VIEW vw_every_item_that_lives_in_a_given_warehouse AS
SELECT w.id AS warehouse_id,
    w.name AS warehouse_name,
    i.id AS item_id,
    i.name AS item_name,
    i.price AS unit_price,
    wim.quantity AS qty_in_warehouse,
    wim.position AS location
FROM warehouses_tbl w
    JOIN warehouse_item_mtm wim ON w.id = wim.fk_warehouse
    JOIN items_tbl i ON i.id = wim.fk_item;
-- I'm using the view `vw_every_item_that_lives_in_a_given_warehouse`.
SELECT * FROM vw_every_item_that_lives_in_a_given_warehouse WHERE warehouse_id = 7 ORDER BY item_name;

-- Variant with total value (quantity * price).
-- I'm creating the view `vw_every_item_that_lives_in_a_given_warehouse_with_total_value`.
CREATE OR REPLACE VIEW vw_every_item_that_lives_in_a_given_warehouse_with_total_value AS
SELECT w.id AS warehouse_id,
    w.name AS warehouse_name,
    i.id AS item_id,
    i.name AS item_name,
    i.price AS unit_price,
    wim.quantity AS qty_in_warehouse,
    wim.position AS location,
    -- Overall value of the item.
    wim.quantity * i.price AS total_value
FROM warehouses_tbl w
    JOIN warehouse_item_mtm wim ON w.id = wim.fk_warehouse
    JOIN items_tbl i ON i.id = wim.fk_item;

-- I'm using the view `vw_every_item_that_lives_in_a_given_warehouse_with_total_value`
SELECT * FROM vw_every_item_that_lives_in_a_given_warehouse_with_total_value WHERE warehouse_id = 1 ORDER BY item_name;

-- Find all warehouses that store a particular item 
-- (for example, the item whose name is 'Hangi').
-- I'm creating the view `vw_item_positions`.
CREATE OR REPLACE VIEW vw_item_positions AS
SELECT 
    -- In order to maintain the alias `i.name` in the view, I'm adding a column `item_name`.
    i.name AS item_name,
    w.id AS warehouse_id,
    w.name AS warehouse_name,
    wim.position AS location,
    wim.quantity AS qty_in_warehouse,
    -- Overall value of the item.
    ROUND(wim.quantity * i.price, 3) AS total_value
FROM items_tbl i
    JOIN warehouse_item_mtm wim ON i.id = wim.fk_item
    JOIN warehouses_tbl w ON w.id = wim.fk_warehouse;

-- I'm retrieving the list of item names registered in the system.
SELECT name FROM items_tbl;
-- I'm using the view `vw_item_positions`.
SELECT *
    FROM vw_item_positions
    -- The original column is `i.name`, so I prefer to use an alias in a view.
    WHERE item_name = 'Hangi' 
        -- Optional.
        AND warehouse_id IS NOT NULL 
    ORDER BY warehouse_id;
-- Or, I'm using it in the following way:
SELECT * FROM vw_item_positions WHERE item_name = 'Irish Stew' ORDER BY warehouse_id;

-- Show the total value of inventory per warehouse.
-- The value is the sum of (quantity * unit price) for 
-- every item stored in that warehouse.
-- I'm creating the view `vw_warehouse_inventory_value`.
CREATE VIEW vw_warehouse_inventory_value AS
SELECT w.id AS warehouse_id,
    w.name AS warehouse_name,
    SUM(wim.quantity * i.price) AS inventory_value
FROM warehouses_tbl w
    JOIN warehouse_item_mtm wim ON w.id = wim.fk_warehouse
    JOIN items_tbl i ON i.id = wim.fk_item
GROUP BY w.id, w.name;

-- I'm using the view `vw_warehouse_inventory_value`.
SELECT * FROM vw_warehouse_inventory_value ORDER BY inventory_value DESC;

--For example, to see the value of warehouses with an inventory value greater than 10,000:
SELECT * FROM vw_warehouse_inventory_value WHERE inventory_value > 1000 ORDER BY inventory_value DESC;
```
