<?php

declare(strict_types=1); // Enforce strict type checking

namespace App\Frontend\Routes;

use App\Frontend\Controllers\DashboardController;
use App\Frontend\Routes\Interfaces\RoutesInterface;
use League\Route\Router;

/**
 * Registers all dashboard-related routes.
 */
final class DashboardRoutes implements RoutesInterface
{
    /**
     * Attach routes to the router.
     *
     * @param Router $router
     * @return void
     */
    public function registerRoutes(Router $router): void
    {
        // Public dashboard entry-point
        $router->get('/dashboard', [DashboardController::class, 'index']);
    }
}
