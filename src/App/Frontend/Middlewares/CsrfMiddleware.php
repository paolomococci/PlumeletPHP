<?php

declare(strict_types=1); // Enforce strict type checking

namespace App\Frontend\Middlewares;

use App\Errors\CsrfTokenException;
use App\Util\Handlers\CsrfTokenHandler;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * CsrfMiddleware 
 * 
 * This middleware protects the application from CSRF attacks by
 * validating a CSRF token on state‑changing HTTP requests (POST, PUT,
 * DELETE, PATCH). It also injects the `CsrfTokenHandler` into the
 * request attributes so that downstream code can generate or read
 * tokens if needed.
 * 
 */
class CsrfMiddleware implements MiddlewareInterface
{
    /**
     * __construct
     *
     * @param  mixed $csrfTokenHandler A service that knows how to validate a token and generate a new one.
     * @return void
     * 
     */
    public function __construct(private CsrfTokenHandler $csrfTokenHandler) {}

    /**
     * process
     * 
     * Process an incoming server request.
     *
     * 1. Determine if the request method requires CSRF protection,
     *    anything other than GET.  
     * 2. If so, call validateToken() to ensure the token is valid.  
     * 3. Attach the CSRF handler as a request attribute named
     *    csrf_token so controllers can access it later.  
     * 4. Pass the possibly modified request to the next handler in
     *    the pipeline.
     *
     * @param ServerRequestInterface  $request  The incoming request.
     * @param RequestHandlerInterface $handler  The next handler in the middleware chain.
     * 
     * @return ResponseInterface
     * 
     */
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        // Validate the token for non‑GET requests.
        if (in_array($request->getMethod(), ['POST', 'PUT', 'DELETE', 'PATCH'])) {
            $this->validateToken($request);
        }

        // Add the CSRF handler to the request so it can be accessed later.
        $request = $request->withAttribute('csrf', $this->csrfTokenHandler);

        // Continue handling the request.
        return $handler->handle($request);
    }

    /**
     * validateToken
     *
     * Validate the CSRF token extracted from the request.
     *
     * 1. Extract the token from the request body or headers.  
     * 2. Ask the injected `CsrfTokenHandler` to validate it.  
     * 3. Throw a CsrfTokenException if the token is invalid
     *    (the application’s exception middleware or error handler
     *    should catch this and respond appropriately).
     *
     * @param ServerRequestInterface $request The request containing the token.
     *
     * @throws CsrfTokenException If the token is missing or invalid.
     * 
     */
    private function validateToken(ServerRequestInterface $request): void
    {
        $token = $this->extractToken($request);

        if (! $this->csrfTokenHandler->validateToken($token ?? '')) {
            // The framework should handle the exception.
            throw new CsrfTokenException;
        }
    }

    /**
     * extractToken
     *
     * Attempt to pull a CSRF token out of the request.
     *
     * The extraction logic follows a common pattern:
     *
     * 1. Look for a field named csrf_token in the parsed body
     *    (typical for form submissions).  
     * 2. If not found, check the `X-CSRF-Token` header which is
     *    commonly used for AJAX requests.  
     * 3. Return the token if found, otherwise return null.
     *
     * @param ServerRequestInterface $request The request to inspect.
     *
     * @return string|null The extracted token, or null if none was found.
     * 
     */
    private function extractToken(ServerRequestInterface $request): ?string
    {
        // Check the body form data.
        $parsedBody = $request->getParsedBody();
        if (is_array($parsedBody) && isset($parsedBody['csrf_token'])) {
            return $parsedBody['csrf_token'];
        }

        // Verify the header for AJAX.
        $headerToken = $request->getHeaderLine('X-CSRF-Token');
        if ($headerToken) {
            return $headerToken;
        }

        return null;
    }
}
