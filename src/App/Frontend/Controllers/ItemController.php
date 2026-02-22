<?php

declare (strict_types = 1); // Enforce strict type checking

namespace App\Frontend\Controllers;

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
        if ($page < 1) {$page = 1;}

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
        // POST request indicates form submission.
        if ($request->getMethod() === 'POST') {
            $parameters = $request->getParsedBody();

            // Build a new Item instance from submitted data.
            $item = Item::create();
            $item->setName(htmlspecialchars($parameters['name']));
            $item->setDescription(htmlspecialchars($parameters['description']));
            $item->setPrice((float) htmlspecialchars($parameters['price']));
            $item->setCurrency(htmlspecialchars($parameters['currency']));

            // Persist the new item via the service; returns the new id.
            $id = $this->itemService->create($item);

            // Redirect to the newly created item’s detail page.
            return $this->redirect("/item/{$id}");
        }

        // Render the form for creating a new item.
        return $this->render(
            'Item/create',
            [
                'view_title' => 'New item',
                'datetime'   => $this->datetime->format('l'),
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
        // POST indicates form submission for updating.
        if ($request->getMethod() === 'POST') {

            $parameters = $request->getParsedBody();

            // Build an Item instance with the updated values.
            $item = Item::create();
            $item->setId(htmlspecialchars($parameters['id']));
            $item->setName(htmlspecialchars($parameters['name']));
            $item->setDescription(htmlspecialchars($parameters['description']));
            $item->setPrice((float) htmlspecialchars($parameters['price']));
            $item->setCurrency(htmlspecialchars($parameters['currency']));

            // Persist changes via the service.
            $id = $this->itemService->update($item);

            // After updating, display the updated item.
            $id = $item->getId();
            return $this->read($request, ['id' => $id]);
        } else {
            // For GET request, fetch current data to pre-populate the form.
            $item = $this->itemService->read($args['id']);

            if ($item !== null) {
                return $this->render(
                    'Item/update',
                    [
                        'view_title'  => 'Edit item',
                        'datetime'    => $this->datetime->format('l'),
                        'id'          => $item->getId(),
                        'name'        => $item->getName(),
                        'price'       => $item->getPrice(),
                        'currency'    => $item->getCurrency(),
                        'description' => $item->getDescription(),
                    ]
                );
            } else {
                // 404
                throw new NotFoundException();
            }
        }
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
        // Delegate deletion to the service layer.
        $deleted = $this->itemService->delete($args['id']);

        if ($deleted) {
            // After deletion, show the index page.
            return $this->index();
        } else {
            // Item not found - 404
            throw new NotFoundException();
        }

    }
}
