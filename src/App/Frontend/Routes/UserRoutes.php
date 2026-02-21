<?php
declare (strict_types = 1); // Enforce strict type checking

namespace App\Frontend\Routes;

use App\Frontend\Controllers\UserController;
use App\Frontend\Routes\Interfaces\RoutesInterface;
use League\Route\Router;

final class UserRoutes implements RoutesInterface
{
    public function registerRoutes(Router $router): void
    {
        $router->get('/users', [UserController::class, 'paginate']);
        $router->get('/user/search', [UserController::class, 'search']);
        $router->map(['GET', 'POST'], '/user/new', [UserController::class, 'create']);
        $router->get('/user/{id:number}', [UserController::class, 'read']);
        $router->map(['GET', 'POST'], '/user/update/{id:number}', [UserController::class, 'update']);
        $router->get('/user/delete/{id:number}', [UserController::class, 'delete']);
    }
}
