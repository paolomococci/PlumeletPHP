# `plumeletphp`, distribution in a realistic development environment

## before any other operation

```shell
su -
cd /var/www/html/
mkdir plumeletphp && cd plumeletphp/
mkdir public && chmod --recursive 775 public/
chown --recursive developer_username:apache .
```

### parameter for generate keys:

Here is just an example of the parameters to keep on hand:

```text
[national_acronym]
[state]
[city]
plumeletphp.local
plumeletphp.local
plumeletphp.local
[webmaster@localhost]
```

It is obvious that the first three parameters must be appropriately valued.

Therefore I can proceed with the generation of the self-signed certificate without the passphrase thanks to the `-nodes` flag:

```shell
ls -al /etc/ssl/
sudo openssl req -new -x509 -nodes -days 365 -newkey rsa:2048 -keyout /etc/ssl/private/plumeletphp.key -out /etc/ssl/certs/plumeletphp.crt
sudo ls -al /etc/ssl/private/ | grep plumeletphp
sudo ls -al /etc/ssl/certs/ | grep plumeletphp
```

### file `/etc/httpd/conf.d/plumeletphp.local.conf`

```shell
sudo nano /etc/httpd/conf.d/plumeletphp.local.conf
```

```xml
<VirtualHost *:80>
        ServerAdmin webmaster@localhost
        ServerName plumeletphp.local
        ServerAlias www.plumeletphp.local
        DocumentRoot /var/www/html/plumeletphp/public
        Redirect permanent "/" "https://plumeletphp.local/"
</VirtualHost>

<VirtualHost *:443>
        ServerAdmin webmaster@localhost
        ServerName plumeletphp.local
        ServerAlias www.plumeletphp.local
        DocumentRoot /var/www/html/plumeletphp/public

        <Directory /var/www/html/plumeletphp/public>
                Options Indexes FollowSymLinks MultiViews
                AllowOverride All
                Require all granted
                DirectoryIndex index.php
        </Directory>

        SSLEngine on

        SSLCertificateFile /etc/ssl/certs/plumeletphp.crt
        SSLCertificateKeyFile /etc/ssl/private/plumeletphp.key

        ErrorLog /var/log/httpd/plumeletphp_error_log

        <FilesMatch "\.(cgi|shtml|phtml|php)$">
                SSLOptions +StdEnvVars
        </FilesMatch>
</VirtualHost>
```

### application scaffolding

With developer user credentials:

```shell
sudo apachectl configtest
sudo systemctl reload httpd
systemctl status httpd --no-pager
```

If I encounter any problems I can investigate with the following command:

```shell
tail -n 5 /var/log/httpd/plumeletphp_error_log
```

If you receive reports about file permission issues, you can proceed with the following commands:

```shell
sudo chmod -R 755 /var/www/html/plumeletphp/public/
sudo chmod -R 644 /var/www/html/plumeletphp/public/*.php
sudo restorecon -Rv /var/www/html/plumeletphp/
sudo systemctl restart httpd
systemctl status httpd --no-pager
systemctl status php-fpm --no-pager
cd /var/www/html/plumeletphp/
```

## set the database credentials correctly

Check connectivity and firewall status:

```shell
ping -c 3 192.168.XXX.XXX
sudo nmap -Pn 192.168.XXX.XXX
sudo nc -vz -w 3 192.168.XXX.XXX 22
sudo nc -vz -w 3 192.168.XXX.XXX 80
sudo nc -vz -w 3 192.168.XXX.XXX 3306
```

Connect to the system hosting the MariaDB, Apache, etc. servers:

```shell
ssh developer_username@192.168.XXX.XXX
```

Log in to the MariaDB server with the root account:

```shell
mariadb -u root -p
```

Grant the developer the appropriate permissions:

```sql
SELECT PASSWORD('developer_username_password');
CREATE USER IF NOT EXISTS 'developer_username'@'192.168.XXX.XXX' IDENTIFIED BY PASSWORD 'developer_username_password_hash_including_the_asterisk';
GRANT ALL ON *.* TO 'developer_username'@'192.168.XXX.XXX';
FLUSH PRIVILEGES;
SELECT `user`, `host`, `Grant_priv`, `Super_priv` FROM `mysql`.`user` ORDER BY `user` DESC;
```

Delete any inappropriate access:

```sql
DROP USER IF EXISTS 'old_developer_username'@'192.168.XXX.XXX';
FLUSH PRIVILEGES;
SELECT `user`, `host`, `Grant_priv`, `Super_priv` FROM `mysql`.`user` ORDER BY `user` DESC;
```
