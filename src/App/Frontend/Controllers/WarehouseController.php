<?php

declare (strict_types = 1); // Enforce strict type checking

namespace App\Frontend\Controllers;

use App\Backend\Models\Warehouse;
use App\Frontend\Controllers\Controller;
use App\Frontend\Controllers\Interfaces\CrudInterface;
use App\Frontend\Services\WarehouseService;
use DateTime;
use League\Route\Http\Exception\NotFoundException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * WarehouseController
 *
 * According to SOLID principles, the component should only
 * be responsible for receiving HTTP requests,
 * delegating the business logic to the service,
 * and returning the appropriate response.
 *
 */
final class WarehouseController extends Controller implements CrudInterface
{
    /**
     * __construct
     *
     * @return void
     *
     * A concise constructor syntax is achieved by using PHP 8.0+ property promotion,
     * which automatically declares and initializes class properties.
     *
     */
    public function __construct(
        private DateTime $datetime,
        protected WarehouseService $warehouseService
    ) {}

    /**
     * index
     *
     * @return ResponseInterface
     */
    public function index(): ResponseInterface
    {
        // The service class can be used to retrieve the complete list of warehouses.
        $warehouses = $this->warehouseService->index();

        return $this->render(
            'Warehouse/index',
            [
                'view_title' => 'List of warehouses',
                'datetime'   => $this->datetime->format('l'),
                'warehouses' => $warehouses,
            ]
        )->withStatus(200);
    }

    /**
     * paginate
     *
     * @param  mixed $request
     * @return ResponseInterface
     */
    public function paginate(ServerRequestInterface $request): ResponseInterface
    {
        // Grab the page query-string, defaults to 1
        $page = (int) ($request->getQueryParams()['page'] ?? 1);
        if ($page < 1) {$page = 1;}

        $perPage    = 5; // configurable
        $warehouses = $this->warehouseService->paginate($page, $perPage);

        // Get total count (used for navigation)
        $total = $this->warehouseService->count();

        // Build the view data
        $viewData = [
            'view_title' => 'List of warehouses',
            'datetime'   => $this->datetime->format('l'),
            'warehouses' => $warehouses,
            'pagination' => static::pagination($page, $perPage, $total),
        ];

        return $this->render('Warehouse/paginate', $viewData)->withStatus(200);
    }

    /**
     * pagination
     *
     * Helper to build pagination data.
     *
     * @param  mixed $page
     * @param  mixed $perPage
     * @param  mixed $total
     * @return array
     */
    private static function pagination(int $page, int $perPage, int $total): array
    {
        $pages = (int) ceil($total / $perPage);

        return [
            'current' => $page,
            'perPage' => $perPage,
            'total'   => $total,
            'pages'   => $pages,
            'prev'    => $page > 1 ? $page - 1 : null,
            'next'    => $page < $pages ? $page + 1 : null,
        ];
    }

    /* --------------------------------------------------------------------- */
    /*  CRUD methods ------------------------------------------------------- */
    /* --------------------------------------------------------------------- */

    /**
     * create
     *
     * @return ResponseInterface
     */
    public function create(ServerRequestInterface $request): ResponseInterface
    {
        if ($request->getMethod() === 'POST') {
            $parameters = $request->getParsedBody();
            $warehouse  = new Warehouse(
                null,
                $parameters['name'],
                $parameters['address'],
                $parameters['email'],
                $parameters['warehouseType'],
                null,
                null
            );
            // Save the new warehouse using the service class, which expects an argument compatible with the model interface.
            $id = $this->warehouseService->create($warehouse);

            return $this->redirect("/warehouse/{$id}");
        }

        // Returns the content of the body as a string.
        return $this->render(
            'Warehouse/create',
            [
                'view_title' => 'New warehouse',
                'datetime'   => $this->datetime->format('l'),
            ]
        );
    }

    /**
     * read
     *
     * @return ResponseInterface
     */
    public function read(ServerRequestInterface $request, array $args): ResponseInterface
    {
        // Retrieve a specific warehouse using the service class.
        $warehouse = $this->warehouseService->read($args['id']);

        if ($warehouse !== null) {
            return $this->render(
                'Warehouse/read',
                [
                    'view_title' => 'Warehouse details',
                    'datetime'   => $this->datetime->format('l'),
                    'id'         => $warehouse->getId(),
                    'name'       => $warehouse->getName(),
                    'address'    => $warehouse->getAddress(),
                    'email'      => $warehouse->getEmail(),
                    'type'       => $warehouse->getType(),
                ]
            )->withStatus(200);
        } else {
            throw new NotFoundException();
        }
    }

    /**
     * update
     *
     * @return ResponseInterface
     */
    public function update(ServerRequestInterface $request, array $args): ResponseInterface
    {

        if ($request->getMethod() === 'POST') {

            $parameters = $request->getParsedBody();
            $warehouse  = new Warehouse(
                $parameters['id'],
                $parameters['name'],
                $parameters['address'],
                $parameters['email'],
                $parameters['warehouseType'],
                $parameters['created_at'] ?? null,
                $parameters['updated_at'] ?? null
            );

            // Apply the changes using the service class.
            $id = $this->warehouseService->update($warehouse);

            $id = $warehouse->getId();
            return $this->read($request, ['id' => $id]);
        } else {

            $warehouse = $this->warehouseService->read($args['id']);

            if ($warehouse !== null) {
                return $this->render(
                    'Warehouse/update',
                    [
                        'view_title' => 'Edit warehouse',
                        'datetime'   => $this->datetime->format('l'),
                        'id'         => $warehouse->getId(),
                        'name'       => $warehouse->getName(),
                        'address'    => $warehouse->getAddress(),
                        'email'      => $warehouse->getEmail(),
                        'type'       => $warehouse->getType(),
                    ]
                );
            } else {
                throw new NotFoundException();
            }
        }
    }

    /**
     * delete
     *
     * @return ResponseInterface
     */
    public function delete(ServerRequestInterface $request, array $args): ResponseInterface
    {
        // Delete the warehouse using the service class.
        $deleted = $this->warehouseService->delete($args['id']);

        if ($deleted) {
            return $this->index();
        } else {
            throw new NotFoundException();
        }

    }
}
