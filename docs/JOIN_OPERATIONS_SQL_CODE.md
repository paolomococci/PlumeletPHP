# # tier one `join operations`

**I need to clarify that the item values can be in different currencies, so the following join operations must be modified to take this detail into account.**

*Therefore, I reiterate that for the time being, queries, join operations, and other elements can undergo drastic changes.*

## some examples of joins to retrieve interesting information

```sql
-- List every item that lives in a given warehouse, 
-- together with its quantity and basic item data.
SELECT w.id AS warehouse_id,
    w.name AS warehouse_name,
    i.id AS item_id,
    i.name AS item_name,
    i.price AS unit_price,
    wim.quantity AS qty_in_warehouse,
    wim.position AS location
FROM warehouses_tbl w
    JOIN warehouse_item_mtm wim ON w.id = wim.fk_warehouse
    JOIN items_tbl i ON i.id = wim.fk_item
-- change to the warehouse you are interested in.
WHERE w.id = 1 
ORDER BY i.name;

-- Find all warehouses that store a particular item 
-- (for example, the item whose name is 'Beef Wellington').
SELECT w.id AS warehouse_id,
    w.name AS warehouse_name,
    wim.position AS location,
    wim.quantity AS qty_in_warehouse
FROM items_tbl i
    JOIN warehouse_item_mtm wim ON i.id = wim.fk_item
    JOIN warehouses_tbl w ON w.id = wim.fk_warehouse
WHERE i.name = 'Beef Wellington' -- replace with the desired item name
ORDER BY w.id;

-- Show the total value of inventory per warehouse.
-- The value is the sum of (quantity * unit price) for 
-- every item stored in that warehouse.
SELECT w.id AS warehouse_id,
    w.name AS warehouse_name,
    SUM(wim.quantity * i.price) AS inventory_value
FROM warehouses_tbl w
    JOIN warehouse_item_mtm wim ON w.id = wim.fk_warehouse
    JOIN items_tbl i ON i.id = wim.fk_item
GROUP BY w.id,
    w.name
ORDER BY inventory_value DESC;

-- Find items that are stored in more than one warehouse.
SELECT i.id AS item_id,
    i.name AS item_name,
    COUNT(DISTINCT wim.fk_warehouse) AS warehouse_count
FROM items_tbl i
    JOIN warehouse_item_mtm wim ON i.id = wim.fk_item
GROUP BY i.id,
    i.name
HAVING COUNT(DISTINCT wim.fk_warehouse) > 1
ORDER BY warehouse_count DESC;

-- List users who own items that are also stored in 
-- a specific warehouse (e.g., warehouse_id = 2).
SELECT DISTINCT u.id AS user_id,
    u.name AS user_name
FROM users_tbl u
    JOIN user_item_mtm ui ON u.id = ui.fk_user
    JOIN warehouse_item_mtm wim ON ui.fk_item = wim.fk_item 
-- change `id` to the warehouse of interest.
WHERE wim.fk_warehouse = 2;
```
