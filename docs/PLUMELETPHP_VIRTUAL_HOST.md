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
        # Standard admin email address.
        ServerAdmin webmaster@localhost

        # Host names that will trigger this block.
        ServerName plumeletphp.local
        ServerAlias www.plumeletphp.local

        # All HTTP traffic is permanently redirected to HTTPS.
        Redirect permanent "/" "https://plumeletphp.local/"
</VirtualHost>

<VirtualHost *:443>
        # Standard admin email address.
        ServerAdmin webmaster@localhost

        # Host names that will trigger this block.
        ServerName plumeletphp.local
        ServerAlias www.plumeletphp.local

        # The directory that contains public files.
        DocumentRoot /var/www/html/plumeletphp/public

        # SSL configuration.
        SSLEngine on
        SSLCertificateFile /etc/ssl/certs/plumeletphp.crt
        SSLCertificateKeyFile /etc/ssl/private/plumeletphp.key

        # Error log for this virtual host.
        ErrorLog /var/log/httpd/plumeletphp_error_log

        # Make sure PHP gets the standard environment variables.
        <FilesMatch "\.(cgi|shtml|phtml|php)$">
                SSLOptions +StdEnvVars
        </FilesMatch>

        # Directory block of public folder.
        <Directory /var/www/html/plumeletphp/public>
                # Keep the front-controller rewrite rules inside .htaccess
                Options +Indexes +FollowSymLinks -MultiViews

                # Allow the .htaccess file to override configuration.
                AllowOverride All
                Require all granted

                # The default file that will be served if a request is made.
                DirectoryIndex index.php
        </Directory>
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
cd /var/www/html/plumeletphp/
rm -Rf vendor/ composer.lock
composer clear-cache
composer install
chown --recursive developer_username:apache .
chmod -R 755 /var/www/html/plumeletphp/
chmod -R 775 /var/www/html/plumeletphp/stores/
chmod -R 644 /var/www/html/plumeletphp/*.php
sudo restorecon -Rv /var/www/html/plumeletphp/
sudo systemctl restart httpd
systemctl status httpd --no-pager
systemctl status php-fpm --no-pager
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

To get both general and specific information about the current database connection in use:

```sql
SHOW VARIABLES LIKE 'max_connections';
SHOW STATUS LIKE 'Threads_connected';
SHOW PROCESSLIST;
SELECT CURRENT_USER();
quit
```

To connect as the user `developer_username` from a remote host:

```shell
mariadb -h 192.168.XXX.XXX -u developer_username -p
```

## make the ollama service accessible remotely

Optional step, only if you want to make the service accessible remotely:

```shell
nano /etc/systemd/system/ollama.service
```

add the host line after the service entry, as shown below:

```conf
[Service]
Environment="OLLAMA_HOST=0.0.0.0"
```

To then restart the service:

```shell
systemctl daemon-reload
systemctl restart ollama
systemctl status ollama --no-pager
```

Now it's time to open the tcp port `11434`:

```shell
systemctl status firewalld
firewall-cmd --permanent --zone=public --add-rich-rule 'rule family="ipv4" source address="192.168.1.0/24" port port=11434 protocol="tcp" accept'
firewall-cmd --reload
```

Finally, for completeness it would be a good idea to test the connectivity of the service:

```shell
nc -vz -w 3 192.168.XXX.XXX 11434
nmap -Pn 192.168.XXX.XXX -p 11434
```

_Of course, `192.168.XXX.XXX` is just a placeholder that must be replaced with the IP address actually in use._
