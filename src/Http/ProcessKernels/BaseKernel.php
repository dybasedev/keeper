<?php
/**
 * BaseKernel.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Keeper\Http\ProcessKernels;

use Closure;
use Dybasedev\Keeper\Http\Response;
use FastRoute\Dispatcher;
use Illuminate\Contracts\Container\Container;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Dybasedev\Keeper\Http\ConfigurationLoader;
use Dybasedev\Keeper\Http\Interfaces\HttpService;
use Dybasedev\Keeper\Http\LifecycleContainer;
use Dybasedev\Keeper\Http\Request;
use Dybasedev\Keeper\Routing\Router;
use InvalidArgumentException;
use Swoole\Http\Request as SwooleHttpRequest;
use Swoole\Http\Response as SwooleHttpResponse;
use Swoole\Http\Server as SwooleHttpServer;
use Dybasedev\Keeper\Server\Interfaces\HttpServerProcessKernel;
use Illuminate\Contracts\Config\Repository as Configuration;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class BaseKernel implements HttpServerProcessKernel, HttpService
{
    /**
     * @var Container
     */
    protected $container;

    protected $basePath;

    protected $paths = [];

    /**
     * @var Router
     */
    protected $router;

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


    public function onRequest(): Closure
    {
        return function (SwooleHttpRequest $request, SwooleHttpResponse $response) {
            $this->process($request, $response);
        };
    }

    public function onStart(): Closure
    {
        return function () {

        };
    }

    public function onShutdown(): Closure
    {
        return function () {

        };
    }

    public function onWorkerStart(): Closure
    {
        return function (SwooleHttpServer $server, $workerId) {
            $this->init($server, $workerId);
        };
    }

    public function onWorkerStop(): Closure
    {
        return function (SwooleHttpServer $server, $workerId) {
            $this->destroy($server, $workerId);
        };
    }

    public function onWorkerError(): Closure
    {
        return function () {

        };
    }

    public function onManagerStart(): Closure
    {
        return function () {

        };
    }

    public function onManagerStop(): Closure
    {
        return function () {

        };
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

    public function init(SwooleHttpServer $server, $workerId)
    {
        // Create lifecycle container
        $this->container = new LifecycleContainer();
        $this->container['server'] = $server;
        $this->container['worker'] = $workerId;

        // Load configuration
        $this->container->instance(Configuration::class, $config = new ConfigurationLoader($this->path('config')));

        // Load http service routes
        $this->router = (new Router($config->get('router.registers', [])))->mount();
    }

    protected function handle(Request $request)
    {
        $route = $this->router->dispatch($request->getMethod(), '/' . trim($request->getPathInfo(), '/'));
        switch ($route[0]) {
            case Dispatcher::NOT_FOUND:
                throw new NotFoundHttpException();
            case Dispatcher::METHOD_NOT_ALLOWED:
                throw new MethodNotAllowedHttpException($route[1]);
            case Dispatcher::FOUND:
                if (is_array($route[1])) {
                    list($controller, $action) = $route[1];
                    $controllerInstance = new $controller;
                    $callable = [$controllerInstance, $action];
                } else if ($route[1] instanceof Closure) {
                    $callable = $route[1];
                } else {
                    $callable = $route[1];
                }

                $parameters = $route[2] ?? [];

                return $this->container->call($callable, $parameters);
        }
    }

    public function process(SwooleHttpRequest $request, SwooleHttpResponse $response)
    {
        try {
            $keeperRequest  = Request::createFromSwooleRequest($request);
            $keeperResponse = $this->handle($keeperRequest);

            $this->prepareResponse($keeperResponse)
                 ->setSwooleResponse($response)
                 ->send();

            unset($illuminateRequest);
            unset($keeperResponse);

            gc_collect_cycles();
        } catch (Throwable $exception) {
            //
        }
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

    }


}