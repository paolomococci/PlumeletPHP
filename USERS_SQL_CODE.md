# tier one `users`

## I create the table dedicated to `users`

```sql
-- `SHOW TABLES;` lists all tables in the current database.
SHOW TABLES;

-- Create the Users Table (if it does not exist).
CREATE TABLE IF NOT EXISTS users_tbl (
    -- `id SERIAL` creates a column named `id` that automatically increments.
    -- In MySQL/MariaDB, `SERIAL` is an alias for `BIGINT UNSIGNED NOT NULL AUTO_INCREMENT`.
    -- This column will serve as the primary key for the table.
    id SERIAL,
    -- Convention and power of 2 minus 1: 255 is 2^8−1, a common value for byte-oriented storage/compatibility.
    -- `name VARCHAR(255)` stores the user's name as a variable-length string up to 255 characters.
    -- `NOT NULL` ensures that every row must contain a name.
    name VARCHAR(255) NOT NULL,
    -- `email VARCHAR(255)` holds the user’s email address.
    -- `NOT NULL` guarantees that every user record has an email.
    email VARCHAR(255) NOT NULL,
    -- Password Hash.
    -- `password_hash VARCHAR(255)` stores the hashed password (e.g., using bcrypt, Argon2, etc.).
    -- `NOT NULL` requires that a password hash be present for each user.
    password_hash VARCHAR(255) NOT NULL,
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
    UNIQUE KEY ux_email (email)
);

-- Verify Table Contents, check that the table is clean.
-- This query also serves as a quick sanity check that the table was created successfully and is ready for use.
SELECT * FROM users_tbl;
```

### I create the table dedicated to logs for user registration

```sql
-- I create a log table that records the creation/registration of users.
CREATE TABLE IF NOT EXISTS user_registration_log_tbl (
    id SERIAL,
    email VARCHAR(255) NOT NULL,
    feedback VARCHAR(255) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- I verify that the log table is also clean.
SELECT * FROM user_registration_log_tbl;
```

### I create the table dedicated to logs that track modifications of user data

```sql
-- I create a log table that tracks the modification/updates of data for already-registered users.
CREATE TABLE IF NOT EXISTS user_update_log_tbl (
    id SERIAL,
    email VARCHAR(255) NOT NULL,
    feedback VARCHAR(255) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- I verify that the log table associated with user data modifications is also clean and functioning.
SELECT * FROM user_update_log_tbl;

-- I view the tables currently present in the database.
SHOW TABLES;
```

## I create the stored procedure for user registration

