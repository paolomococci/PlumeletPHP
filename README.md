# PlumeletPHP

![](./images/plumeletphp_v0.0.3.png)

Professionally developed personal PHP framework for demonstration purposes.

Plumelet, a word that literally means a small feather or tuft of feathers.
I chose this word because it denotes the lightness of this demonstration framework.

This project is licensed under the MIT License.
See the License file for details.

The official MIT license website is an excellent resource for learning more.
You can visit the official MIT License website at the following link: <https://opensource.org/license/mit> or more generally: <https://opensource.org/licenses>

By visiting these websites and reading the information provided, you can gain a better understanding of the MIT License and how to use it effectively.

## example of screenshot

![](./images/PlumeletPHP_mobile_view.png)

## scaffolding

Here's a series of useful shell commands for scaffolding the project:

```shell
mkdir PlumeletPHP && cd PlumeletPHP
composer init --help
composer init --verbose --name=plumeletphp/app --description="demo framework" --type=project --license=MIT --autoload=src/App
```

If you make changes to the composer.json file, remember to correct them and then regenerate autoloading.

### autoload

Now is the time to regenerate the autoloading:

```shell
composer dump-autoload
```

## packages

Here I will start adding the dependencies that I consider necessary for now:

```shell
composer require guzzlehttp/psr7 httpsoft/http-emitter league/route php-di/php-di vlucas/phpdotenv filp/whoops monolog/monolog adhocore/jwt
```

Install `league/plates` separately, preferring a stable version:

```shell
composer require league/plates --prefer-stable
```

## PHP built-in web server

Now I proceed to start the built-in web server offered by PHP itself:

```shell
php -h
php -S localhost:8080 -t ./public/
```

## PHP interactive shell thanks readline extension

The PHP interactive shell can be useful in case you want to test some constructs of the programming language:

```shell
php -a
```

## how to create a local Git repository

**Commands to be typed on the development host.**

Create a `.gitignore` file:

```shell
nano .gitignore
```

Edit `.gitignore` file:

```txt
.vscode/
.notes/
vendor/
storage/
.env
```

Then give the following commands:

```shell
git --help
git init
git branch -m main
git status
git config user.email "developer@example.local"
git config user.name "developer"
git add .
git commit -m "initializing the local repository"
git tag -a v0.0.0 -m "starting version of clean repo"
git log
git checkout -b staging
git checkout -b draft
git checkout -b wip
git branch --list | wc -l
git branch --list
```

And, after each change, the cycle repeats:

```shell
git status
git add .
git commit -m "further adjustments"
git tag -a v0.0.1 -m "further adjustments"
git log
git checkout draft
git merge --no-ff wip -m "merge wip into draft"
git checkout staging
git merge --no-ff draft -m "merge draft into staging"
git checkout main
git merge --no-ff staging -m "merge staging into main"
git checkout wip
```

If something were to go wrong:

```shell
git reset --hard v0.0.0
```

## MariaDB database

Command to connect to the development database:

```shell
mariadb --user=developer_name --password --pager
```

## custom class for debugging

```php
\App\Util\Handlers\VarDebugHandler::varDump($var);
\App\Util\Handlers\VarDebugHandler::varExport($var);
```

## converting an SVG file into a favicon

```shell
convert -background none plumeletphp_ico.svg -resize 64x64 favicon-64.png && \
convert -background none plumeletphp_ico.svg -resize 48x48 favicon-48.png && \
convert -background none plumeletphp_ico.svg -resize 32x32 favicon-32.png && \
convert -background none plumeletphp_ico.svg -resize 16x16 favicon-16.png && \
convert favicon-16.png favicon-32.png favicon-48.png favicon-64.png favicon.ico && \
rm favicon-16.png favicon-32.png favicon-48.png favicon-64.png
```
