<?php
declare (strict_types = 1); // Enforce strict type checking

namespace App\Frontend\Templates\Interfaces;

interface TemplateInterface
{
    public function render(string $template, array $data = []): string;
}
