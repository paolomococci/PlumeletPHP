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
     */
    public function __construct(
        private DateTime $datetime,
        protected ItemService $itemService
    ) {}

    /**
     * index
     *
     * @return ResponseInterface
     */
    public function index(): ResponseInterface
    {
        // The service class can be used to retrieve the complete list of items.
        $items = $this->itemService->index();

        return $this->render(
            'Item/index',
            [
                'view_title' => 'List of items',
                'datetime'   => $this->datetime->format('l'),
                'items'      => $items,
            ]
        )->withStatus(200);
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
            $item       = new Item(
                null,
                $parameters['name'],
                $parameters['description'],
                (float) $parameters['price'],
                $parameters['currency'],
                null,
                null
            );
            // Save the new item using the service class, which expects an argument compatible with the model interface.
            $id = $this->itemService->create($item);

            return $this->redirect("/item/{$id}");
        }

        // Returns the content of the body as a string.
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
     * @return ResponseInterface
     */
    public function read(ServerRequestInterface $request, array $args): ResponseInterface
    {
        // Retrieve a specific item using the service class.
        $item = $this->itemService->read($args['id']);

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
            $item       = new Item(
                $parameters['id'],
                $parameters['name'],
                $parameters['description'],
                (float) $parameters['price'],
                $parameters['currency'],
                $parameters['created_at'] ?? null,
                $parameters['updated_at'] ?? null
            );

            // Apply the changes using the service class.
            $id = $this->itemService->update($item);

            $id = $item->getId();
            return self::read($request, ['id' => $id]);
        } else {

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
        // Delete the item using the service class.
        $deleted = $this->itemService->delete($args['id']);

        if ($deleted) {
            return self::index();
        } else {
            throw new NotFoundException();
        }

    }
}
