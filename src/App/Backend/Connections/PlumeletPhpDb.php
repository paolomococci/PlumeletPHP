<?php
declare (strict_types = 1); // Enforce strict type checking

namespace App\Backend\Connections;

use App\Backend\Connections\Interfaces\ConnectionInterface;
use Dotenv\Dotenv;
use PDO;

/**
 * PlumeletPhpDb
 */
final class PlumeletPhpDb implements ConnectionInterface
{
    /**
     * PDO connection cached.
     *
     * @var PDO|null
     */
    private static ?PDO $pdo = null;

    /**
     * Internal flag to check if the environment has already been loaded.
     *
     * @var bool
     */
    private static bool $envLoaded = false;

    /**
     * Load environment variables only once.
     */
    private static function loadEnv(): void
    {
        if (self::$envLoaded) {
            return;
        }

        // The path to the project's root directory (4 levels above).
        $dotenv = Dotenv::createImmutable(dirname(__DIR__, 4));
        $dotenv->load();

        self::$envLoaded = true;
    }

    /**
     * Returns a PDO instance. If already existing, it reuses it.
     *
     * @return PDO
     *
     * @throws \PDOException
     */
    public static function getPdo(): PDO
    {
        // If the connection has already been created, return the cached instance.
        if (self::$pdo !== null) {
            return self::$pdo;
        }

        // Load the environment if it hasn't already been loaded.
        self::loadEnv();

        // Obtain credentials from environment variables.
        $host     = $_ENV['MARIADB_DB_HOST'] ?? 'localhost';
        $database = $_ENV['MARIADB_DB_PLUMELETPHP'] ?? 'plumelet';
        $user     = $_ENV['MARIADB_DB_USER'] ?? 'root';
        $pass     = $_ENV['MARIADB_DB_PASSWORD'] ?? '';

        // Builds the DSN.
        $dsn = "mysql:host={$host};dbname={$database}";

        // Create a PDO connection using recommended options.
        self::$pdo = new PDO(
            $dsn,
            $user,
            $pass,
            [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]
        );

        return self::$pdo;
    }

    /**
     * (Optional) Enables resetting the cached connection.
     *
     * @return void
     */
    public static function resetConnection(): void
    {
        self::$pdo = null;
    }
}
