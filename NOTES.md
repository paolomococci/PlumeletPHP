# developer notes

## setup of MariaDB

```shell
systemctl status mariadb
systemctl enable mariadb
systemctl start mariadb
systemctl status mariadb
id mysql
mysql_secure_installation
```

Since it is a clone of hologram-php this size is already set. Therefore, the improvement of data processing will be dealt with later, during the development phase of the application that the virtual machine will serve the user.

However, if the developer user needs permissions to create databases remotely it will be necessary to issue the following commands:

```shell
mariadb -u root -p
```

and from sql cli:

```sql
SHOW DATABASES;
SELECT `user`, `host`, `Grant_priv`, `Super_priv` FROM `mysql`.`user` ORDER BY `user` DESC;
```

If you believe that the previous type of access is too extensive, you can restrict it to a subnet:

```sql
SELECT PASSWORD('database_developer_password');
CREATE USER IF NOT EXISTS 'developer_username'@'192.168.1.0/24' IDENTIFIED BY PASSWORD 'database_developer_password_hash';
GRANT ALL ON *.* TO 'developer_username'@'192.168.1.0/24';
FLUSH PRIVILEGES;
SELECT `user`, `host`, `Grant_priv`, `Super_priv` FROM `mysql`.`user` ORDER BY `user` DESC;
```

or a specific host as you wish:

```sql
CREATE USER IF NOT EXISTS 'developer_username'@'192.168.1.1' IDENTIFIED BY PASSWORD 'database_developer_password_hash';
GRANT ALL ON *.* TO 'developer_username'@'192.168.1.1';
FLUSH PRIVILEGES;
SELECT `user`, `host`, `Grant_priv`, `Super_priv` FROM `mysql`.`user` ORDER BY `user` DESC;
```

and from `localhost`:

```sql
CREATE USER IF NOT EXISTS 'developer_username'@'localhost' IDENTIFIED BY PASSWORD 'database_developer_password_hash';
GRANT ALL ON *.* TO 'developer_username'@'localhost';
FLUSH PRIVILEGES;
SELECT `user`, `host`, `Grant_priv`, `Super_priv` FROM `mysql`.`user` ORDER BY `user` DESC;
```

## revoke the privileges

```sql
REVOKE SUPER ON *.* FROM 'developer_username'@'192.168.1.0/24';
FLUSH PRIVILEGES;
SELECT `user`, `host`, `password`, `Grant_priv`, `Super_priv` FROM `mysql`.`user` ORDER BY `user` DESC;
```

## drop user

```sql
DROP USER IF EXISTS 'developer_username'@'192.168.1.0/24';
FLUSH PRIVILEGES;
SELECT `user`, `host`, `password`, `Grant_priv`, `Super_priv` FROM `mysql`.`user` ORDER BY `user` DESC;
```