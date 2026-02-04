<?php
declare (strict_types = 1); // Enforce strict type checking

namespace App\Frontend\Templates;

use League\Plates\Engine;
use App\Frontend\Templates\Interfaces\TemplateInterface;

class RenderTemplate implements TemplateInterface
{
    public function render(string $template, array $data = []): string
    {
        $engine = new Engine(
            dirname(__DIR__, 1) . "/Views"
        );

        return $engine->render($template, $data);
    }
}
