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
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\AbstractHandler;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use SplFileInfo;
use Swoole\Http\Request as SwooleRequest;
use Swoole\Http\Response as SwooleResponse;
use Swoole\Http\Server as SwooleHttpServer;
use Illuminate\Http\Request as IlluminateRequest;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Throwable;

abstract class Kernel implements ProcessKernel
{
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
     * @var string
     */
    protected $processName;

    /**
     * @var ExceptionHandler
     */
    protected $exceptionHandler;

    /**
     * @return string
     */
    public function getProcessName(): string
    {
        return $this->processName ?: 'keeper';
    }

    /**
     * Kernel constructor.
     *
     * @param            $basePath
     * @param Container  $container
     */
    public function __construct($basePath, Container $container = null)
    {
        $this->container = $container ?: new ContextContainer($basePath);

        ContextContainer::setInstance($this->container);
    }

    /**
     * @return ExceptionHandler
     */
    public function getExceptionHandler(): ExceptionHandler
    {
        return $this->exceptionHandler ?: (new ExceptionHandler($this->container,
            $this->config))->setLogger($this->container['log']);
    }

    /**
     * @param ExceptionHandler $exceptionHandler
     *
     * @return Kernel
     */
    public function setExceptionHandler(ExceptionHandler $exceptionHandler): Kernel
    {
        $this->exceptionHandler = $exceptionHandler;

        return $this;
    }

    /**
     * 载入环境配置文件
     *
     * @return void
     */
    protected function loadEnvironment()
    {
        if (is_file($this->container->applicationPath . DIRECTORY_SEPARATOR . '.env')) {
            (new Dotenv($basePath = $this->container->applicationPath))->load();
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
        $configurations = new FilesystemIterator($this->container->applicationPath . DIRECTORY_SEPARATOR . 'config',
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
        $this->registerLogger();
    }

    protected function registerLogger()
    {
        $logger = new Logger('keeper#' . $this->container['worker.id']);
        $logger->setHandlers($this->getLoggerHandlers());

        $this->container->instance('log', $logger);
    }

    protected function getLoggerHandlers()
    {
        $logConfiguration = $this->container['config']['global.log'];
        $formatter        = tap(new LineFormatter(null, null, true, true), function (LineFormatter $formatter) {
            $formatter->includeStacktraces();
        });

        return [
            (new StreamHandler($logConfiguration['path'], $logConfiguration['level']))->setFormatter($formatter),
        ];
    }

    /**
     * 初始化
     *
     * @param SwooleHttpServer $server
     * @param                  $workerId
     */
    public function init(SwooleHttpServer $server, $workerId)
    {
        $this->container['worker.id'] = $workerId;
        $this->loadBaseModule();

        $this->config = $this->container['config'];
        $this->loadModules();

        $this->loadMiddlewares();
    }

    /**
     * 处理过程
     *
     * @param SwooleRequest  $request
     * @param SwooleResponse $response
     */
    public function process(SwooleRequest $request, SwooleResponse $response)
    {
        try {
            $illuminateRequest  = Request::createFromSwooleRequest($request);
            $illuminateResponse = $this->handle($illuminateRequest);

            $this->prepareResponse($illuminateResponse)
                 ->setSwooleResponse($response)
                 ->send();

            unset($this->container[IlluminateRequest::class]);
            unset($illuminateRequest);
            unset($illuminateResponse);

            gc_collect_cycles();
        } catch (Throwable $exception) {
            $this->exceptionHandle($exception, $response);
        }
    }

    /**
     * @param IlluminateRequest $illuminateRequest
     *
     * @return \Illuminate\Http\Response
     */
    protected function handle(IlluminateRequest $illuminateRequest)
    {
        $this->container->instance('request', $illuminateRequest);

        /** @var Router $router */
        $router = $this->container->make('router');

        $pipeline           = new Pipeline($this->container);
        $illuminateResponse = $pipeline->send($illuminateRequest)
                                       ->through($this->middlewares)
                                       ->then(function (IlluminateRequest $request) use ($router) {
                                           return $router->dispatch($request);
                                       });

        unset($pipeline);
        unset($router);

        return $illuminateResponse;
    }

    protected function exceptionHandle(Throwable $exception, SwooleResponse $response)
    {
        $this->getExceptionHandler()->handle($exception, $response);
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
            'config'  => [Repository::class, IlluminateRepository::class],
            'router'  => [Router::class],
            'event'   => [Dispatcher::class, IlluminateDispatcher::class, 'events'],
            'log'     => ['logger', Logger::class],
            'request' => [IlluminateRequest::class, Request::class, SymfonyRequest::class],
        ];
    }

    /**
     * 加载模块
     */
    protected function loadModules()
    {
        $moduleProviderInstances = [];
        foreach ($this->moduleProviders as $module) {
            /** @var ModuleProvider $moduleProviderInstance */
            $moduleProviderInstance = new $module($this->container, $this->config);
            $moduleProviderInstance->register();
            $this->alias($moduleProviderInstance->alias());

            if ($moduleProviderInstance instanceof DestructibleModuleProvider) {
                $this->moduleInstances[] = $moduleProviderInstance;
            }

            $moduleProviderInstances[] = $moduleProviderInstance;
        }

        $this->alias($this->getBaseModuleAlias());

        foreach ($moduleProviderInstances as $moduleProviderInstance) {
            $moduleProviderInstance->boot();
        }

        unset($moduleProviderInstances);
        unset($moduleProviderInstance);
    }

    /**
     * 加载中间件
     */
    protected function loadMiddlewares()
    {
        $router = $this->container['router'];
        foreach ($this->middlewareGroups as $key => $middleware) {
            $router->middlewareGroup($key, $middleware);
        }

        foreach ($this->routeMiddlewares as $key => $middleware) {
            $router->aliasMiddleware($key, $middleware);
        }

        unset($router);
    }
}