<?php

namespace App\Frontend\Middlewares\Interfaces;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface CsrfMiddlewareInterface
{
    /**
     * process
     *
     * @param  mixed $request
     * @param  mixed $next
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, callable $next): ResponseInterface;

    /**
     * extractToken
     *
     * @param  mixed $request
     * @return string
     */
    public function extractToken(ServerRequestInterface $request): string;

    /**
     * extractFromAuthHeader
     *
     * @param  mixed $request
     * @return string
     */
    public function extractFromAuthHeader(ServerRequestInterface $request): string;
}
