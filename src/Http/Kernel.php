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
use Dybasedev\Keeper\Http\Standard\ContextContainer;
use FilesystemIterator;
use Illuminate\Config\Repository;
use Illuminate\Events\Dispatcher as IlluminateDispatcher;
use Illuminate\Contracts\Container\Container;
use Illuminate\Routing\Router;
use SplFileInfo;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Http\Server as SwooleHttpServer;

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
     * Kernel constructor.
     *
     * @param            $basePath
     * @param Container  $container
     */
    public function __construct($basePath, Container $container = null)
    {
        $this->container = $container ?: new ContextContainer();
        $this->registerPath($basePath);
    }

    protected function registerPath($basePath)
    {
        $this->container['path.base']   = $basePath;
        $this->container['path.config'] = $basePath . '/config';
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
     * @return Repository
     */
    protected function loadConfiguration()
    {
        $this->loadEnvironment();

        $config         = new Repository();
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
        $this->container->instance('config', $this->loadConfiguration());
        $this->container->instance('event', new IlluminateDispatcher($this->container));
        $this->container->instance('router', new Router($this->container['event'], $this->container));
    }

    public function init(SwooleHttpServer $server, $workerId)
    {
        $this->loadBaseModule();

        $moduleProviderInstances = [];
        $configuration   = $this->container['config'];
        foreach ($this->moduleProviders as $module) {
            /** @var ModuleProvider $moduleProviderInstance */
            $moduleProviderInstance = new $module($this->container, $configuration);
            $moduleProviderInstance->register();
            $moduleProviderInstances[] = $moduleProviderInstance;
        }

        foreach ($moduleProviderInstances as $moduleProviderInstance) {
            $moduleProviderInstance->boot();
        }

        unset($moduleProviderInstances);
        unset($moduleProviderInstance);
        unset($configuration);
    }

    public function process(Request $request, Response $response)
    {

    }

    public function destroy(SwooleHttpServer $server, $workerId)
    {

    }


}