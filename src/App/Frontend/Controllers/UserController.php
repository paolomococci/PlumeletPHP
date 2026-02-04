<?php

declare (strict_types = 1); // Enforce strict type checking

namespace App\Frontend\Controllers;

use App\Backend\Connections\PlumeletPhpDb;
use App\Backend\Models\User;
use App\Errors\InternalServerError;
use App\Frontend\Controllers\Interfaces\CrudInterface;
use DateTime;
use InvalidArgumentException;
use League\Route\Http\Exception\NotFoundException;
use PDO;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * UserController
 */
class UserController extends Controller implements CrudInterface
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

        $statement = $pdo->query('select  id, name, email, created_at, updated_at from plumeletphp_db.users_tbl');

        // When the query fails, $statement will be false.
        if ($statement === false) {
            throw new InternalServerError('Unable to fetch users from UserController::index function.');
        }

        $statement->setFetchMode(PDO::FETCH_CLASS, User::class);

        // fetchAll() Always returns an array.
        $users = $statement->fetchAll() ?? [];
        // \App\Util\Handlers\VarDebugHandler::varDump($users);

        return $this->render(
            'User/index',
            [
                'view_title' => 'List of users',
                'datetime'   => $this->datetime->format('l'),
                'users'      => $users,
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
            $user       = new User;
            $user->setName($parameters['name']);
            $user->setEmail($parameters['email']);
            // $user->setPlainPassword($parameters['password']);
            $user->setHashedPassword($parameters['password']);
            // \App\Util\Handlers\VarDebugHandler::varDump($user);
            // parametrized SQL for create data to the database
            $statement = $pdo->prepare("insert into plumeletphp_db.users_tbl (name, email, password_hash) values (:name, :email, :password_hash)");
            $statement->execute([
                ':name'  => $user->getName(),
                ':email' => $user->getEmail(),
                ':password_hash' => $user->getHashedPassword(),
            ]);
            // $id is correctly populated with the last automatically incremented ID of the latest inserted record.
            $id = $pdo->lastInsertId();
            // \App\Util\Handlers\VarDebugHandler::varDump($id);
            return $this->redirect("/user/{$id}");
        }

        // Returns the content of the body as a string.
        return $this->render(
            'User/create',
            [
                'view_title' => 'New user',
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

        $statement = $pdo->prepare("select * from plumeletphp_db.users_tbl where id = :id limit 1");
        $statement->execute([':id' => $args['id']]);

        // If the query fails, the value of $statement will be false.
        if ($statement === false) {
            throw new InternalServerError('Unable to fetch users from UserController::read function.');
        }

        $statement->setFetchMode(PDO::FETCH_CLASS, User::class);

        // fetchById() Always returns an array as a result.
        $users = $statement->fetchAll() ?? [];

        if (! empty($users)) {
            return $this->render(
                'User/read',
                [
                    'view_title' => 'User details',
                    'datetime'   => $this->datetime->format('l'),
                    'id'         => $users[0]->getId(),
                    'name'       => $users[0]->getName(),
                    'email'      => $users[0]->getEmail(),
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
            $user       = new User;
            try {
                $user->setId($parameters['id']);
            } catch (InvalidArgumentException $e) {
                $e->getMessage();
            }
            $user->setName($parameters['name']);
            $user->setEmail($parameters['email']);
            $statement = $pdo->prepare("update plumeletphp_db.users_tbl set name=:name, email=:email where id=:id");

            $statement->execute([
                ':id'    => $user->getId(),
                ':name'  => $user->getName(),
                ':email' => $user->getEmail(),
            ]);

            $id = $user->getId();
            return self::read($request, ['id' => $id]);
        } else {

            $pdo = PlumeletPhpDb::getPDO();

            $statement = $pdo->prepare("select id, name, email from plumeletphp_db.users_tbl where id = :id limit 1");
            $statement->execute([':id' => $args['id']]);
            $statement->setFetchMode(PDO::FETCH_CLASS, User::class);
            $users = $statement->fetchAll();
            // \App\Util\Handlers\VarDebugHandler::varDump($users);

            if (count($users) > 0) {
                return $this->render(
                    'User/update',
                    [
                        'view_title' => 'Edit user',
                        'datetime'   => $this->datetime->format('l'),
                        'id'         => $users[0]->getId(),
                        'name'       => $users[0]->getName(),
                        'email'      => $users[0]->getEmail(),
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

        $statement = $pdo->prepare("delete from plumeletphp_db.users_tbl where id = :id");
        $statement->execute([':id' => $args['id']]);
        return self::index();
    }
}
