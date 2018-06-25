<?php
/**
 * KeeperHttpService.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Keeper\Http\HttpServices;

use Closure;
use Dybasedev\Keeper\Http\Interfaces\ExceptionHandler;
use Dybasedev\Keeper\Http\Interfaces\WorkerHookDelegation;
use Dybasedev\Keeper\Http\KeeperBaseController;
use Dybasedev\Keeper\Routing\Pipeline;
use Dybasedev\KeeperContracts\Module\DestructibleModuleProvider;
use Dybasedev\KeeperContracts\Module\ModuleProvider;
use RuntimeException;
use Swoole\Http\Request as SwooleHttpRequest;
use Swoole\Http\Response as SwooleHttpResponse;
use Swoole\Http\Server as SwooleHttpServer;
use InvalidArgumentException;
use Dybasedev\Keeper\Http\Interfaces\HttpService;
use Dybasedev\Keeper\Routing\Router as KeeperRouter;
use Dybasedev\Keeper\Routing\Interfaces\Router;
use Psr\Container\ContainerInterface as Container;
use Dybasedev\Keeper\Http\Response;
use FastRoute\Dispatcher;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Dybasedev\Keeper\Http\ConfigurationLoader;
use Dybasedev\Keeper\Module\LifecycleContainer;
use Dybasedev\Keeper\Http\Request;
use Illuminate\Contracts\Config\Repository as Configuration;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

/**
 * Class KeeperHttpService
 *
 * @package Dybasedev\Keeper\Http\HttpServices
 */
class KeeperHttpService implements HttpService
{
    /**
     * @var Container
     */
    protected $container;

    /**
     * @var string
     */
    protected $basePath;

    /**
     * @var array
     */
    protected $paths = [];

    /**
     * @var Router
     */
    protected $router;

    /**
     * @var array|DestructibleModuleProvider[]
     */
    protected $destructibleModules = [];

    /**
     * @var array
     */
    protected $processBeginHooks = [];

    /**
     * @var array
     */
    protected $processEndHooks = [];

    /**
     * @var ExceptionHandler
     */
    protected $exceptionHandler;

    /**
     * BaseKernel constructor.
     *
     * @param array $paths
     */
    public function __construct(array $paths)
    {
        $this->paths = array_map('realpath', $paths);;

        if (isset($this->paths['base'])) {
            $this->basePath = $this->paths['base'];
        }

        if (!$this->basePath) {
            throw new InvalidArgumentException('Invalid base path.');
        }
    }

    public function basePath($path = null): string
    {
        if ($path) {
            return $this->basePath . DIRECTORY_SEPARATOR . trim($path, '/');
        }

        return $this->basePath;
    }

    public function path($name = null)
    {
        if (is_null($name)) {
            return $this->paths;
        }

        if (isset($this->paths[$name])) {
            return $this->paths[$name];
        }

        return $this->basePath($name);
    }

    /**
     * @param ConfigurationLoader $config
     *
     * @return Router
     */
    public function getRouter(ConfigurationLoader $config)
    {
        return (new KeeperRouter($config->get('router.registers')))->setContainer($this->container);
    }

    public function init(SwooleHttpServer $server, $workerId)
    {
        // Create lifecycle container
        $this->container = new LifecycleContainer();

        // Bind context data to container
        $this->container['server'] = $server;
        $this->container['worker'] = $workerId;

        // Create hook delegation
        $this->container->instance(WorkerHookDelegation::class, $this->createWorkerHookDelegation());

        // Bind exception handler
        if ($this->exceptionHandler) {
            $this->container->instance(ExceptionHandler::class, $this->exceptionHandler);
        }

        // Load configuration
        $this->container->instance(Configuration::class, $config = new ConfigurationLoader($this->path('config')));

        // Load router component
        $this->router = $this->getRouter($config);
        $this->container->instance(Router::class, $this->router);

        // Load Modules
        $this->loadModules($config->get('app.modules', []));

        // Mount route
        $this->router->mount();
    }

    public function onProcessBegin(Closure $callback)
    {
        $this->processBeginHooks[] = $callback;
    }

    public function onProcessEnd(Closure $callback)
    {
        $this->processEndHooks[] = $callback;
    }

