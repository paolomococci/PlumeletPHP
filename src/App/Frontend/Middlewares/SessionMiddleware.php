<?php

declare(strict_types=1); // Enforce strict type checking

namespace App\Frontend\Middlewares;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Server\MiddlewareInterface;

/**
 * SessionMiddleware
 * 
 * PSR-15 middleware that ensures a PHP session is started.
 * 
 */
class SessionMiddleware implements MiddlewareInterface
{
    /**
     * @var array<string, mixed> Options for session cookie
     *   - lifetime  (int)   0 - until the browser is closed
     *   - path      (string)
     *   - domain    (string)
     *   - secure    (bool)
     *   - httponly  (bool)
     *   - samesite  (string)  "Lax", "Strict" or "None"
     *   - name      (string) name of the session cookie
     */
    private array $options;

    /**
     * @param array<string, mixed> $options
     */
    public function __construct(array $options = [])
    {
        $this->options = $options;
    }

    /**
     * 
     *
     * The middleware simply starts a session (if not already started) and
     * then hands over the request to the next handler.
     *
     * @param ServerRequestInterface  $request
     * @param RequestHandlerInterface $handler
     *
     * @return ResponseInterface
     */
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        // ------------------------------------------------------------------
        // Start the session only if it hasn't been started yet
        // ------------------------------------------------------------------
        if (session_status() === PHP_SESSION_NONE) {
            // If headers were already sent we cannot start a session.
            if (headers_sent()) {
                throw new \RuntimeException('Cannot start PHP session - headers already sent.');
            }

            // ------------------------------------------------------------------
            // Build the cookie params
            // ------------------------------------------------------------------
            // PHP 7.3+ lets us pass an array; for older versions we fall back
            // to the legacy parameter signature.
            $defaultParams = session_get_cookie_params();

            $cookieOptions = $this->options + [
                'lifetime'  => 0,                                      // till browser closes.
                'path'      => $defaultParams['path'] ?? '/',
                'domain'    => $defaultParams['domain'] ?? '',
                // default secure to true if HTTPS is on
                'secure'    => $defaultParams['secure'] ?? (
                    !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'
                ),
                'httponly'  => $defaultParams['httponly'] ?? true,
                'samesite'  => $defaultParams['samesite'] ?? 'Lax',    // Lax is a safe default.
            ];

            // PHP 7.3+: array of options
            if (PHP_VERSION_ID >= 70300) {
                session_set_cookie_params($cookieOptions);
            } else {
                // Legacy signature: lifetime, path, domain, secure, httponly
                session_set_cookie_params(
                    (int)$cookieOptions['lifetime'],
                    (string)$cookieOptions['path'],
                    (string)$cookieOptions['domain'],
                    (bool)$cookieOptions['secure'],
                    (bool)$cookieOptions['httponly']
                );
                // SameSite can be forced via the "samesite" cookie param in PHP 7.3+
                // (pre-7.3 you have to set it manually with setcookie())
                if (!empty($cookieOptions['samesite'])) {
                    ini_set('session.cookie_samesite', (string)$cookieOptions['samesite']);
                }
            }

            // ------------------------------------------------------------------
            // Start the session
            // ------------------------------------------------------------------
            session_start();

            // Optional: regenerate the session id on each request to avoid fixation.
            // Uncomment if you want stricter security.
            // if (random_int(0, 1) === 1) {
            //     session_regenerate_id(true);
            // }
        }

        // ------------------------------------------------------------------
        // Pass the request to the next middleware / route handler
        // ------------------------------------------------------------------
        return $handler->handle($request);
    }
}
