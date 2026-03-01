<?php

declare(strict_types=1); // Enforce strict type checking

namespace App\Frontend\Routes;

use App\Frontend\Controllers\WarehouseController;
use App\Frontend\Routes\Interfaces\RoutesInterface;
use League\Route\Router;

/**
 * WarehouseRoutes
 */
final class WarehouseRoutes implements RoutesInterface
{
    /**
     * registerRoutes
     *
     * @param  mixed $router
     * @return void
     */
    public function registerRoutes(Router $router): void
    {
        $router->get('/warehouses', [WarehouseController::class, 'paginate']);
        $router->get('/warehouse/search', [WarehouseController::class, 'search']);
        $router->get('/warehouse/{id:number}', [WarehouseController::class, 'read']);

        $router->group(
            '/admin',
            function ($router) {
                $router->map(['GET', 'POST'], '/warehouse/new', [WarehouseController::class, 'create']);
                $router->map(['GET', 'POST'], '/warehouse/update/{id:number}', [WarehouseController::class, 'update']);
                $router->map(['GET', 'POST'], '/warehouse/delete/{id:number}', [WarehouseController::class, 'delete']);
            }
        );
    }
}
