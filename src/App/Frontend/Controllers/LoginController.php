<?php

declare(strict_types=1); // Enable strict type checking

namespace App\Frontend\Controllers;

use App\Backend\Models\User;
use App\Frontend\Controllers\Controller;
use App\Frontend\Controllers\Helpers\ValidateHelper;
use App\Frontend\Services\LoginService;
use App\Util\Interfaces\MailerInterface;
use App\Util\Mailers\MailBuilder;
use Egulias\EmailValidator\EmailValidator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class LoginController extends Controller
{
    use ValidateHelper;

    /**
     * Constructor - PHP 8 property promotion keeps the code short.
     *
     * @param LoginService      $loginService
     * @param EmailValidator    $emailValidator
     * @param MailerInterface   $mailer
     */
    public function __construct(
        private readonly LoginService $loginService,
        private readonly EmailValidator $emailValidator,
        private readonly MailerInterface $mailer
    ) {}

    /**
     * login
     *
     * Handles the login flow for both GET, show the form, and POST, process credentials.
     *
     * @param  ServerRequestInterface $request  PSR-7 request instance supplied by the framework.
     * @return ResponseInterface                The response that will be sent back to the client.
     */
    public function login(ServerRequestInterface $request): ResponseInterface
    {
        /**
         * 1.Grab the CSRF token that the middleware already attached.
         */
        $csrf      = $request->getAttribute('csrf');
        $csrfToken = $csrf->getToken();

        /**
         * 2. If it’s not a POST request we just display the login form.
         */
        if ($request->getMethod() !== 'POST') {
            return $this->renderLogin($csrfToken);
        }

        /**
         * 3. Extract and normalize the posted data.
         */
        $parameters = $request->getParsedBody(); // ['email'=>…, 'password'=>…]
        $email      = static::toTrimmedString($parameters['email']);
        $password   = static::toTrimmedString($parameters['password']);

        /**
         * 4. Sanitize user input, protects against XSS.
         */
        $email    = static::escapeHtmlForPreventXss($email);
        $password = static::escapeHtmlForPreventXss($password);

        /**
         * 5. RFC compliance validation for email.
         */
        if (($msg = static::validateEmail($email))) {
            return $this->renderLogin($csrfToken, $msg);
        }

        /**
         * 6. Email & password must be valid.
         */
        if ($this->credentialsAreInvalid($email, $password)) {
            // The same generic please fill in valid credentials message is shown.
            return $this->renderLogin($csrfToken, 'Please enter valid credentials!');
        }

        /**
         * 7. Attempt to load the user by e-mail.
         */
        $user = $this->loginService->findByEmail($email);
        if ($user === null) {
            // Unknown e-mail  the same generic error is shown.
            return $this->renderLogin($csrfToken, 'Invalid credentials!');
        }

        /**
         * 8. Verify the supplied password.
         */
        $isAuth = $user->checkPassword($password);
        if (! $isAuth) {
            // Wrong password, same error message.
            return $this->renderLogin($csrfToken, 'Invalid credentials!');
        }

        /**
         * 9. Successful authentication, show the dashboard.
         */
        return $this->render(
            'Dashboard/index',
            [
                'pageTitle'  => 'Welcome',
                'userName'   => $user->getName(),
                'csrf_token' => $csrfToken,
            ]
        );
    }

    /**
     * logout
     *
     * @param  mixed $request
     * @return ResponseInterface
     */
    public function logout(ServerRequestInterface $request): ResponseInterface
    {
        // Destroys the session of the user who was logged in.
        session_destroy();

        // Call the helper defined in the base Controller to render a template
        return $this->render(
            'Login/logout',
            [
                'pageTitle' => 'Logged out', // Page title used by the layout
            ]
        );
    }

    /**
     * regenerate
     *
     * @param  mixed $request
     * @return ResponseInterface
     */
    public function regenerate(ServerRequestInterface $request): ResponseInterface
    {
        // Destroys the session of the user who was logged in.
        session_destroy();

        // Generate a new random token.
        $token = bin2hex(random_bytes(32));

        // Save in session.
        $_SESSION['csrf_token'] = $token;

        // As a request attribute.
        $request = $request->withAttribute('csrf', $token);

        // Call the helper defined in the base Controller to render a template
        return $this->render(
            'Login/regenerate',
            [
                'pageTitle'  => 'Regenerate session', // Page title used by the layout
                'csrf_token' => $token,
            ]
        );
    }

    /**
     * The register endpoint is simply a thin wrapper that
     * delegates the whole flow to UserController::create().
     *
     * @param  ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function register(ServerRequestInterface $request): ResponseInterface
    {
        // The middleware is already part of every request!
        // So, in any controller or view I can access it with:
        $csrf  = $request->getAttribute('csrf');
        $token = $csrf->getToken();

        // POST request indicates form submission.
        if ($request->getMethod() === 'POST') {
            $parameters = $request->getParsedBody();

            /**
             * 1. Normalization.
             */
            $name     = static::toTrimmedString($parameters['name']);
            $email    = static::toTrimmedString($parameters['email']);
            $password = static::toTrimmedString($parameters['password']);

            /**
             * 2. Sanitization.
             */
            $name     = static::escapeHtmlForPreventXss($name);
            $email    = static::escapeHtmlForPreventXss($email);
            $password = static::escapeHtmlForPreventXss($password);

            /**
             * 3. Name and password validation.
             */
            $errors = [];
            if (! static::isNameSafe($name)) {
                $errors['name'] = 'Invalid name!';
            }
            if (! static::isPasswordSafe($password)) {
                $errors['password'] = 'Invalid password!';
            }

            /**
             * 4. RFC compliance validation for email.
             */
            if (($msg = static::validateEmail($email))) {
                $errors['email'] = $msg;
            }

            // If there are any errors, re-render the form.
            if ($errors) {
                return $this->render(
                    'Login/register',
                    [
                        'view_title' => 'Register',
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
            $id = $this->loginService->register($user);

            return $this->redirect("/login");
        }

        // Render the form for creating a new user.
        return $this->render(
            'Login/register',
            [
                'pageTitle'  => 'Register', // Page title used by the layout.
                'csrf_token' => $token,
            ]
        );

        // \App\Util\Handlers\VarDebugHandler::varDump('login method');
        // Call the helper defined in the base Controller to render a template
        return $this->render(
            'Login/register',
            [
                'pageTitle'  => 'Register', // Page title used by the layout.
                'datetime'   => $this->datetime->format('Y-m-d H:i:s'),
                'csrf_token' => $token,
            ]
        );
    }

    /**
     * The forgot endpoint is simply a thin wrapper that
     * delegates the whole flow to UserController::create().
     *
     * @param  ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function forgot(ServerRequestInterface $request): ResponseInterface
    {
        // The middleware is already part of every request!
        // So, in any controller or view I can access it with:
        $csrf  = $request->getAttribute('csrf');
        $token = $csrf->getToken();

        // POST request indicates form submission.
        if ($request->getMethod() === 'POST') {
            $parameters = $request->getParsedBody();

            /**
             * 1. Normalization.
             */
            $errors = [];
            $email  = static::toTrimmedString($parameters['email']);

            /**
             * 2. Sanitization.
             */
            $email = static::escapeHtmlForPreventXss($email);

            /**
             * 3. RFC compliance validation for email.
             */
            if (($msg = static::validateEmail($email))) {
                $errors['email'] = $msg;
            } elseif ($this->loginService->findByEmail($email) === null) { // Email not exists.
                $errors['email'] = 'Unknown email!';
            }

            // If there are any errors, re-render the form.
            if ($errors) {
                return $this->render(
                    'Login/forgot',
                    [
                        'view_title' => 'Forgot',
                        'csrf_token' => $token,
                        'errors'     => $errors,
                    ]
                );
            }

            // If there are no errors you must:
            // Set the token in the database.
            $passphrase = $this->loginService->generateTokenTwoFaHash($email);

            // Write the email to file.
            $subject = 'reset passphrase';
            $body    = "Your password reset passphrase: {$passphrase}";

            MailBuilder::create($this->mailer)
                ->to($email)
                ->subject($subject)
                ->body($body)
                ->send();

            // Reset
            return $this->render(
                'Login/reset',
                [
                    'pageTitle'  => 'Reset', // Page title used by the layout
                    'csrf_token' => $token,
                    'email'      => $email,
                    'message'    => 'Passphrase sent to your e-mail',
                    // 'passphrase' => $passphrase, // TODO: For debugging only!
                ]
            );
        }

        // Call the helper defined in the base Controller to render a template
        return $this->render(
            'Login/forgot',
            [
                'pageTitle'  => 'Forgot', // Page title used by the layout
                'csrf_token' => $token,
            ]
        );
    }

    /**
     * The reset endpoint is simply a thin wrapper that
     * delegates the whole flow to UserController::create().
     *
     * @param  ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function reset(ServerRequestInterface $request): ResponseInterface
    {
        // The middleware is already part of every request!
        // So, in any controller or view I can access it with:
        $csrf  = $request->getAttribute('csrf');
        $token = $csrf->getToken();

        // POST request indicates form submission.
        if ($request->getMethod() === 'POST') {
            $parameters = $request->getParsedBody();

            /**
             * 1. Normalization.
             */
            $errors     = [];
            $email      = static::toTrimmedString($parameters['email']);
            $password   = static::toTrimmedString($parameters['password']);
            $passphrase = static::toTrimmedString($parameters['passphrase']);

            /**
             * 2. Sanitization.
             */
            $email      = static::escapeHtmlForPreventXss($email);
            $password   = static::escapeHtmlForPreventXss($password);
            $passphrase = static::escapeHtmlForPreventXss($passphrase);

            /**
             * 3. RFC compliance validation for email.
             */
            if (($msg = static::validateEmail($email))) {
                $errors['email'] = $msg;
            } elseif ($this->loginService->findByEmail($email) === null) { // Email not exists.
                $errors['email'] = 'Unknown email!';
            }

            /**
             * 4. Check password.
             */
            if (! static::isPasswordSafe($password)) {
                $errors['password'] = 'Invalid password!';
            }

            /**
             * 5. Check passphrase.
             */
            if (! static::isPassphraseValid($passphrase)) {
                $errors['passphrase'] = 'Invalid passphrase!';
            }

            // If there are any errors, re-render the form.
            if ($errors) {
                return $this->render(
                    'Login/reset',
                    [
                        'view_title' => 'Reset',
                        'csrf_token' => $token,
                        'email'      => $email,
                        'errors'     => $errors,
                    ]
                );
            }

            // Call the LoginService::resetPassword() method, which is responsible for recording the new password.
            $isUpdated = $this->loginService->resetPassword($email, $password, $passphrase);

            if ($isUpdated) {
                return $this->redirect("/login");
            } else {
                $errors['passphrase'] = 'Invalid passphrase!';
                return $this->render(
                    'Login/reset',
                    [
                        'view_title' => 'Reset',
                        'csrf_token' => $token,
                        'email'      => $email,
                        'errors'     => $errors,
                    ]
                );
            }
        }

        // Call the helper defined in the base Controller to render a template
        return $this->render(
            'Login/reset',
            [
                'pageTitle'  => 'Forgot', // Page title used by the layout
                'csrf_token' => $token,
            ]
        );
    }

    /**
     * isAuthenticated
     *
     * @return bool
     */
    public function isAuthenticated(): bool
    {
        return isset($_SESSION['userId']);
    }

    /**
     * renderLogin
     *
     * Renders the login page, optionally passing an error message.
     *
     * @param  string $csrfToken    The CSRF token to embed in the form.
     * @param  string|null $error   Optional one-time error message.
     * @return ResponseInterface    The response that will be sent back to the client.
     */
    private function renderLogin(string $csrfToken, ?string $error = null): ResponseInterface
    {
        return $this->render(
            'Login/login',
            [
                'pageTitle'  => 'Log in',
                'csrf_token' => $csrfToken,
                'error'      => $error,
            ]
        );
    }

    /**
     * credentialsAreInvalid
     *
     * @param  string $email
     * @param  string $password
     * @return bool
     */
    private function credentialsAreInvalid(string $email, string $password): bool
    {
        // Empty e-mail OR malformed address
        if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return true;
        }

        // Password shorter than 8 characters
        if (strlen($password) < 8) {
            return true;
        }

        return false;
    }
}
