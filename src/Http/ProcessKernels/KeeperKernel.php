<?php
/**
 * BaseKernel.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Keeper\Http\ProcessKernels;

use Closure;
use Dybasedev\Keeper\Http\Interfaces\HttpService;
use Swoole\Http\Request as SwooleHttpRequest;
use Swoole\Http\Response as SwooleHttpResponse;
use Swoole\Http\Server as SwooleHttpServer;
use Dybasedev\Keeper\Server\Interfaces\HttpServerProcessKernel;

class KeeperKernel implements HttpServerProcessKernel
{
    /**
     * @var HttpService
     */
    protected $httpService;

    /**
     * KeeperKernel constructor.
     *
     * @param HttpService $httpService
     */
    public function __construct(HttpService $httpService)
    {
        $this->httpService = $httpService;
    }

    public function onRequest(): Closure
    {
        return function (SwooleHttpRequest $request, SwooleHttpResponse $response) {
            $this->httpService->process($request, $response);
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
            $this->httpService->init($server, $workerId);
        };
    }

    public function onWorkerStop(): Closure
    {
        return function (SwooleHttpServer $server, $workerId) {
            $this->httpService->destroy($server, $workerId);
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

}