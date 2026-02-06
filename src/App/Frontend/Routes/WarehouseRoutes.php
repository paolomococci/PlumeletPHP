<?php
declare (strict_types = 1); // Enforce strict type checking

namespace App\Frontend\Routes;

use League\Route\Router;
use App\Frontend\Controllers\WarehouseController;
use App\Frontend\Routes\Interfaces\RoutesInterface;

final class WarehouseRoutes implements RoutesInterface
{
    public function registerRoutes(Router $router): void
    {
        $router->get('/warehouses', [WarehouseController::class, 'index']);
        $router->map(['GET', 'POST'], '/warehouse/new', [WarehouseController::class, 'create']);
        $router->get('/warehouse/{id:number}', [WarehouseController::class, 'read']);
        $router->map(['GET', 'POST'], '/warehouse/update/{id:number}', [WarehouseController::class, 'update']);
        $router->get('/warehouse/delete/{id:number}', [WarehouseController::class, 'delete']);
    }
}
