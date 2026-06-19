<?php

declare(strict_types=1); // Enforce strict type checking

namespace App\Frontend\Middlewares;

use App\Backend\Models\Enums\AuthEnum;
use App\Backend\Models\Operator;
use League\Route\Http\Exception\ForbiddenException;
use League\Route\Http\Exception\UnauthorizedException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * OperatorMiddleware
 *
 * Middleware that enforces operator-based access control.
 *
 * The class uses the AuthEnum backed enum to decide which
 * routes a given operator may access.
 * Routes are defined in the frontend router classes
 * (e.g. \App\Frontend\Routes\UserRoutes)
 * using the '/admin' prefix for actions that modify data.
 *
 *  - employee    can only read data
 *  - admin       can read and modify everything except users/operators
 *  - chief       full CRUD including operator management
 *
 * The middleware looks for an operator entry in the PHP session
 * that contains at least a role key.
 * If the session is missing or the role is not a valid
 * AuthEnum value the request is considered unauthenticated.
 *
 */
final class OperatorMiddleware implements MiddlewareInterface
{
    /**
     * A list of paths that do not require authentication.
     *
     */
    private const PUBLIC_ROUTES = [
        '/',         // home (login page)
        '/home',     // home (login page)
        '/login',    // login page
        '/logout',   // logout page
        '/register', // register page
        '/forgot',   // forgot page
        '/reset',    // reset page
    ];

    /**
     * Basic permissions, all roles have access to these routes.
     *
     */
    private const BASE_ROUTES = [
        '^/dashboard$',
        '^/items$',
        '^/item/(search|\\d+)$',
        '^/warehouses$',
        '^/warehouse/(search|\\d+)$',
        '^/users$',
        '^/user/(search|\\d+)$',
    ];

    /**
     * Role permissions, an extension of the basic patterns.
     *
     */
    private const ROLE_PERMISSIONS = [
        AuthEnum::EMPLOYEE->value => [],
        AuthEnum::ADMIN->value    => [
            // Access to all sub-routes of '/admin/item' and '/admin/warehouse'.
            '^/admin/item(/.*)?$',
            '^/admin/warehouse(/.*)?$',
            // Possibly other modules that do not concern the operators:
            // (e.g. /admin/orders, /admin/orders/\d+ …)
        ],
        AuthEnum::CHIEF->value    => [
            // The chief can also manage operators and users.
            '^/admin$',
            '^/admin/user(/.*)?$',
            // The same paths that the administrator can already access, 
            // but with the same flexibility on any sub-segment.
            '^/admin/item(/.*)?$',
            '^/admin/warehouse(/.*)?$',
        ],
    ];

    /**
     * normalizePath
     *
     * Removes query string and fragment and normalizes any trailing slashes.
     *
     * @param  string $uriPath
     *
     * @return string
     */
    private function normalizePath(string $uriPath): string
    {
        // parse_url is lightweight and sufficient for removing queries and fragments.
        $path = parse_url($uriPath, PHP_URL_PATH) ?? '/';
        // Normalize by removing any '//' and trailing slashes if not root.
        return rtrim($path, '/') ?: '/';
    }

    /**
     * isPublic
     *
     * Check if the path is public.
     *
     * @param  string $path
     *
     * @return bool
     */
    private function isPublic(string $path): bool
    {
        foreach (self::PUBLIC_ROUTES as $public) {
            // Consider both exact match and the variant with a final slash.
            if ($path === $public || ($public !== '/' && str_starts_with($path, rtrim($public, '/') . '/'))) {
                return true;
            }
        }

        return false;
    }

    /**
     * isAllowedNew
     *
     * Returns true if the role can access the path.
     *
     * @param  string $path
     * @param  AuthEnum $auth
     *
     * @return bool
     */
    private function isAllowed(string $path, AuthEnum $auth): bool
    {
        // If the user is an employee, he cannot access paths that contain '/admin'.
        if ($auth === AuthEnum::EMPLOYEE && str_contains($path, '/admin')) {
            return false;
        }

        // Start with the basic patterns.
        foreach (self::BASE_ROUTES as $pattern) {
            if (preg_match('#' . $pattern . '#', $path)) {
                return true;
            }
        }

        // Then check the role-specific patterns.
        foreach (self::ROLE_PERMISSIONS[$auth->value] ?? [] as $pattern) {
            if (preg_match('#' . $pattern . '#', $path)) {
                return true;
            }
        }

        return false;
    }

    /**
     * process
     *
     * Handles the incoming request.
     *
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     *
     * @throws UnauthorizedException  If the user is not authenticated.
     * @throws ForbiddenException     If the user does not have the required
     *                                privileges for the requested route.
     *
     * @return ResponseInterface
     */
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        /** --------------------------------------------------------------
         * 1. PUBLIC ROUTE CHECK, performed before touching the session.
         * --------------------------------------------------------------- */
        $rawPath = $request->getUri()->getPath();

        // Normalize the path.
        $path = method_exists($this, 'normalizePath')
            ? $this->normalizePath($rawPath)
            : $rawPath;

        // Exact match or trailing-slash variants are allowed for public routes.
        if ($this->isPublic($path)) {
            // No auth/authorization required, just forward the request.
            return $handler->handle($request);
        }

        /** --------------------------------------------------------------
         * 2. AUTHENTICATION
         * --------------------------------------------------------------- */
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        /** @var null|Operator $operator */
        $operator = $_SESSION['operator'] ?? null;

        // The operator must be an object of type 'App\Backend\Models\Operator'.
        // If it isn't or it isn't present the user is not authenticated.
        if (! $operator instanceof Operator) {
            throw new UnauthorizedException('Authentication required.');
        }

        // The 'auth' property is an 'AuthEnum' instance or 'null'.
        // type: ?AuthEnum
        $auth = $operator->getAuth();
        if ($auth === null) {
            throw new UnauthorizedException('Invalid operator role.');
        }

        /** --------------------------------------------------------------
         * 3. AUTHORIZATION
         * --------------------------------------------------------------- */
        if (! $this->isAllowed($path, $auth)) {
            throw new ForbiddenException(
                'The operator does not have permission to access this resource.'
            );
        }

        /** --------------------------------------------------------------
         * 4. FORWARD, the request.
         * --------------------------------------------------------------- */
        return $handler->handle($request);
    }
}
