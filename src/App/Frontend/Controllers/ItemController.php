<?php

declare(strict_types=1); // Enforce strict type checking

namespace App\Frontend\Controllers;

use App\Backend\Models\Enums\CurrencyEnum;
use App\Backend\Models\Item;
use App\Frontend\Controllers\Controller;
use App\Frontend\Controllers\Interfaces\CrudInterface;
use App\Frontend\Services\ItemService;
use DateTime;
use League\Route\Http\Exception\NotFoundException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * ItemController
 *
 * According to SOLID principles, the component should only
 * be responsible for receiving HTTP requests,
 * delegating the business logic to the service,
 * and returning the appropriate response.
 *
 */
final class ItemController extends Controller implements CrudInterface
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
        protected ItemService $itemService
    ) {}

    /* --------------------------------------------------------------------- */
    /*  INDEX  ------------------------------------------------------------- */
    /* --------------------------------------------------------------------- */

    /**
     * index
     *
     * @return ResponseInterface
     *
     * Handles a GET request for the root resource (e.g. /items).
     * Delegates data retrieval to the service and renders the list view.
     */
    public function index(): ResponseInterface
    {
        // Retrieve the full list of items via the service layer.
        $items = $this->itemService->index();

        // Render the template and pass necessary data.
        return $this->render(
            'Item/index',
            [
                'view_title' => 'List of items',
                'datetime'   => $this->datetime->format('l'),
                'items'      => $items,
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
     * Handles a request that requires pagination.
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

        // Configurable items per page.
        $perPage = 5;
        $items   = $this->itemService->paginate($page, $perPage);

        // Get total count (used for navigation)
        $total = $this->itemService->count();

        // Build the view data
        $viewData = [
            'view_title' => 'List of items',
            'datetime'   => $this->datetime->format('l'),
            'items'      => $items,
            'pagination' => static::pagination($page, $perPage, $total),
        ];

        return $this->render('Item/paginate', $viewData)->withStatus(200);
    }

    /**
     * pagination
     *
     * Helper to build pagination data.
     *
     * Computes next/previous page values and overall navigation data.
     *
     * @param  mixed $page
     * @param  mixed $perPage
     * @param  mixed $total
     * @return array
     */
    private static function pagination(int $page, int $perPage, int $total): array
    {
        // Total number of pages.
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
     * Handles search queries on items.
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
        $name = Item::sanitize($params['name'] ?? '', ['max_length' => 32]);

        // If nothing was provided, redirect back to the pagination page.
        if (strlen($name) < 1) {
            return $this->paginate($request);
        }

        // Resolve pagination for the search results.
        $page    = (int) (array_key_exists('page', $params) ? $params['page'] : 1);
        $perPage = (int) (array_key_exists('perPage', $params) ? $params['perPage'] : 5);

        // Delegate the search to the service layer.
        $items = $this->itemService->searchByName($name, $page, $perPage);

        // Get the number of matching items for navigation.
        $total = $this->itemService->countByName($name);

        // Build the view data to be passed to the template.
        $viewData = [
            'view_title' => 'List of items',
            'datetime'   => $this->datetime->format('l'),
            'items'      => $items,
            'pagination' => static::pagination($page, $perPage, $total),
        ];

        return $this->render('Item/paginate', $viewData)->withStatus(200);
    }

    /* --------------------------------------------------------------------- */
    /*  CRUD methods ------------------------------------------------------- */
    /* --------------------------------------------------------------------- */

    /**
     * create
     *
     * Handles GET for showing the create form and POST for actually creating.
     *
     * @return ResponseInterface
     */
    public function create(ServerRequestInterface $request): ResponseInterface
    {
        // The middleware is already part of every request!
        // So, in any controller or view I can access it with:
        $csrf = $request->getAttribute('csrf');
        $token = $csrf->getToken();

        // POST request indicates form submission.
        if ($request->getMethod() === 'POST') {
            $parameters = $request->getParsedBody();

            // ------------- 1. Normalization ----------
            $name        = $parameters['name']        ?? '';
            $description = $parameters['description'] ?? '';
            $price       = $parameters['price']       ?? '';
            $currency    = $parameters['currency']    ?? '';

            // ------------- 2. Sanitization -----------
            $name        = htmlspecialchars((string) $name, ENT_QUOTES, 'UTF-8');
            $description = htmlspecialchars((string) $description, ENT_QUOTES, 'UTF-8');
            $price       = (float) htmlspecialchars((string) $price, ENT_QUOTES, 'UTF-8');
            // Keep raw for validation.
            $currency    = (string) $currency;

            // ------------- 3. Enum validation ----------
            if (!CurrencyEnum::isValid($currency)) {
                // Error: does not create item, redirects form with message.
                $errors = [
                    'currency' => "Invalid currency!",
                ];

                return $this->render(
                    'Item/create',
                    [
                        'view_title' => 'New item',
                        'datetime'   => $this->datetime->format('l'),
                        'csrf_token' => $token,
                        'errors'     => $errors,
                        // Passes the already cleaned values ​​so the user does not have to re-enter them.
                        'form'       => [
                            'name'        => $name,
                            'description' => $description,
                            'price'       => $price,
                            'currency'    => $currency,
                        ],
                    ]
                );
            }

            // ------------- 4. Creation of the Item ----------
            $item = Item::create();
            $item->setName($name);
            $item->setDescription($description);
            $item->setPrice($price);
            // Save the enum value, not the string representation.
            $item->setCurrency(CurrencyEnum::from(strtoupper($currency))->value);

            $id = $this->itemService->create($item);
            return $this->redirect("/item/{$id}");
        }

        // Render the form for creating a new item.
        return $this->render(
            'Item/create',
            [
                'view_title' => 'New item',
                'datetime'   => $this->datetime->format('l'),
                'csrf_token' => $token,
            ]
        );
    }

    /**
     * read
     *
     * Displays the details of a single item.
     *
     * @return ResponseInterface
     */
    public function read(ServerRequestInterface $request, array $args): ResponseInterface
    {
        // Retrieve the specific item using its id.
        $item = $this->itemService->read($args['id']);

        // If found, render the detail view; otherwise throw 404.
        if ($item !== null) {
            return $this->render(
                'Item/read',
                [
                    'view_title'  => 'Item details',
                    'datetime'    => $this->datetime->format('l'),
                    'id'          => $item->getId(),
                    'name'        => $item->getName(),
                    'price'       => $item->getPrice(),
                    'currency'    => $item->getCurrency(),
                    'description' => $item->getDescription(),
                ]
            )->withStatus(200);
        } else {
            // 404
            throw new NotFoundException();
        }
    }

    /**
     * update
     *
     * Handles GET to display the edit form and POST to apply changes.
     *
     * @return ResponseInterface
     */
    public function update(ServerRequestInterface $request, array $args): ResponseInterface
    {
        // The middleware is already part of every request!
        // So, in any controller or view I can access it with:
        $csrf = $request->getAttribute('csrf');
        $token = $csrf->getToken();

        // POST request indicates form submission.
        if ($request->getMethod() === 'POST') {

            $parameters = $request->getParsedBody();

            // ------------- 1. Normalization ----------
            $id          = $parameters['id']          ?? '';
            $name        = $parameters['name']        ?? '';
            $description = $parameters['description'] ?? '';
            $price       = $parameters['price']       ?? '';
            $currency    = $parameters['currency']    ?? '';

            // ------------- 2. Sanitization -----------
            $id          = htmlspecialchars((string) $id,        ENT_QUOTES, 'UTF-8');
            $name        = htmlspecialchars((string) $name,      ENT_QUOTES, 'UTF-8');
            $description = htmlspecialchars((string) $description, ENT_QUOTES, 'UTF-8');
            // Convert the string 'price' to a float.
            $price       = (float) htmlspecialchars((string) $price, ENT_QUOTES, 'UTF-8');
            // currency è lasciato “raw” per la validazione enum.
            $currency    = (string) $currency;

            // ------------- 3. Enum validation ----------
            $errors = [];
            if (!CurrencyEnum::isValid($currency)) {
                $errors['currency'] = 'Invalid currency!';
            }

            // If there are any errors, re-render the form.
            if ($errors) {
                return $this->render(
                    'Item/update',
                    [
                        'view_title'  => 'Modifica articolo',
                        'datetime'    => $this->datetime->format('l'),
                        'csrf_token'  => $token,
                        'errors'      => $errors,
                        // Passes the already cleaned values ​​so the user does not have to re-enter them.
                        'form'        => [
                            'id'          => $id,
                            'name'        => $name,
                            'description' => $description,
                            'price'       => $price,
                            'currency'    => $currency,
                        ],
                    ]
                );
            }

            // ------------- 4. Update of the Item ----------
            $item = Item::create();
            $item->setId($id);
            $item->setName($name);
            $item->setDescription($description);
            $item->setPrice($price);
            // Save the enum value, not the string representation.
            $item->setCurrency(CurrencyEnum::from(strtoupper($currency))->value);

            // Update the database using the service method.
            $this->itemService->update($item);

            // Redirect to the item details page.
            return $this->redirect("/item/{$item->getId()}");
        }

        // Display the form with the current values.
        $id  = $args['id'] ?? null;
        $item = $this->itemService->read($id);

        if ($item === null) {
            // 404
            throw new NotFoundException();
        }

        return $this->render(
            'Item/update',
            [
                'view_title'  => 'Modifica articolo',
                'datetime'    => $this->datetime->format('l'),
                'csrf_token'  => $token,
                'form'        => [
                    'id'          => $item->getId(),
                    'name'        => $item->getName(),
                    'description' => $item->getDescription(),
                    'price'       => $item->getPrice(),
                    'currency'    => $item->getCurrency(),
                ],
            ]
        );
    }

    /**
     * delete
     *
     * Handles deletion of an item identified by id.
     *
     * @return ResponseInterface
     */
    public function delete(ServerRequestInterface $request, array $args): ResponseInterface
    {
        // The middleware is already part of every request!
        // So, in any controller or view I can access it with:
        $csrf = $request->getAttribute('csrf');
        $token = $csrf->getToken();

        // POST indicates form submission for deleting.
        if ($request->getMethod() === 'POST') {
            $parameters = $request->getParsedBody();

            // Build an Item instance with the updated values.
            $item = Item::create();
            $item->setId(htmlspecialchars($parameters['id']));

            // Persist changes via the service.
            $deleted = $this->itemService->delete($item->getId());

            // After deleted, display the updated items.
            if ($deleted) {
                // Gets the URI of the request just made.
                $uri = $request->getUri();
                // I create a new immutable object to point the browser to the index page.
                $itemsUri = $uri->withPath('/items');
                // After deletion, show the paginate page.
                return $this->paginate($request->withUri($itemsUri));
            } else {
                // Item not found - 404
                throw new NotFoundException();
            }
        } else {
            // For GET request, fetch current data to pre-populate the form.
            $item = $this->itemService->read($args['id']);

            if ($item !== null) {
                return $this->render(
                    'Item/delete',
                    [
                        'view_title'  => 'Delete item',
                        'datetime'    => $this->datetime->format('l'),
                        'id'          => $item->getId(),
                        'name'        => $item->getName(),
                        'price'       => $item->getPrice(),
                        'currency'    => $item->getCurrency(),
                        'description' => $item->getDescription(),
                        'csrf_token'  => $token,
                    ]
                );
            } else {
                // 404
                throw new NotFoundException();
            }
        }
    }
}
