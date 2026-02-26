<?php

declare(strict_types=1); // Enforce strict type checking

namespace App\Frontend\Templates\Interfaces;

interface TemplateInterface
{
    /**
     * render
     *
     * @param  mixed $template
     * @param  mixed $data
     * @return string
     */
    public function render(string $template, array $data = []): string;
}
