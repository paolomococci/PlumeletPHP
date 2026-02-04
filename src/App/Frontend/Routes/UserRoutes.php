<?php
declare (strict_types = 1); // Enforce strict type checking

namespace App\Frontend\Routes;

use League\Route\Router;
use App\Frontend\Controllers\UserController;
use App\Frontend\Routes\Interfaces\RoutesInterface;

final class UserRoutes implements RoutesInterface
{
    public function registerRoutes(Router $router): void
    {
        $router->get('/users', [UserController::class, 'index']);
        $router->map(['GET', 'POST'], '/user/new', [UserController::class, 'create']);
        $router->get('/user/{id:number}', [UserController::class, 'read']);
        $router->map(['GET', 'POST'], '/user/update/{id:number}', [UserController::class, 'update']);
        $router->get('/user/delete/{id:number}', [UserController::class, 'delete']);
    }
}
