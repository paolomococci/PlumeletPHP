<?php

declare (strict_types = 1); // Enforce strict type checking

namespace App\Frontend\Controllers;

use App\Backend\Connections\PlumeletPhpDb;
use App\Backend\Models\Enums\WarehouseType;
use App\Backend\Models\Warehouse;
use App\Errors\InternalServerError;
use App\Frontend\Controllers\Interfaces\CrudInterface;
use DateTime;
use InvalidArgumentException;
use League\Route\Http\Exception\NotFoundException;
use PDO;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * WarehouseController
 */
class WarehouseController extends Controller implements CrudInterface
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

        $statement = $pdo->query('select id, name, address, email, type, created_at, updated_at from plumeletphp_db.warehouses_tbl');

        // If the query fails.
        if ($statement === false) {
            throw new InternalServerError('Unable to fetch warehouses from WarehouseController::index function.');
        }

        $statement->setFetchMode(PDO::FETCH_CLASS, Warehouse::class);

        // fetchAll() always returns a result in the form of an array.
        $warehouses = $statement->fetchAll() ?? [];
        // \App\Util\Handlers\VarDebugHandler::varDump($warehouses);

        return $this->render(
            'Warehouse/index',
            [
                'view_title' => 'List of warehouses',
                'datetime'   => $this->datetime->format('l'),
                'warehouses' => $warehouses,
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
            $pdo        = PlumeletPhpDb::getPDO();
            $parameters = $request->getParsedBody();

            // Validate enum
            // PHP >= 8.1: `tryFrom` is a built‑in static method that PHP automatically generates for every backed enum.
            // When the engine compiles this enum it creates the following magic methods:
            // WarehouseType::cases(): array<WarehouseType>, returns an array with all defined cases.
            // WarehouseType::from(string $value): WarehouseType, throws `ValueError` if `$value` is not one of the case values.
            // WarehouseType::tryFrom(string $value): ?WarehouseType, returns the matching enum case or **`null`** if the value is invalid.
            $type = WarehouseType::tryFrom($parameters['warehouseType'])->value;
            if ($type === null) {
                // If the value is invalid, can I render the page with an error message, 
                // or should I return a 400 HTTP error Bad Request.
                return $this->render('Warehouse/create', [
                    'view_title' => 'New warehouse',
                    'datetime'   => $this->datetime->format('l'),
                    // To fill the form again.
                    'parameters' => $parameters,
                    // 'error'      => 'The specified warehouse type is invalid.',
                ]);
            }

            $warehouse = new Warehouse;
            $warehouse->setName($parameters['name']);
            $warehouse->setAddress($parameters['address']);
            $warehouse->setEmail($parameters['email']);
            $warehouse->setType($type);

            // \App\Util\Handlers\VarDebugHandler::varDump($warehouse);
            // parametrized SQL for create data to the database
            $statement = $pdo->prepare("insert into plumeletphp_db.warehouses_tbl (name, address, email, type) values (:name, :address, :email, :type)");
            $statement->execute([
                ':name'    => $warehouse->getName(),
                ':address' => $warehouse->getAddress(),
                ':email'   => $warehouse->getEmail(),
                ':type'    => $warehouse->getType(),
            ]);
            // $id is correctly populated with the last automatically incremented ID of the latest inserted record.
            $id = $pdo->lastInsertId();
            // \App\Util\Handlers\VarDebugHandler::varDump($id);
            return $this->redirect("/warehouse/{$id}");
        }

        // Returns the content of the body as a string.
        return $this->render(
            'Warehouse/create',
            [
                'view_title' => 'New warehouse',
                'datetime'   => $this->datetime->format('l'),
            ]
        )->withStatus(201);
    }

    /**
     * read
     *
     * @return ResponseInterface
     */
    public function read(ServerRequestInterface $request, array $args): ResponseInterface
    {
        $pdo = PlumeletPhpDb::getPDO();

        $statement = $pdo->prepare("select * from plumeletphp_db.warehouses_tbl where id = :id limit 1");
        $statement->execute([':id' => $args['id']]);

        // If the query fails, the value of $statement will be false.
        if ($statement === false) {
            throw new InternalServerError('Unable to fetch warehouses from WarehouseController::read function.');
        }

        $statement->setFetchMode(PDO::FETCH_CLASS, Warehouse::class);

        // fetchById() Always returns an array as a result.
        $warehouses = $statement->fetchAll() ?? [];

        if (! empty($warehouses)) {
            return $this->render(
                'Warehouse/read',
                [
                    'view_title' => 'Warehouse details',
                    'datetime'   => $this->datetime->format('l'),
                    'id'         => $warehouses[0]->getId(),
                    'name'       => $warehouses[0]->getName(),
                    'address'    => $warehouses[0]->getAddress(),
                    'email'      => $warehouses[0]->getEmail(),
                    'type'       => $warehouses[0]->getType(),
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

            $pdo = PlumeletPhpDb::getPDO();

            $parameters = $request->getParsedBody();
            $warehouse  = new Warehouse;
            try {
                $warehouse->setId($parameters['id']);
            } catch (InvalidArgumentException $e) {
                $e->getMessage();
            }
            $warehouse->setName($parameters['name']);
            $warehouse->setAddress($parameters['address']);
            $warehouse->setEmail($parameters['email']);
            $warehouse->setType($parameters['warehouseType']);
            $statement = $pdo->prepare("update plumeletphp_db.warehouses_tbl set name=:name, address=:address, email=:email, type=:type where id=:id");

            $statement->execute([
                ':id'      => $warehouse->getId(),
                ':name'    => $warehouse->getName(),
                ':address' => $warehouse->getAddress(),
                ':email'   => $warehouse->getEmail(),
                ':type'    => $warehouse->getType(),
            ]);

            $id = $warehouse->getId();
            return self::read($request, ['id' => $id]);
        } else {

            $pdo = PlumeletPhpDb::getPDO();

            $statement = $pdo->prepare("select id, name, address, email, type from plumeletphp_db.warehouses_tbl where id = :id limit 1");
            $statement->execute([':id' => $args['id']]);
            $statement->setFetchMode(PDO::FETCH_CLASS, Warehouse::class);
            $warehouses = $statement->fetchAll();
            // \App\Util\Handlers\VarDebugHandler::varDump($warehouses);

            if (count($warehouses) > 0) {
                return $this->render(
                    'Warehouse/update',
                    [
                        'view_title' => 'Edit warehouse',
                        'datetime'   => $this->datetime->format('l'),
                        'id'         => $warehouses[0]->getId(),
                        'name'       => $warehouses[0]->getName(),
                        'address'    => $warehouses[0]->getAddress(),
                        'email'      => $warehouses[0]->getEmail(),
                        'type'       => $warehouses[0]->getType(),
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

        $statement = $pdo->prepare("delete from plumeletphp_db.warehouses_tbl where id = :id");
        $statement->execute([':id' => $args['id']]);
        return self::index();
    }
}
