<?php

declare(strict_types=1); // Enforce strict type checking

namespace App\Frontend\Routes\Interfaces;

use League\Route\Router;

interface RoutesInterface
{
    /**
     * registerRoutes
     *
     * @param  mixed $router
     * @return void
     */
    public function registerRoutes(Router $router): void;
}
