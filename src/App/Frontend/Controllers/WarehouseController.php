<?php

declare(strict_types=1); // Enforce strict type checking

namespace App\Frontend\Controllers;

use App\Backend\Models\Enums\WarehouseTypeEnum;
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

    /* --------------------------------------------------------------------- */
    /*  INDEX  ------------------------------------------------------------- */
    /* --------------------------------------------------------------------- */

    /**
     * index
     *
     * Handles a GET request for the root resource (e.g. /warehouses).
     * Delegates data retrieval to the service and renders the list view.
     *
     * @return ResponseInterface
     */
    public function index(): ResponseInterface
    {
        // The service class can be used to retrieve the complete list of warehouses.
        $warehouses = $this->warehouseService->index();

        // Render the template and pass necessary data.
        return $this->render(
            'Warehouse/index',
            [
                'view_title' => 'List of warehouses',
                'datetime'   => $this->datetime->format('l'),
                'warehouses' => $warehouses,
            ]
            // HTTP 200 OK
        )->withStatus(200);
    }

    /* --------------------------------------------------------------------- */
    /*  PAGINATION  -------------------------------------------------------- */
    /* --------------------------------------------------------------------- */

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
        if ($page < 1) {
            $page = 1;
        }

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
    /*  SEARCH  ------------------------------------------------------------ */
    /* --------------------------------------------------------------------- */

    /**
     * search
     *
     * Handles search queries on warehouses.
     *
     * @param  mixed $request
     * @return ResponseInterface
     */
    public function search(ServerRequestInterface $request): ResponseInterface
    {
        $params = $request->getQueryParams();

        // If there are no search parameters, fall back to regular pagination.
        if (empty($params) and ! array_key_exists('search', $params)) {
            return $this->paginate($request);
        }

        // Sanitize and extract the name search field text.
        $name = Warehouse::sanitize($params['name'] ?? '', ['max_length' => 32]);

        // If nothing was provided, redirect back to the pagination page.
        if (strlen($name) < 1) {
            return $this->paginate($request);
        }

        // Resolve pagination for the search results.
        $page    = (int) (array_key_exists('page', $params) ? $params['page'] : 1);
        $perPage = (int) (array_key_exists('perPage', $params) ? $params['perPage'] : 5);

        // Delegate the search to the service layer.
        $warehouses = $this->warehouseService->searchByName($name, $page, $perPage);

        // Get the number of matching warehouses for navigation.
        $total = $this->warehouseService->countByName($name);

        // Build the view data to be passed to the template.
        $viewData = [
            'view_title' => 'List of warehouses',
            'datetime'   => $this->datetime->format('l'),
            'warehouses' => $warehouses,
            'pagination' => static::pagination($page, $perPage, $total),
        ];

        return $this->render('Warehouse/paginate', $viewData)->withStatus(200);
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
        // The middleware is already part of every request!
        // So, in any controller or view I can access it with:
        $csrf  = $request->getAttribute('csrf');
        $token = $csrf->getToken();

        // POST request indicates form submission.
        if ($request->getMethod() === 'POST') {
            $parameters = $request->getParsedBody();

            // ------------- 1. Normalization ----------
            $name    = $parameters['name'] ?? '';
            $address = $parameters['address'] ?? '';
            $email   = $parameters['email'] ?? '';
            $type    = $parameters['warehouseType'] ?? '';

            // ------------- 2. Sanitization -----------
            $name    = htmlspecialchars((string) $name, ENT_QUOTES, 'UTF-8');
            $address = htmlspecialchars((string) $address, ENT_QUOTES, 'UTF-8');
            $email   = htmlspecialchars((string) $email, ENT_QUOTES, 'UTF-8');
            $type    = htmlspecialchars((string) $type, ENT_QUOTES, 'UTF-8');

            // ------------- 3. Validation ----------
            $errors = [];
            if ($name === null || $name === '') {
                $errors['name'] = 'Invalid name!';
            }
            if ($address === null || $address === '') {
                $errors['address'] = 'Invalid address!';
            }
            if ($email === null || $email === '') {
                $errors['email'] = 'Invalid email!';
            }
            if (! WarehouseTypeEnum::isValid($type)) {
                $errors['type'] = 'Invalid type!';
            }

            // If there are any errors, re-render the form.
            if ($errors) {
                return $this->render(
                    'Warehouse/create',
                    [
                        'view_title' => 'New warehouse',
                        'datetime'   => $this->datetime->format('l'),
                        'csrf_token' => $token,
                        'errors'     => $errors,
                        // Passes the already cleaned values ​​so the user does not have to re-enter them.
                        'form'       => [
                            'name'          => $name,
                            'address'       => $address,
                            'email'         => $email,
                            'warehouseType' => $type,
                        ],
                    ]
                );
            }

            // ------------- 4. Creation of the Warehouse ----------
            $warehouse = Warehouse::create();
            $warehouse->setName($name);
            $warehouse->setAddress($address);
            $warehouse->setEmail($email);
            $warehouse->setType($type);

            // Save the new warehouse using the service class, which expects an argument compatible with the model interface.
            $id = $this->warehouseService->create($warehouse);

            return $this->redirect("/warehouse/{$id}");
        }

        // Render the form for creating a new warehouse.
        return $this->render(
            'Warehouse/create',
            [
                'view_title' => 'New warehouse',
                'datetime'   => $this->datetime->format('l'),
                'csrf_token' => $token,
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
        // The middleware is already part of every request!
        // So, in any controller or view I can access it with:
        $csrf  = $request->getAttribute('csrf');
        $token = $csrf->getToken();

        // POST request indicates form submission.
        if ($request->getMethod() === 'POST') {

            $parameters = $request->getParsedBody();

            // ------------- 1. Normalization ----------
            $id      = $parameters['id'] ?? '';
            $name    = $parameters['name'] ?? '';
            $address = $parameters['address'] ?? '';
            $email   = $parameters['email'] ?? '';
            $type    = $parameters['warehouseType'] ?? '';

            // ------------- 2. Sanitization -----------
            $id      = htmlspecialchars((string) $id, ENT_QUOTES, 'UTF-8');
            $name    = htmlspecialchars((string) $name, ENT_QUOTES, 'UTF-8');
            $address = htmlspecialchars((string) $address, ENT_QUOTES, 'UTF-8');
            // Convert the string 'email' to a float.
            $email = htmlspecialchars((string) $email, ENT_QUOTES, 'UTF-8');
            // type is left raw for enum validation.
            $type = (string) $type;

            // ------------- 3. Validation ----------
            $errors = [];
            if ($name === null || $name === '') {
                $errors['name'] = 'Invalid name!';
            }
            if ($address === null || $address === '') {
                $errors['address'] = 'Invalid address!';
            }
            if ($email === null || $email === '') {
                $errors['email'] = 'Invalid email!';
            }
            if (! WarehouseTypeEnum::isValid($type)) {
                $errors['type'] = 'Invalid type!';
            }

            // If there are any errors, re-render the form.
            if ($errors) {
                return $this->render(
                    'Warehouse/update',
                    [
                        'view_title' => 'Edit warehouse',
                        'datetime'   => $this->datetime->format('l'),
                        'csrf_token' => $token,
                        'errors'     => $errors,
                        // Passes the already cleaned values ​​so the user does not have to re-enter them.
                        'form'       => [
                            'id'      => $id,
                            'name'    => $name,
                            'address' => $address,
                            'email'   => $email,
                            'type'    => $type,
                        ],
                    ]
                );
            }

            // ------------- 4. Update of the Warehouse ----------
            $warehouse = Warehouse::create();
            $warehouse->setId($id);
            $warehouse->setName($name);
            $warehouse->setAddress($address);
            $warehouse->setEmail($email);
            // Save the enum value, not the string representation.
            $warehouse->setType(WarehouseTypeEnum::from(strtolower($type))->value);

            // Update the database using the service method.
            $this->warehouseService->update($warehouse);

            // Redirect to the warehouse details page.
            return $this->redirect("/warehouse/{$warehouse->getId()}");
        }

        // Display the form with the current values.
        $id        = $args['id'] ?? null;
        $warehouse = $this->warehouseService->read($id);

        if ($warehouse === null) {
            // 404
            throw new NotFoundException();
        }

        return $this->render(
            'Warehouse/update',
            [
                'view_title' => 'Edit warehouse',
                'datetime'   => $this->datetime->format('l'),
                'csrf_token' => $token,
                'form'       => [
                    'id'      => $warehouse->getId(),
                    'name'    => $warehouse->getName(),
                    'address' => $warehouse->getAddress(),
                    'email'   => $warehouse->getEmail(),
                    'type'    => $warehouse->getType(),
                ],
            ]
        );
    }

    /**
     * delete
     *
     * @return ResponseInterface
     */
    public function delete(ServerRequestInterface $request, array $args): ResponseInterface
    {
        // The middleware is already part of every request!
        // So, in any controller or view I can access it with:
        $csrf  = $request->getAttribute('csrf');
        $token = $csrf->getToken();

        // POST indicates form submission for deleting.
        if ($request->getMethod() === 'POST') {
            $parameters = $request->getParsedBody();

            // Build an Warehouse instance with the updated values.
            $warehouse = Warehouse::create();
            $warehouse->setId(htmlspecialchars($parameters['id']));

            // Persist changes via the service.
            $deleted = $this->warehouseService->delete($warehouse->getId());

            // After deleted, display the updated warehouses.
            if ($deleted) {
                // Gets the URI of the request just made.
                $uri = $request->getUri();
                // I create a new immutable object to point the browser to the index page.
                $warehousesUri = $uri->withPath('/warehouses');
                // After deletion, show the paginate page.
                return $this->paginate($request->withUri($warehousesUri));
            } else {
                // Warehouse not found - 404
                throw new NotFoundException();
            }
        } else {
            // For GET request, fetch current data to pre-populate the form.
            $warehouse = $this->warehouseService->read($args['id']);

            if ($warehouse !== null) {
                return $this->render(
                    'Warehouse/delete',
                    [
                        'view_title' => 'Delete warehouse',
                        'datetime'   => $this->datetime->format('l'),
                        'id'         => $warehouse->getId(),
                        'name'       => $warehouse->getName(),
                        'address'    => $warehouse->getAddress(),
                        'email'      => $warehouse->getEmail(),
                        'type'       => $warehouse->getType(),
                        'csrf_token' => $token,
                    ]
                );
            } else {
                // 404
                throw new NotFoundException();
            }
        }
    }
}
