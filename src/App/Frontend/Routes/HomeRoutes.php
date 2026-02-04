<?php
declare (strict_types = 1); // Enforce strict type checking

namespace App\Frontend\Routes;

use App\Frontend\Controllers\HomeController;
use App\Frontend\Routes\Interfaces\RoutesInterface;
use League\Route\Router;

final class HomeRoutes implements RoutesInterface
{
    public function registerRoutes(Router $router): void
    {
        $router->get('/', [HomeController::class, 'index']);
        $router->get('/home', [HomeController::class, 'index']);
    }
}
