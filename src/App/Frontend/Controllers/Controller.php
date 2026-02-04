<?php

declare(strict_types=1); // Enforce strict type checking

namespace App\Frontend\Controllers;

use DI\Attribute\Inject;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use App\Frontend\Templates\Interfaces\TemplateInterface;

/**
 * Controller
 */
abstract class Controller
{
    #[Inject]
    private ResponseFactoryInterface $factory;

    #[Inject]
    private TemplateInterface $renderer;

    /**
     * render
     *
     * @param  mixed $template
     * @param  mixed $data
     * @return ResponseInterface
     */
    protected function render(string $template, array $data = []): ResponseInterface
    {
        $contents = $this->renderer->render($template, $data);
        $stream   = $this->factory->createStream($contents);
        $response = $this->factory->createResponse(200);
        $response = $response->withBody($stream);
        return $response;
    }

    /**
     * redirect
     *
     * @param  mixed $path
     * @return ResponseInterface
     */
    protected function redirect(string $path): ResponseInterface
    {
        return $this->factory
            // 303 See Other per POST-Redirect-GET
            ->createResponse(303)
            ->withHeader('Location', $path);
    }
}