```sql
DELIMITER $$
  -- Procedure Header
  -- Defines a stored procedure named `sp_insert_user_on_users_tbl`.
  CREATE PROCEDURE sp_insert_user_on_users_tbl (
      -- It accepts three input parameters (`p_name`, `p_email`, `p_password_hash`) and returns the newly inserted user’s ID via the output parameter `p_new_id`.
      IN p_name VARCHAR(255),
      IN p_email VARCHAR(255),
      IN p_password_hash VARCHAR(255),
      OUT p_new_id BIGINT
  )
  BEGIN
      -- Variable Declarations
      -- `v_email_exists` tracks whether an email already exists in the table.
      DECLARE v_email_exists INT DEFAULT 0;
      -- `v_len_hash` stores the character length of the password hash for validation.
      DECLARE v_len_hash INT DEFAULT 0;
      -- `v_err_msg` holds a custom error message that can be logged if the procedure encounters an exception.
      DECLARE v_err_msg VARCHAR(255);

      -- General SQL Exception Handler
      -- If any SQL error occurs (including `SIGNAL` statements), this handler logs the error to `user_registration_log_tbl` and then re-throws the original exception to the caller.
      DECLARE EXIT HANDLER FOR SQLEXCEPTION
      BEGIN
          INSERT INTO user_registration_log_tbl(email, feedback, created_at)
          VALUES (p_email, IFNULL(v_err_msg, 'Unknown error'), NOW());
          RESIGNAL;
      END;

      -- Data Normalization
      -- Removes leading/trailing whitespace from all incoming values to avoid accidental validation failures.
      SET p_name = TRIM(p_name);
      SET p_email = TRIM(p_email);
      SET p_password_hash = TRIM(p_password_hash);

      -- Field Validation (Block 1)
      -- Checks that all required fields are non-empty and meet format requirements.
      -- Presence Check
      -- Ensures that none of the parameters are blank, otherwise signals a generic missing fields error.
      IF p_name = '' OR p_email = '' OR p_password_hash = '' THEN
          SET v_err_msg = 'Missing required field(s)';
          SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = v_err_msg;
      END IF;

      -- Name Length Check
      -- Validates that the name is between 2 and 255 characters.
      IF LENGTH(p_name) < 2 OR LENGTH(p_name) > 255 THEN
          SET v_err_msg = 'Invalid name format';
          SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = v_err_msg;
      END IF;

      -- Email Format Check
      -- Uses a regular expression to verify that the email follows a standard RFC-compliant pattern.
      IF p_email NOT REGEXP '^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$' THEN
          SET v_err_msg = 'Invalid email format';
          SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = v_err_msg;
      END IF;

      -- Password Hash Length Check
      -- Assumes a bcrypt hash, which is typically 60 characters.
      -- Rejects hashes that are too short or excessively long.
      SET v_len_hash = CHAR_LENGTH(p_password_hash);
      IF v_len_hash < 60 OR v_len_hash > 255 THEN
          SET v_err_msg = 'Password hash must be 60 characters (bcrypt)';
          SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = v_err_msg;
      END IF;

      -- Email Uniqueness Check (Block 2)
      -- Queries the `users_tbl` table to determine if the supplied email is already in use.
      -- If a duplicate exists, the procedure signals a “duplicate email” error.
      SELECT COUNT(*) INTO v_email_exists
      FROM users_tbl
      WHERE email = p_email;
      IF v_email_exists > 0 THEN
          SET v_err_msg = 'Email already registered';
          SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = v_err_msg;
      END IF;

      -- Insert the New User (Block 3)
      -- Performs the actual insertion once all validations pass.
      INSERT INTO users_tbl(name, email, password_hash)
      VALUES (p_name, p_email, p_password_hash);

      -- Success Log
      -- Records a success entry in the registration log table.
      INSERT INTO user_registration_log_tbl(email, feedback, created_at)
      VALUES (p_email, 'SUCCESS', NOW());

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
SHOW CREATE PROCEDURE sp_insert_user_on_users_tbl;

-- I run a test with dummy data.
CALL sp_insert_user_on_users_tbl(
    'John Doe',
    'john.doe@example.local',
    '$2y$12$JJzRd.BfJy6O.NCO9LFpOeb/.ogUo8RvYcACfmD/8BUztsBtS8DGq',
    @new_id
);

-- I verify that the newly inserted data has been correctly registered.
SELECT @new_id AS user_id;

-- I check that there have been no problems.
SHOW WARNINGS;
```

## I create the stored procedure dedicated to updating user data

