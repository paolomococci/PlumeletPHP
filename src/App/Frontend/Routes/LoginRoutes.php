<?php

declare(strict_types=1); // Enforce strict type checking

namespace App\Frontend\Routes;

use App\Frontend\Controllers\LoginController;
use App\Frontend\Routes\Interfaces\RoutesInterface;
use League\Route\Router;

/**
 * LoginRoutes
 */
final class LoginRoutes implements RoutesInterface
{
    /**
     * registerRoutes
     *
     * @param  mixed $router
     * @return void
     */
    public function registerRoutes(Router $router): void
    {
        $router->get('/', [LoginController::class, 'login']);
        $router->get('/home', [LoginController::class, 'login']);
        $router->map(['GET', 'POST'], '/login', [LoginController::class, 'login']);
        $router->get('/logout', [LoginController::class, 'logout']);
        $router->map(['GET', 'POST'], '/forgot', [LoginController::class, 'forgot']);
        $router->map(['GET', 'POST'], '/reset', [LoginController::class, 'reset']);
        $router->map(['GET', 'POST'], '/register', [LoginController::class, 'register']);
    }
}
