<?php
declare (strict_types = 1); // Enforce strict type checking

namespace App\Frontend\Controllers\Interfaces;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface CrudInterface
{
    public function create(ServerRequestInterface $request): ResponseInterface;
    public function read(ServerRequestInterface $request, array $args): ResponseInterface;
    public function update(ServerRequestInterface $request, array $args): ResponseInterface;
    public function delete(ServerRequestInterface $request, array $args): ResponseInterface;
}
