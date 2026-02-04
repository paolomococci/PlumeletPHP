<?php
declare (strict_types = 1); // Enforce strict type checking

namespace App\Backend\Connections\Interfaces;

use PDO;

interface ConnectionInterface
{    
    /**
     * getPdo
     *
     * @return PDO
     */
    public static function getPdo(): PDO;
}
