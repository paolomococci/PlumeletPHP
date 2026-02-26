<?php

declare(strict_types=1); // Enforce strict type checking

namespace App\Frontend\Middlewares;

use App\Frontend\Middlewares\Interfaces\CsrfMiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Psr15Wrapper
 * 
 * Wrapper that allows a middleware that expects a callable $next
 * used in older or custom middleware patterns to be used
 * inside a PSR-15 environment that calls process(ServerRequestInterface, RequestHandlerInterface).
 * 
 */
final class Psr15Wrapper implements MiddlewareInterface
{
    /** 
     * @var CsrfMiddlewareInterface
     * 
     * The inner middleware that accepts a callable $next.
     * 
     */
    private CsrfMiddlewareInterface $inner;

    /**
     * __construct
     *
     * Constructor receives the inner middleware instance.
     *
     * @param CsrfMiddlewareInterface $inner The middleware that uses a callable $next.
     * 
     * @return void
     * 
     */
    public function __construct(CsrfMiddlewareInterface $inner)
    {
        $this->inner = $inner;
    }

    /**
     * process
     *
     * PSR-15 entry point.
     *
     * 1. The inner middleware expects a callable $next that will receive a
     *    ServerRequestInterface and return a ResponseInterface.  
     * 2. We create a closure that simply forwards the request to the
     *    PSR-15 $handler.  
     * 3. Pass the request and the closure to the inner middleware.
     *
     * @param ServerRequestInterface  $request The incoming HTTP request.
     * @param RequestHandlerInterface $handler The next PSR-15 handler in the stack.
     *
     * @return ResponseInterface The HTTP response produced by the inner middleware.
     * 
     */
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        /**
         * The inner middleware expects a callable $next,
         * so we create a closure that calls the next PSR‑15 handler.
         */
        $next = fn(ServerRequestInterface $req): ResponseInterface => $handler->handle($req);

        // Delegate the request handling to the inner middleware.
        return $this->inner->process($request, $next);
    }
}
