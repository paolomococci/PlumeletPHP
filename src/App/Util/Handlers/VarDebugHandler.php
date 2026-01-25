<?php

declare (strict_types = 1); // Enforce strict type checking

namespace App\Util\Handlers;

/**
 * VarDebugHandler
 */
final class VarDebugHandler
{    
    /**
     * varDump
     *
     * @param  mixed $vars
     * @return string
     */
    public static function varDump(mixed ...$vars): string
    {
        echo '<pre>';
        var_dump($vars);
        echo '<pre>';
        exit;
    }
    
    /**
     * varExport
     *
     * @param  mixed $vars
     * @return string
     */
    public static function varExport(mixed ...$vars): string
    {
        echo '<pre>';
        var_export($vars);
        echo '<pre>';
        exit;
    }
}