```sql
-- stored procedure for updating the data of already registered users.
DELIMITER $$

CREATE PROCEDURE sp_update_user_on_users_tbl (
    IN  p_name VARCHAR(255),
    IN  p_email VARCHAR(255),
    IN  p_password_hash VARCHAR(255),
    OUT p_updated_id BIGINT
)
BEGIN
    -- Local variables keep track of whether the e-mail exists, the hash length, and any error message.
    DECLARE v_email_exists INT DEFAULT 0;
    DECLARE v_len_hash INT DEFAULT 0;
    DECLARE v_err_msg VARCHAR(255);

    -- Normalization trim whitespace to avoid accidental validation failures.
    SET p_name = TRIM(p_name);
    SET p_email = TRIM(p_email);
    SET p_password_hash = TRIM(p_password_hash);

    -- Validations each business rule (presence, name length, e-mail format, hash length) is checked.
    -- On failure the error is logged in `user_update_log_tbl` and a `SIGNAL` aborts execution.
    -- Check that no required field is empty.
    IF p_name = '' OR p_email = '' OR p_password_hash = '' THEN
        SET v_err_msg = 'Missing required field(s)';
        INSERT INTO user_update_log_tbl(email, feedback)
            VALUES (p_email, CONCAT('FAILURE: ', v_err_msg));
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = v_err_msg;
    END IF;

    -- Name length must be between 2 and 255 characters.
    IF LENGTH(p_name) < 2 OR LENGTH(p_name) > 255 THEN
        SET v_err_msg = 'Invalid name format';
        INSERT INTO user_update_log_tbl(email, feedback)
            VALUES (p_email, CONCAT('FAILURE: ', v_err_msg));
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = v_err_msg;
    END IF;

    -- Email must match a standard RFC-compliant pattern.
    IF p_email NOT REGEXP '^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$' THEN
        SET v_err_msg = 'Invalid email format';
        INSERT INTO user_update_log_tbl(email, feedback)
            VALUES (p_email, CONCAT('FAILURE: ', v_err_msg));
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = v_err_msg;
    END IF;

    -- Password hash must be 60 characters (typical for bcrypt).
    SET v_len_hash = CHAR_LENGTH(p_password_hash);
    IF v_len_hash < 60 OR v_len_hash > 255 THEN
        SET v_err_msg = 'Password hash must be 60 characters (bcrypt)';
        INSERT INTO user_update_log_tbl(email, feedback)
            VALUES (p_email, CONCAT('FAILURE: ', v_err_msg));
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = v_err_msg;
    END IF;

    -- Verify that the e-mail exists.
    SELECT COUNT(*) INTO v_email_exists
    FROM users_tbl
    WHERE email = p_email
    -- locks the row for the current transaction
    FOR UPDATE;

    IF v_email_exists = 0 THEN
        SET v_err_msg = 'No existing user to update';
        INSERT INTO user_update_log_tbl(email, feedback)
            VALUES (p_email, CONCAT('FAILURE: ', v_err_msg));
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = v_err_msg;
    END IF;

    -- Update the user record.
    UPDATE users_tbl
       SET name          = p_name,
           password_hash = p_password_hash
     WHERE email = p_email;

    -- ROW_COUNT() should always be > 0 here because we already verified that the e-mail exists, but this block is a defensive measure.
    IF ROW_COUNT() = 0 THEN
        SET v_err_msg = 'Update failed: no rows affected';
        INSERT INTO user_update_log_tbl(email, feedback)
            VALUES (p_email, CONCAT('FAILURE: ', v_err_msg));
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = v_err_msg;
    END IF;

    -- Log a successful update.
    INSERT INTO user_update_log_tbl(email, feedback)
        VALUES (p_email, 'SUCCESS');

    -- Retrieve the updated user id
    SELECT id INTO p_updated_id
      FROM users_tbl
      WHERE email = p_email
      -- not strictly necessary but guarantees a lock
      FOR UPDATE;

END$$
-- note: formatting can accidentally attach the semicolon to the word DELIMITER
DELIMITER ;

-- I verify its functionality by modifying the data of a dummy user.
SET @new_id := 0;
-- The following call should succeed.
CALL sp_update_user_on_users_tbl(
    'John Junior Doe',                                                  -- new name
    'johnjunior.doe@example.local',                                     -- e-mail (not modifiable)
    '$2y$12$JJzRd.BfJy6O.NCO9LFpOeb/.ogUo8RvYcACfmD/8BUztsBtS8DGq',     -- bcrypt hash of at least 60 characters and no more than 255
    @new_id                                                             -- OUTPUT
);

-- Check that the data has actually been updated.
SELECT @new_id AS updated_user_id;
SELECT * FROM users_tbl;

-- This other call should fail with an error because I attempt to modify the email.
CALL sp_update_user_on_users_tbl(
    'John Junior Doe',                                                  -- new name
    'johnjunior.doe@example.local',                                     -- e-mail (not modifiable)
    '$2y$12$9RfdaLC2ChS733bSbyLznetndBcCxd.12j5aSUKi7pSjkaDeLDybm',     -- bcrypt hash of at least 60 characters and no more than 255
    @new_id                                                             -- OUTPUT
);

-- Check the log table dedicated to updates.
SELECT * FROM user_update_log_tbl;
```

## some notes on bcrypt and how I generated the hash from the shell to record it in the `password_hash` field of the `users_tbl` table.

bcrypt has become the de-facto standard for storing passwords in databases, configuration files and more.

```shell
htpasswd --help
htpasswd -nbBC 12 "" "john.doe.password"
htpasswd -nbBC 12 "" "johnjunior.doe.password"
```