    /**
     * @return WorkerHookDelegation
     */
    protected function createWorkerHookDelegation()
    {
        return new class($this) implements WorkerHookDelegation
        {

            private $delegation;

            public function __construct(KeeperHttpService $delegation)
            {
                $this->delegation = $delegation;
            }

            public function processBegin(Closure $callback)
            {
                $this->delegation->onProcessBegin($callback);
            }

            public function processEnd(Closure $callback)
            {
                $this->delegation->onProcessEnd($callback);
            }

        };
    }

    protected function loadModules($modules)
    {
        /** @var ModuleProvider[] $moduleProviders */
        $moduleProviders = [];

        foreach ($modules as $module) {
            $moduleProvider = new $module;
            if ($moduleProvider instanceof ModuleProvider) {
                if ($moduleProvider instanceof DestructibleModuleProvider) {
                    $this->destructibleModules[] = $moduleProvider;
                }

                $moduleProvider->register($this->container);
                $moduleProviders[] = $moduleProvider;
            }
        }

        foreach ($moduleProviders as $provider) {
            $provider->mount($this->container);
        }
    }

    /**
     * Handle Http request and return processed result
     *
     * @param Request $request
     *
     * @return mixed
     */
    protected function handle(Request $request)
    {
        try {
            list($routeInformation, $parameters) = $this->findRoute($request);

            return (new Pipeline($this->container))
                ->through($routeInformation['middlewares'])
                ->send($request)
                ->then(function (Request $request) use ($routeInformation, $parameters) {
                    if ($routeInformation['action'] instanceof Closure) {
                        return call_user_func_array($routeInformation['action'], $parameters);
                    }

                    list($controller, $action) = $routeInformation['action'];
                    $controllerInstance = new $controller;

                    if ($controllerInstance instanceof KeeperBaseController) {
                        $controllerInstance->setContainer($this->container);
                        $controllerInstance->setRequest($request);
                    }

                    return call_user_func_array([$controllerInstance, $action], $parameters);
                });

        } catch (Throwable $exception) {
            if ($this->exceptionHandler) {
                return $this->exceptionHandler->handle($exception, $request);
            }

            return new Response((string)$exception, 500);
        }
    }

    public function findRoute(Request $request)
    {
        $route = $this->router->dispatch($request->getMethod(), '/' . trim($request->getPathInfo(), '/'));
        switch ($route[0]) {
            case Dispatcher::NOT_FOUND:
                throw new NotFoundHttpException();
            case Dispatcher::METHOD_NOT_ALLOWED:
                throw new MethodNotAllowedHttpException($route[1]);
            case Dispatcher::FOUND:
                return [$route[1], $route[2] ?? []];
        }

        throw new RuntimeException();
    }

    public function process(SwooleHttpRequest $request, SwooleHttpResponse $response)
    {
        try {
            $keeperRequest = Request::createFromSwooleRequest($request);

            // trigger: process begin
            foreach ($this->processBeginHooks as $hook) {
                ($hook)($keeperRequest);
            }

            $keeperResponse = $this->handle($keeperRequest);

            $this->prepareResponse($keeperResponse)
                 ->setSwooleResponse($response)
                 ->send();

            // trigger: process end
            foreach ($this->processEndHooks as $hook) {
                ($hook)($keeperRequest, $keeperResponse);
            }

            unset($keeperRequest);
            unset($keeperResponse);
            unset($hook);

            gc_collect_cycles();
        } catch (Throwable $exception) {
            if ($this->exceptionHandler) {
                $this->exceptionHandler->handle($exception, $keeperRequest);
            }
        }
    }

    public function setExceptionHandler(ExceptionHandler $handler)
    {
        $this->exceptionHandler = $handler;

        return $this;
    }

    protected function prepareResponse($response)
    {
        if (!$response instanceof Response) {
            if ($response instanceof SymfonyResponse) {
                $response = new Response($response->getContent(), $response->getStatusCode(),
                    $response->headers->all());
            } elseif (is_array($response)) {
                return $this->prepareResponse(new JsonResponse($response));
            } else {
                $response = new Response($response);
            }
        }

        return $response;
    }

    public function destroy(SwooleHttpServer $server, $workerId)
    {
        foreach ($this->destructibleModules as $module) {
            $module->destroy($this->container);
        }
    }
}