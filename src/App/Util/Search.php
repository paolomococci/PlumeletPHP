<?php
declare (strict_types = 1); // Enforce strict type checking

namespace App\Util;

use App\Backend\Connections\PlumeletPhpDb;
use PDO;
use RuntimeException;

/**
 * Search
 *
 * A utility class that performs name-based look-ups and counts
 * on any database entity that has a name column.
 */
final class Search
{
    /** @var PDO Connection to the DB */
    protected PDO $pdo;

    /**
     * __construct
     *
     * Obtains a PDO instance from the application-wide database
     * connection factory (PlumeletPhpDb) and stores it for later use.
     *
     * @return void
     */
    public function __construct()
    {
        $this->pdo = PlumeletPhpDb::getPdo();
    }

    /**
     * byName
     *
     * Find records by partial name match.
     *
     * This method searches for rows in the table that belongs to
     * $entityClass where the name column contains $name.
     * Pagination is handled via $page and $perPage.
     *
     * @param string $entityClass  Full class name of the entity to query
     * @param string $name          Search string (will be wrapped with %)
     * @param int    $page          Page number (1-based)
     * @param int    $perPage       Number of records per page
     *
     * @return array Array of instantiated model objects
     */
    public function byName(
        string $entityClass,
        string $name,
        int $page = 1,
        int $perPage = 5
    ): array {
        // Resolve the table name from the entity class
        $table = $this->getTableNameFromEntity($entityClass);
        // Build the fully-qualified class name for later instantiation
        $model = '\\' . $entityClass;

        // Prepare the LIKE pattern: %searchTerm%
        $likeName = sprintf("%%%s%%", $name);
        // Calculate the offset for the LIMIT clause.
        $offset = (($page - 1) * $perPage);
        // SQL statement with named placeholders.
        $sql = "SELECT * FROM {$table} WHERE name LIKE :name LIMIT :limit OFFSET :offset";

        // Prepare, bind, and execute the query.
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':name', $likeName, PDO::PARAM_STR);
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        // Fetch all rows as associative arrays.
        $arrayResults = $stmt->fetchAll(PDO::FETCH_ASSOC);

        /**
         * Map each associative array to an instance of the model class.
         *
         * Advantages of the arrow function:
         * - resolves the class reference immediately;
         * - avoid potential static method binding issues;
         * - allows for additional manipulations if necessary;
         * - easier to add transformation logic;
         * - clearer and more immediate code;
         * - explicit mapping operation.
         *
         */
        $models = array_map(fn($data) => $model::fetchFromData($data), $arrayResults);

        // Return the array of model objects
        return $models;
    }

    /**
     * getTableNameFromEntity
     *
     * Get the table name for a given entity class.
     *
     * @param  mixed $entity Full class name of the entity.
     *
     * @return string Resolved table name.
     */
    private function getTableNameFromEntity(string $entity): string
    {
        // Check for a custom static method on the entity.
        if (method_exists($entity, 'getTableName')) {
            return $entity::getTableName();
        }

        // Convention-based fallback:
        // plumeletphp_db.<entity>s_tbl
        $parts = explode('\\', $entity);
        return strtolower('plumeletphp_db.' . end($parts) . 's_tbl');
    }

    /**
     * countByName
     *
     * Count the number of items that contain a specific text in name.
     *
     * @param string $entityClass Full class name of the entity to query.
     * @param string $name Search string (will be wrapped with %).
     *
     * @return int Total count of matching rows.
     */
    public function countByName(
        string $entityClass,
        string $name
    ): int {
        // Resolve the table name.
        $table = $this->getTableNameFromEntity($entityClass);

        // Build a COUNT query using a named placeholder.
        $sql = sprintf("SELECT COUNT(*) FROM %s WHERE name LIKE :name", $table);

        // Prepare, bind, and execute the query
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':name', "%%{$name}%%", \PDO::PARAM_STR);
        if (! $stmt->execute()) {
            // If execution fails, fetch the PDO error info.
            $error = $stmt->errorInfo();
            throw new RuntimeException('Error query COUNT: ' . $error[2]);
        }

        // Return the single column result as an integer.
        return (int) $stmt->fetchColumn();
    }
}
