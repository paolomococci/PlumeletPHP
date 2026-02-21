<?php

declare (strict_types = 1); // Enforce strict type checking

namespace App\Frontend\Controllers;

use App\Backend\Models\User;
use App\Frontend\Controllers\Controller;
use App\Frontend\Controllers\Interfaces\CrudInterface;
use App\Frontend\Services\UserService;
use DateTime;
use League\Route\Http\Exception\NotFoundException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * UserController
 *
 * According to SOLID principles, the component should only
 * be responsible for receiving HTTP requests,
 * delegating the business logic to the service,
 * and returning the appropriate response.
 *
 */
final class UserController extends Controller implements CrudInterface
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
        protected UserService $userService
    ) {}

    /* --------------------------------------------------------------------- */
    /*  INDEX  ------------------------------------------------------------- */
    /* --------------------------------------------------------------------- */

    /**
     * index
     *
     * @return ResponseInterface
     */
    public function index(): ResponseInterface
    {
        // The service class can be used to retrieve the complete list of users.
        $users = $this->userService->index();

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
        if ($page < 1) {$page = 1;}

        $perPage = 5; // configurable
        $users   = $this->userService->paginate($page, $perPage);

        // Get total count (used for navigation)
        $total = $this->userService->count();

        // Build the view data
        $viewData = [
            'view_title' => 'List of users',
            'datetime'   => $this->datetime->format('l'),
            'users'      => $users,
            'pagination' => static::pagination($page, $perPage, $total),
        ];

        return $this->render('User/paginate', $viewData)->withStatus(200);
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
     * Handles search queries on users.
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
        $name = User::sanitize($params['name'] ?? '', ['max_length' => 32]);

        // If nothing was provided, redirect back to the pagination page.
        if (strlen($name) < 1) {
            return $this->paginate($request);
        }

        // Resolve pagination for the search results.
        $page    = (int) (array_key_exists('page', $params) ? $params['page'] : 1);
        $perPage = (int) (array_key_exists('perPage', $params) ? $params['perPage'] : 5);

        // Delegate the search to the service layer.
        $users = $this->userService->searchByName($name, $page, $perPage);

        // Get the number of matching users for navigation.
        $total = $this->userService->countByName($name);

        // Build the view data to be passed to the template.
        $viewData = [
            'view_title' => 'List of users',
            'datetime'   => $this->datetime->format('l'),
            'users'      => $users,
            'pagination' => static::pagination($page, $perPage, $total),
        ];

        return $this->render('User/paginate', $viewData)->withStatus(200);
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
            $user       = new User(
                null,
                $parameters['name'],
                $parameters['email'],
                $parameters['password'],
                null,
                null,
                null
            );
            // Save the new user using the service class, which expects an argument compatible with the model interface.
            $id = $this->userService->create($user);

            return $this->redirect("/user/{$id}");
        }

        // Returns the content of the body as a string.
        return $this->render(
            'User/create',
            [
                'view_title' => 'New user',
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
        // Retrieve a specific user using the service class.
        $user = $this->userService->read($args['id']);

        if ($user !== null) {
            return $this->render(
                'User/read',
                [
                    'view_title' => 'User details',
                    'datetime'   => $this->datetime->format('l'),
                    'id'         => $user->getId(),
                    'name'       => $user->getName(),
                    'email'      => $user->getEmail(),
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
            $user       = new User(
                $parameters['id'],
                $parameters['name'],
                $parameters['email'],
                $parameters['password'],
                null,
                null,
                null
            );

            // Apply the changes using the service class.
            $id = $this->userService->update($user);

            $id = $user->getId();
            return $this->read($request, ['id' => $id]);
        } else {

            $user = $this->userService->read($args['id']);

            if ($user !== null) {
                return $this->render(
                    'User/update',
                    [
                        'view_title' => 'Edit user',
                        'datetime'   => $this->datetime->format('l'),
                        'id'         => $user->getId(),
                        'name'       => $user->getName(),
                        'email'      => $user->getEmail(),
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
        // Delete the user using the service class.
        $deleted = $this->userService->delete($args['id']);

        if ($deleted) {
            return $this->index();
        } else {
            throw new NotFoundException();
        }

    }
}
