<?php

declare(strict_types=1); // Enforce strict type checking

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
     * Handles a GET request for the root resource (e.g. /users).
     * Delegates data retrieval to the service and renders the list view.
     *
     * @return ResponseInterface
     */
    public function index(): ResponseInterface
    {
        // The service class can be used to retrieve the complete list of users.
        $users = $this->userService->index();

        // Render the template and pass necessary data.
        return $this->render(
            'User/index',
            [
                'view_title' => 'List of users',
                'datetime'   => $this->datetime->format('l'),
                'users'      => $users,
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
        // The middleware is already part of every request!
        // So, in any controller or view I can access it with:
        $csrf  = $request->getAttribute('csrf');
        $token = $csrf->getToken();

        // POST request indicates form submission.
        if ($request->getMethod() === 'POST') {
            $parameters = $request->getParsedBody();

            // ------------- 1. Normalization ----------
            $name     = $parameters['name'] ?? '';
            $email    = $parameters['email'] ?? '';
            $password = $parameters['password'] ?? '';

            // ------------- 2. Sanitization -----------
            $name     = htmlspecialchars((string) $name, ENT_QUOTES, 'UTF-8');
            $email    = htmlspecialchars((string) $email, ENT_QUOTES, 'UTF-8');
            $password = htmlspecialchars((string) $password, ENT_QUOTES, 'UTF-8');

            // ------------- 3. Validation ----------
            $errors = [];
            if ($name === null || $name === '') {
                $errors['name'] = 'Invalid name!';
            }
            if ($email === null || $email === '') {
                $errors['email'] = 'Invalid email!';
            }
            if ($password === null || $password === '') {
                $errors['password'] = 'Invalid password!';
            }

            // If there are any errors, re-render the form.
            if ($errors) {
                return $this->render(
                    'User/create',
                    [
                        'view_title' => 'New user',
                        'datetime'   => $this->datetime->format('l'),
                        'csrf_token' => $token,
                        'errors'     => $errors,
                        // Passes the already cleaned values ​​so the user does not have to re-enter them.
                        'form'       => [
                            'name'  => $name,
                            'email' => $email,
                        ],
                    ]
                );
            }

            // ------------- 4. Creation of the User ----------
            $user = User::create();
            $user->setName($name);
            $user->setEmail($email);
            $user->setPlainPassword($password);

            // Save the new user using the service class, which expects an argument compatible with the model interface.
            $id = $this->userService->create($user);

            return $this->redirect("/user/{$id}");
        }

        // Render the form for creating a new user.
        return $this->render(
            'User/create',
            [
                'view_title' => 'New user',
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
        // The middleware is already part of every request!
        // So, in any controller or view I can access it with:
        $csrf  = $request->getAttribute('csrf');
        $token = $csrf->getToken();

        // POST request indicates form submission.
        if ($request->getMethod() === 'POST') {

            $parameters = $request->getParsedBody();

            // ------------- 1. Normalization ----------
            $id       = $parameters['id'] ?? '';
            $name     = $parameters['name'] ?? '';
            $email    = $parameters['email'] ?? '';
            $password = $parameters['password'] ?? '';

            // ------------- 2. Sanitization -----------
            $id    = htmlspecialchars((string) $id, ENT_QUOTES, 'UTF-8');
            $name  = htmlspecialchars((string) $name, ENT_QUOTES, 'UTF-8');
            $email = htmlspecialchars((string) $email, ENT_QUOTES, 'UTF-8');
            $password = (string) $password;

            // ------------- 3. Validation ----------
            $errors = [];
            if ($name === null || $name === '') {
                $errors['name'] = 'Invalid name!';
            }
            if ($email === null || $email === '') {
                $errors['email'] = 'Invalid email!';
            }

            // If there are any errors, re-render the form.
            if ($errors) {
                return $this->render(
                    'User/update',
                    [
                        'view_title' => 'Edit user',
                        'datetime'   => $this->datetime->format('l'),
                        'csrf_token' => $token,
                        'errors'     => $errors,
                        // Passes the already cleaned values ​​so the user does not have to re-enter them.
                        'form'       => [
                            'name'  => $name,
                            'email' => $email,
                        ],
                    ]
                );
            }

            // ------------- 4. Update of the User ----------
            $user = User::create();
            $user->setId($id);
            $user->setName($name);
            $user->setEmail($email);
            $user->setPlainPassword($password);

            // Update the database using the service method.
            $this->userService->update($user);

            // Redirect to the user details page.
            return $this->redirect("/user/{$user->getId()}");
        }

        // Display the form with the current values.
        $id   = $args['id'] ?? null;
        $user = $this->userService->read($id);

        if ($user === null) {
            // 404
            throw new NotFoundException();
        }

        return $this->render(
            'User/update',
            [
                'view_title' => 'Edit user',
                'datetime'   => $this->datetime->format('l'),
                'csrf_token' => $token,
                'form'       => [
                    'id'       => $user->getId(),
                    'name'     => $user->getName(),
                    'email'    => $user->getEmail(),
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
        $csrf = $request->getAttribute('csrf');
        $token = $csrf->getToken();

        // POST indicates form submission for deleting.
        if ($request->getMethod() === 'POST') {
            $parameters = $request->getParsedBody();

            // Build an User instance with the updated values.
            $user = User::create();
            $user->setId(htmlspecialchars($parameters['id']));

            // Persist changes via the service.
            $deleted = $this->userService->delete($user->getId());

            // After deleted, display the updated users.
            if ($deleted) {
                // Gets the URI of the request just made.
                $uri = $request->getUri();
                // I create a new immutable object to point the browser to the index page.
                $usersUri = $uri->withPath('/users');
                // After deletion, show the paginate page.
                return $this->paginate($request->withUri($usersUri));
            } else {
                // User not found - 404
                throw new NotFoundException();
            }
        } else {
            // For GET request, fetch current data to pre-populate the form.
            $user = $this->userService->read($args['id']);

            if ($user !== null) {
                return $this->render(
                    'User/delete',
                    [
                        'view_title'  => 'Delete user',
                        'datetime'    => $this->datetime->format('l'),
                        'id'          => $user->getId(),
                        'name'        => $user->getName(),
                        'email'       => $user->getEmail(),
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
