<?php

declare(strict_types=1); // Enable strict type checking

namespace App\Util\Helpers;

final class ErrorsFluentRenderHelper
{
    /**
     * @var array All validation errors.
     * E.g. ['name' => 'Invalid name!', 'email' => 'Invalid email!', 'password' => 'Invalid password!']
     */
    private array $errors;

    /**
     * @var string The field name that this instance will inspect.
     */
    private string $field;

    /**
     * @var array CSS classes that should always be applied.
     */
    private array $classes = [];

    /**
     * Property promotion: store $errors and $field in the constructor.
     *
     * @param array     $errors
     * @param string    $field
     */
    public function __construct(array $errors, string $field)
    {
        $this->errors = $errors;
        $this->field  = $field;
    }

    /**
     * Fluent method to set the CSS classes that will always be present
     * in the <div> rendering the error message.
     *
     * @param array $classes
     *
     * @return self Returns the same instance to allow chaining.
     */
    public function with(array $classes): self
    {
        $this->classes = $classes;
        return $this;
    }

    /**
     * Renders the error message, if any, wrapped in a <div>.
     *
     * @param string|null $fallback Optional message that will be used instead of the actual error.
     *
     * @return string  The <div> element containing the error HTML-escaped or an empty string if no error.
     */
    public function render(?string $fallback = null): string
    {
        // If the field has no error, nothing to render.
        if (empty($this->errors[$this->field])) {
            return '';
        }

        // Prepare the class attribute with() may leave $this->classes empty, that's perfectly fine.
        $classAttr = implode(' ', $this->classes ?? []);

        // The message to display: fallback if supplied, otherwise the real error from the validation array.
        $msg = $fallback ?? $this->errors[$this->field];

        // Escape it to avoid XSS attacks.
        $escaped = htmlspecialchars($msg, ENT_QUOTES, 'UTF-8');

        // Return a ready-to-echo <div class="…">…</div>
        return "<div class=\"{$classAttr}\">{$escaped}</div>";
    }
}
