<?php

declare (strict_types = 1); // Enforce strict type checking

namespace App;

use App\Errors\InternalServerError;
use App\Frontend\Routes\HomeRoutes;
use App\Frontend\Routes\ItemRoutes;
use App\Frontend\Routes\UserRoutes;
use App\Frontend\Templates\Interfaces\TemplateInterface;
use App\Frontend\Templates\RenderTemplate;
use DI\Container;
use DI\ContainerBuilder;
use function DI\create;
use GuzzleHttp\Psr7\HttpFactory;
use GuzzleHttp\Psr7\ServerRequest;
use HttpSoft\Emitter\SapiEmitter;
use League\Route\Http\Exception\NotFoundException;
use League\Route\Router;
use League\Route\Strategy\ApplicationStrategy;
use Psr\Http\Message\ResponseFactoryInterface;

/**
 * Bootstrap
 */
class Bootstrap
{
    /** @var Container */
    private Container $container;

    /** @var Router */
    private Router $router;

    /** @var SapiEmitter */
    private SapiEmitter $emitter;

    public function __construct(
        Container | null $container = null,
        private string $environment = 'pro',
        private string $appRootDir = ''
    ) {
        // --------- DI Container -------------
        $builder = new ContainerBuilder();
        // If the caller provides an existing container, use it.
        if ($container instanceof Container) {
            $this->container = $container;
        } else {
            // Otherwise, create a default container.
            $builder->addDefinitions([
                ResponseFactoryInterface::class => create(HttpFactory::class),
                TemplateInterface::class        => create(RenderTemplate::class),
            ]);
            $builder->useAttributes(true);
            $this->container = $builder->build();
        }

        // --------- Strategy & Router ----
        $strategy = new ApplicationStrategy();
        $strategy->setContainer($this->container);

        $this->router = new Router();
        $this->router->setStrategy($strategy);

        // The routes are stored in these classes, which contain all authorized routes!
        // Home
        $homeRoutes = new HomeRoutes;
        $homeRoutes->registerRoutes($this->router);
        // Item
        $itemRoutes = new ItemRoutes;
        $itemRoutes->registerRoutes($this->router);
        // User
        $userRoutes = new UserRoutes;
        $userRoutes->registerRoutes($this->router);

        // ---------  Emitter ---------------
        $this->emitter = new SapiEmitter();
    }

    /**
     * Start the application: Receive the request, dispatch it, and send the response.
     */
    public function run(): void
    {
        // If I choose to pass the request as an argument, then I will have to alter the function signature.
        $request = ServerRequest::fromGlobals();

        try {
            $response = $this->router->dispatch($request);
        } catch (NotFoundException $nfe) {
            http_response_code(404);

            if ($this->environment === 'dev') {
                throw $nfe;
            } else {
                if ($this->appRootDir != '') {
                    require $this->appRootDir . '/src/App/Frontend/Views/404.html';
                }
                exit;
            }

        } catch (InternalServerError $ise) {
            http_response_code(500);

            if ($this->environment === 'dev') {
                throw $ise;
            } else {
                if ($this->appRootDir != '') {
                    require $this->appRootDir . '/src/App/Frontend/Views/500.html';
                }
                exit;
            }

        } catch (\Exception $e) {
            http_response_code(500);

            if ($this->environment === 'dev') {
                throw $e;
            } else {
                if ($this->appRootDir != '') {
                    require $this->appRootDir . '/src/App/Frontend/Views/500.html';
                }
                exit;
            }

        }

        $this->emitter->emit($response);
    }
}
