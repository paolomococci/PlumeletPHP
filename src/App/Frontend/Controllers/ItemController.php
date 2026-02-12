<?php

declare (strict_types = 1); // Enforce strict type checking

namespace App\Frontend\Controllers;

use App\Backend\Connections\PlumeletPhpDb;
use App\Backend\Models\Item;
use App\Errors\InternalServerError;
use App\Frontend\Controllers\Interfaces\CrudInterface;
use DateTime;
use InvalidArgumentException;
use League\Route\Http\Exception\NotFoundException;
use PDO;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * ItemController
 */
class ItemController extends Controller implements CrudInterface
{
    /**
     * __construct
     *
     * @return void
     */
    public function __construct(
        private DateTime $datetime
    ) {}

    /**
     * index
     *
     * @return ResponseInterface
     */
    public function index(): ResponseInterface
    {
        $pdo = PlumeletPhpDb::getPdo();

        $statement = $pdo->query('SELECT id, name, price, description, currency, created_at, updated_at FROM plumeletphp_db.items_tbl');

        // When the query fails, $statement will be false.
        if ($statement === false) {
            throw new InternalServerError('Unable to fetch items from ItemController::index function.');
        }

        $statement->setFetchMode(PDO::FETCH_CLASS, Item::class);

        // fetchAll() Always returns an array.
        $items = $statement->fetchAll() ?? [];
        // \App\Util\Handlers\VarDebugHandler::varDump($items);

        return $this->render(
            'Item/index',
            [
                'view_title' => 'List of items',
                'datetime'   => $this->datetime->format('l'),
                'items'      => $items,
            ]
        );
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
            $pdo        = PlumeletPhpDb::getPDO();
            $parameters = $request->getParsedBody();
            $item       = new Item;
            $item->setName($parameters['name']);
            $item->setPrice((float) $parameters['price']);
            $item->setCurrency($parameters['currency']);
            $item->setDescription($parameters['description']);
            // parametrized SQL for create data to the database
            $statement = $pdo->prepare("INSERT INTO plumeletphp_db.items_tbl (name, price, currency, description) VALUES (:name, :price, :currency, :description)");
            $statement->execute([
                ':name'        => $item->getName(),
                ':price'       => $item->getPrice(),
                ':currency'    => $item->getCurrency(),
                ':description' => $item->getDescription(),
            ]);
            // $id is correctly populated with the last automatically incremented ID of the latest inserted record.
            $id = $pdo->lastInsertId();
            // \App\Util\Handlers\VarDebugHandler::varDump($id);
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
        $pdo = PlumeletPhpDb::getPDO();

        $statement = $pdo->prepare("SELECT * FROM plumeletphp_db.items_tbl WHERE id = :id LIMIT 1");
        $statement->execute([':id' => $args['id']]);

        // If the query fails, the value of $statement will be false.
        if ($statement === false) {
            throw new InternalServerError('Unable to fetch items from ItemController::read function.');
        }

        $statement->setFetchMode(PDO::FETCH_CLASS, Item::class);

        // fetchById() Always returns an array as a result.
        $items = $statement->fetchAll() ?? [];

        if (! empty($items)) {
            return $this->render(
                'Item/read',
                [
                    'view_title'  => 'Item details',
                    'datetime'    => $this->datetime->format('l'),
                    'id'          => $items[0]->getId(),
                    'name'        => $items[0]->getName(),
                    'price'       => $items[0]->getPrice(),
                    'currency'    => $items[0]->getCurrency(),
                    'description' => $items[0]->getDescription(),
                ]
            );
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

            $pdo = PlumeletPhpDb::getPDO();

            $parameters = $request->getParsedBody();
            $item       = new Item;
            try {
                $item->setId($parameters['id']);
            } catch (InvalidArgumentException $e) {
                $e->getMessage();
            }
            $item->setName($parameters['name']);
            $item->setPrice((float) $parameters['price']);
            $item->setCurrency($parameters['currency']);
            $item->setDescription($parameters['description']);
            $statement = $pdo->prepare("UPDATE plumeletphp_db.items_tbl SET name=:name, price=:price, currency=:currency, description=:description WHERE id=:id");

            $statement->execute([
                ':id'          => $item->getId(),
                ':name'        => $item->getName(),
                ':price'       => $item->getPrice(),
                ':currency'    => $item->getCurrency(),
                ':description' => $item->getDescription(),
            ]);

            $id = $item->getId();
            return self::read($request, ['id' => $id]);
        } else {

            $pdo = PlumeletPhpDb::getPDO();

            $statement = $pdo->prepare("SELECT id, name, price, description FROM plumeletphp_db.items_tbl WHERE id = :id LIMIT 1");
            $statement->execute([':id' => $args['id']]);
            $statement->setFetchMode(PDO::FETCH_CLASS, Item::class);
            $items = $statement->fetchAll();
            // \App\Util\Handlers\VarDebugHandler::varDump($items);

            if (count($items) > 0) {
                return $this->render(
                    'Item/update',
                    [
                        'view_title'  => 'Edit item',
                        'datetime'    => $this->datetime->format('l'),
                        'id'          => $items[0]->getId(),
                        'name'        => $items[0]->getName(),
                        'price'       => $items[0]->getPrice(),
                        'currency'    => $items[0]->getCurrency(),
                        'description' => $items[0]->getDescription(),
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
        $pdo = PlumeletPhpDb::getPDO();

        $statement = $pdo->prepare("DELETE FROM plumeletphp_db.items_tbl WHERE id = :id");
        $statement->execute([':id' => $args['id']]);
        return self::index();
    }
}
