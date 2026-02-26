<?php

declare (strict_types = 1); // Enforce strict type checking

namespace App\Frontend\Routes;

use App\Frontend\Controllers\ItemController;
use App\Frontend\Routes\Interfaces\RoutesInterface;
use League\Route\Router;

/**
 * ItemRoutes
 */
final class ItemRoutes implements RoutesInterface
{    
    /**
     * registerRoutes
     *
     * @param  mixed $router
     * @return void
     */
    public function registerRoutes(Router $router): void
    {
        $router->get('/items', [ItemController::class, 'paginate']);
        $router->get('/item/search', [ItemController::class, 'search']);
        $router->get('/item/{id:number}', [ItemController::class, 'read']);

        $router->group(
            '/admin',
            function ($router) {
                $router->map(['GET', 'POST'], '/item/new', [ItemController::class, 'create']);
                $router->map(['GET', 'POST'], '/item/update/{id:number}', [ItemController::class, 'update']);
                $router->map(['GET', 'POST'], '/item/delete/{id:number}', [ItemController::class, 'delete']);
            }
        );
    }
}
