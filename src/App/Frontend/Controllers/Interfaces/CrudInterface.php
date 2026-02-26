<?php

declare(strict_types=1); // Enforce strict type checking

namespace App\Frontend\Controllers\Interfaces;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface CrudInterface
{
    /**
     * create
     *
     * @param  mixed $request
     * @return ResponseInterface
     */
    public function create(ServerRequestInterface $request): ResponseInterface;

    /**
     * read
     *
     * @param  mixed $request
     * @param  mixed $args
     * @return ResponseInterface
     */
    public function read(ServerRequestInterface $request, array $args): ResponseInterface;

    /**
     * update
     *
     * @param  mixed $request
     * @param  mixed $args
     * @return ResponseInterface
     */
    public function update(ServerRequestInterface $request, array $args): ResponseInterface;

    /**
     * delete
     *
     * @param  mixed $request
     * @param  mixed $args
     * @return ResponseInterface
     */
    public function delete(ServerRequestInterface $request, array $args): ResponseInterface;
}
