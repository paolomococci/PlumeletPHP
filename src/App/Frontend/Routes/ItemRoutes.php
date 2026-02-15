<?php
declare (strict_types = 1); // Enforce strict type checking

namespace App\Frontend\Routes;

use App\Frontend\Controllers\ItemController;
use App\Frontend\Routes\Interfaces\RoutesInterface;
use League\Route\Router;

final class ItemRoutes implements RoutesInterface
{
    public function registerRoutes(Router $router): void
    {
        $router->get('/items', [ItemController::class, 'paginate']);
        $router->map(['GET', 'POST'], '/item/new', [ItemController::class, 'create']);
        $router->get('/item/{id:number}', [ItemController::class, 'read']);
        $router->map(['GET', 'POST'], '/item/update/{id:number}', [ItemController::class, 'update']);
        $router->get('/item/delete/{id:number}', [ItemController::class, 'delete']);
    }
}
