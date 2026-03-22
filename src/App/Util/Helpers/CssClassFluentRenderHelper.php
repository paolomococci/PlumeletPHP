<?php

declare(strict_types=1); // Enable strict type checking

namespace App\Util\Helpers;

/**
 * CssClassFluentHelper
 */
final class CssClassFluentRenderHelper
{
    /**
     * Creates a new helper.
     *
     * @param array  $errors   All validation errors.
     * @param string $field    The field that will be checked for errors.
     */
    public function __construct(
        private array $errors,
        private string $field,
        private array $cssClassesAlwaysPresent = [],
        private string $conditional = 'is-invalid'
    ) {}

    /** ------------------------------------------------------------------
     *  1. Fluent configuration
     * ------------------------------------------------------------------ */

    /**
     * Sets the CSS classes that should always be present on the
     * element.  These classes are turned into a space-separated string
     * and then the optional conditional class will be appended if an
     * error exists.
     *
     * @param array $classes
     *
     * @return self
     */
    public function withCssClassesAlwaysPresent(array $classes): self
    {
        $this->cssClassesAlwaysPresent = $classes;
        return $this;
    }

    /**
     * Sets the conditional class that will be appended when the
     * field contains an error. Passing null keeps the current value.
     *
     * @param string|null $conditional
     *
     * @return self
     */
    public function withConditional(?string $conditional): self
    {
        if ($conditional !== null) {
            $this->conditional = $conditional;
        }
        return $this;
    }

    /** ------------------------------------------------------------------
     *  2. Rendering
     * ------------------------------------------------------------------ */

    /**
     * Builds the class="…"
     * attribute for an input element.
     *
     * The attribute always contains the classes set via
     * withCssClassesAlwaysPresent() and,
     * if the field has an error, also contains the conditional class.
     *
     * @return string e.g. class="form-control" or class="form-control is-invalid"
     */
    public function renderClassAttribute(): string
    {
        // Base classes first.
        $classList = implode(' ', $this->cssClassesAlwaysPresent);

        // Append the conditional part only when an error is present
        if (!empty($this->errors[$this->field])) {
            $classList .= ' ' . $this->conditional;
        }

        // Return the full attribute, trimmed to avoid trailing spaces.
        return 'class="' . trim($classList) . '"';
    }
}
