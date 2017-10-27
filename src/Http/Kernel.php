<?php
/**
 * Kernel.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Keeper\Http;

use Dotenv\Dotenv;
use Dybasedev\Keeper\Http\Interfaces\ProcessKernel;
use FilesystemIterator;
use Illuminate\Config\Repository as IlluminateRepository;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Events\Dispatcher as IlluminateDispatcher;
use Illuminate\Contracts\Container\Container;
use Illuminate\Http\JsonResponse;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Routing\Router;
use InvalidArgumentException;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\AbstractHandler;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Logger;
use SplFileInfo;
use Swoole\Http\Request as SwooleRequest;
use Swoole\Http\Response as SwooleResponse;
use Swoole\Http\Server as SwooleHttpServer;
use Illuminate\Http\Request as IlluminateRequest;
use Symfony\Component\Debug\Exception\FlattenException;
use Symfony\Component\Debug\ExceptionHandler;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

abstract class Kernel implements ProcessKernel
{
    const DEFAULT_PATH
        = [
            'config'  => 'config',
            'storage' => 'storage',
        ];

    /**
     * @var ContextContainer
     */
    protected $container;

    /**
     * @var array
     */
    protected $moduleProviders = [];

    /**
     * @var DestructibleModuleProvider[]
     */
    protected $moduleInstances = [];

    /**
     * @var array
     */
    protected $middlewareGroups = [];

    /**
     * @var array
     */
    protected $middlewares = [];

    /**
     * @var array
     */
    protected $routeMiddlewares = [];

    /**
     * @var Repository
     */
    protected $config;

    /**
     * Kernel constructor.
     *
     * @param            $basePath
     * @param Container  $container
     */
    public function __construct($basePath, Container $container = null)
    {
        $this->container = $container ?: new ContextContainer();
        $this->registerPath($basePath);

        ContextContainer::setInstance($this->container);
    }

    protected function registerPath($basePath)
    {
        $this->container['path.base'] = $basePath;
        foreach (self::DEFAULT_PATH as $name => $path) {
            $this->container['path.' . $name] = $basePath . DIRECTORY_SEPARATOR . $path;
        }
    }

    /**
     * 替换默认路径
     *
     * @param string $pathName
     * @param string $path
     *
     * @return $this
     */
    public function replacePath(string $pathName, string $path)
    {
        if (!in_array($pathName, array_keys(self::DEFAULT_PATH))) {
            throw new InvalidArgumentException();
        }

        $this->container['path.' . $pathName] = $path;

        return $this;
    }

    /**
     * 载入环境配置文件
     *
     * @return void
     */
    protected function loadEnvironment()
    {
        if (is_file($this->container['path.base'] . '/.env')) {
            (new Dotenv($basePath = $this->container['path.base']))->load();
        }
    }

    /**
     * 载入配置文件
     *
     * @return IlluminateRepository
     */
    public function loadConfiguration()
    {
        $this->loadEnvironment();

        $config         = new IlluminateRepository();
        $configurations = new FilesystemIterator($this->container['path.base'] . '/config',
            FilesystemIterator::SKIP_DOTS);

        /** @var SplFileInfo $configuration */
        foreach ($configurations as $configuration) {
            if ($configuration->isFile() && $configuration->isReadable() && $configuration->getExtension() == 'php') {
                $pathinfo = pathinfo($configuration->getPathname());
                $config->set($pathinfo['filename'], require $configuration->getRealPath());
            }
        }

        return $config;
    }

    protected function loadBaseModule()
    {
        $this->container->instance('config', $config = $this->loadConfiguration());
        $this->container->instance('event', new IlluminateDispatcher($this->container));
        $this->container->instance('router', new Router($this->container['event'], $this->container));
        $this->container->instance(
            'log.handler',
            (new ErrorLogHandler(ErrorLogHandler::OPERATING_SYSTEM, $config['global.log.level']))->setFormatter(
                tap(new LineFormatter(null, null, true, true), function (LineFormatter $formatter) {
                    $formatter->includeStacktraces();
                }))
        );
        $this->container->instance('log',
            (new Logger('keeper#' . $this->container['worker.id']))->pushHandler($this->container['log.handler']));
    }

    public function init(SwooleHttpServer $server, $workerId)
    {
        $this->container['worker.id'] = $workerId;
        $this->loadBaseModule();

        $moduleProviderInstances = [];
        $this->config            = $configuration = $this->container['config'];
        foreach ($this->moduleProviders as $module) {
            /** @var ModuleProvider $moduleProviderInstance */
            $moduleProviderInstance = new $module($this->container, $configuration);
            $moduleProviderInstance->register();
            $this->alias($moduleProviderInstance->alias());

            if ($moduleProviderInstance instanceof DestructibleModuleProvider) {
                $this->moduleInstances[] = $moduleProviderInstance;
            }

            $moduleProviderInstances[] = $moduleProviderInstance;
        }

        foreach ($moduleProviderInstances as $moduleProviderInstance) {
            $moduleProviderInstance->boot();
        }

        unset($moduleProviderInstances);
        unset($moduleProviderInstance);
        unset($configuration);

        $router = $this->container['router'];
        foreach ($this->middlewareGroups as $key => $middleware) {
            $router->middlewareGroup($key, $middleware);
        }

        foreach ($this->routeMiddlewares as $key => $middleware) {
            $router->aliasMiddleware($key, $middleware);
        }

        unset($router);

        $this->alias($this->getBaseModuleAlias());
    }

    public function process(SwooleRequest $request, SwooleResponse $response)
    {
        try {
            $illuminateRequest = Request::createFromSwooleRequest($request);

            $this->container->instance(IlluminateRequest::class, $illuminateRequest);
            $this->container->alias(IlluminateRequest::class, 'request');
            $this->container->alias(IlluminateRequest::class, Request::class);
            $this->container->alias(IlluminateRequest::class, SymfonyRequest::class);

            /** @var Router $router */
            $router = $this->container->make('router');

            $pipeline           = new Pipeline($this->container);
            $illuminateResponse = $pipeline->send($illuminateRequest)
                                           ->through($this->middlewares)
                                           ->then(function (IlluminateRequest $request) use ($router) {
                                               return $router->dispatch($request);
                                           });

            $this->prepareResponse($illuminateResponse)
                 ->setSwooleResponse($response)
                 ->send();

            unset($pipeline);
            unset($router);
            unset($this->container[IlluminateRequest::class]);
            unset($illuminateRequest);
            unset($illuminateResponse);

            gc_collect_cycles();
        } catch (Throwable $exception) {
            $statusCode = 500;
            $headers    = [];

            if ($exception instanceof HttpException) {
                $statusCode = $exception->getStatusCode();
                $headers    = $exception->getHeaders();
            }

            $html = (new ExceptionHandler($this->config->get('global.debug',
                false)))->getHtml(FlattenException::create($exception));
            $this->prepareResponse(new SymfonyResponse($html, $statusCode, $headers))
                 ->setSwooleResponse($response)
                 ->send();

            $this->container['log']->error((string)$exception);
        }
    }

    /**
     * 响应预处理
     *
     * @param string|\Symfony\Component\HttpFoundation\Response $response
     *
     * @return Response 输出一个 Dybasedev\Keeper\Http\Response 对象
     */
    private function prepareResponse($response)
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
        foreach ($this->moduleInstances as $moduleInstance) {
            $moduleInstance->destroy();
        }

        unset($this->moduleInstances);
        unset($moduleInstance);

        $this->container->flush();
        ContextContainer::setInstance(null);
    }

    /**
     * 系统模块别名
     */
    protected function alias(array $map)
    {
        foreach ($map as $origin => $aliases) {
            foreach ($aliases as $alias) {
                $this->container->alias($origin, $alias);
            }
        }
    }

    protected function getBaseModuleAlias()
    {
        return [
            'config'      => [Repository::class, IlluminateRepository::class],
            'router'      => [Router::class],
            'event'       => [Dispatcher::class, IlluminateDispatcher::class, 'events'],
            'log'         => ['logger', Logger::class],
            'log.handler' => ['logger.handler', AbstractHandler::class],
        ];
    }
}