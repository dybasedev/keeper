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
     * @var array
     */
    protected $processNames = [];

    /**
     * KeeperKernel constructor.
     *
     * @param HttpService $httpService
     */
    public function __construct(HttpService $httpService)
    {
        $this->httpService = $httpService;
    }

    /**
     * @param $process
     * @param $name
     *
     * @return $this
     */
    public function setProcessName($process, $name)
    {
        $this->processNames[$process] = $name;

        return $this;
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
            cli_set_process_title($this->processNames[self::MASTER_PROCESS] ?? 'keeper:master');
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
            cli_set_process_title($this->processNames[self::WORKER_PROCESS] ?? 'keeper:worker');
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
            cli_set_process_title($this->processNames[self::MANAGER_PROCESS] ?? 'keeper:manager');
        };
    }

    public function onManagerStop(): Closure
    {
        return function () {

        };
    }

}