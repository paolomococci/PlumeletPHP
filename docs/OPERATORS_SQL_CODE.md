# tier one `operators`

The operators table is closely related to the users table; therefore, it would be advisable to examine it using the following query:

```sql
SHOW TABLES;

SELECT
    k.COLUMN_NAME,
    k.CONSTRAINT_NAME,
    k.ORDINAL_POSITION,
    s.INDEX_NAME,
    s.NON_UNIQUE
FROM
    information_schema.KEY_COLUMN_USAGE AS k
LEFT JOIN
    information_schema.STATISTICS AS s
    ON k.TABLE_SCHEMA = s.TABLE_SCHEMA
    AND k.TABLE_NAME    = s.TABLE_NAME
    AND k.COLUMN_NAME   = s.COLUMN_NAME
WHERE
    k.TABLE_SCHEMA = DATABASE()
    AND k.TABLE_NAME = 'users_tbl';
```

## I create the table dedicated to `operators`

```sql
-- Create the Operators Table (if it does not exist).
CREATE TABLE IF NOT EXISTS operators_tbl (
    -- `id BIGINT` derived from table users_tbl
    id BIGINT UNSIGNED NOT NULL,
    -- `email VARCHAR(255)` holds the user’s email address
    email VARCHAR(255) NOT NULL,
    -- `auth`
    auth ENUM('admin','chief','employee') NOT NULL DEFAULT 'employee',
    CONSTRAINT fk_operator_user FOREIGN KEY (id) REFERENCES users_tbl(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
);

-- DROP TABLE IF EXISTS operators_tbl;
```

### example

Example of adding an operator:

```sql
-- INSERT
INSERT INTO operators_tbl (id, email, auth) VALUES (15, 'jenny.doe@example.local', 'chief');
-- INSERT SELECT
INSERT INTO operators_tbl (id, email, auth) SELECT u.id, u.email, 'admin' AS auth FROM users_tbl AS u WHERE id = 17;
-- INSERT SELECT
INSERT INTO operators_tbl (id, email, auth) SELECT u.id, u.email, 'employee' AS auth FROM users_tbl AS u WHERE id = 16 AND email = 'dolly.doe@example.local';
```

To verify the entries by sorting them by `auth`, using function FIELD():

```sql
SELECT auth, email, id FROM operators_tbl ORDER BY FIELD(auth, 'chief', 'admin', 'employee'), email;
```

Or in the following way, using CASE:

```SQL
SELECT auth, id, email FROM operators_tbl ORDER BY 
    CASE auth 
        WHEN 'chief' THEN 1 
        WHEN 'admin' THEN 2 
        WHEN 'employee' THEN 3 
        else 4 
    END, 
    email;
```
