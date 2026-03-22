<?php

declare(strict_types=1); // Enforce strict type checking

namespace App\Frontend\Controllers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Dashboard controller - shows a welcoming screen for the logged-in user.
 *
 * The controller receives the logged-in user name via dependency injection.
 * In a real project you would inject an Auth/Session service that knows the
 * current user. For brevity we just inject the name directly.
 */
class DashboardController extends Controller
{

    /**
     * Render the dashboard view.
     *
     * @return ResponseInterface
     */
    public function index(ServerRequestInterface $request): ResponseInterface
    {

        // The middleware is already part of every request!
        // So, in any controller or view I can access it with:
        $csrf  = $request->getAttribute('csrf');
        $token = $csrf->getToken();

        // Pass the user name to the view.
        return $this->render(
            'Dashboard/index',
            [
                'userName'   => 'unset',
                'csrf_token' => $token,
            ]
        );
    }
}
