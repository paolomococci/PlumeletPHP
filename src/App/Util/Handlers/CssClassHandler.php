<?php

declare(strict_types=1); // Enable strict type checking

namespace App\Util\Handlers;

/**
 * CssClassHandler
 */
final class CssClassHandler
{
    /**
     * writeCssClasses
     *
     * @return void
     */
    public static function writeCssClasses(
        array $cssClassesAlwaysPresent,
        array $errors,
        string $field,
        ?string $conditional = 'is-invalid'
    ): string {
        // Base classes, turn the array into a space-separated string.
        $classList = implode(' ', $cssClassesAlwaysPresent);

        // Conditional part, add it only when the field has an error.
        if (! empty($errors[$field])) {
            $classList .= ' ' . $conditional;
        }

        // Return the complete attribute with the class= part.
        return 'class="' . trim($classList) . '"';
    }
}
