<?php
/**
 * BaseKernel.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Keeper\Http\ProcessKernels;

use Closure;
use Dybasedev\Keeper\Http\ConfigurationLoader;
use Dybasedev\Keeper\Http\Interfaces\HttpService;
use Dybasedev\Keeper\Http\LifecycleContainer;
use InvalidArgumentException;
use Swoole\Http\Request as SwooleHttpRequest;
use Swoole\Http\Response as SwooleHttpResponse;
use Swoole\Http\Server as SwooleHttpServer;
use Dybasedev\Keeper\Server\Interfaces\HttpServerProcessKernel;
use Illuminate\Contracts\Config\Repository as Configuration;

class BaseKernel implements HttpServerProcessKernel, HttpService
{
    protected $container;

    protected $basePath;

    protected $paths = [];

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
        $this->container->instance(Configuration::class, new ConfigurationLoader($this->path('config')));

        // Load http service routes

    }

    public function process(SwooleHttpRequest $request, SwooleHttpResponse $response)
    {

    }

    public function destroy(SwooleHttpServer $server, $workerId)
    {

    }


}