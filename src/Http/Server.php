<?php
/**
 * Server.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Keeper\Http;


use Dybasedev\Keeper\Http\Interfaces\ProcessKernel;
use Dybasedev\Keeper\Server\Server as AbstractServer;
use RuntimeException;
use Swoole\Http\Server as SwooleHttpServer;

class Server extends AbstractServer
{
    /**
     * @var ProcessKernel
     */
    protected $handler;

    /**
     * @param ProcessKernel $handler
     *
     * @return Server
     */
    public function setHandler(ProcessKernel $handler)
    {
        $this->handler = $handler;

        return $this;
    }

    /**
     * @return SwooleHttpServer
     */
    protected function makeSwooleInstance()
    {
        $server = new SwooleHttpServer($this->host, $this->port);

        if (!$this->handler) {
            throw new RuntimeException('Unknown process kernel.');
        }

        $server->on('start', $this->onServerStart());
        $server->on('managerStart', $this->onManagerStart());
        $server->on('workerStart', $this->onWorkerStart());
        $server->on('request', [$this->handler, 'process']);
        $server->on('workerStop', [$this->handler, 'destroy']);

        return $server;
    }

    protected function onWorkerStart()
    {
        return function (SwooleHttpServer $server, int $workerId) {
            cli_set_process_title($this->handler->getProcessName() . ':worker#' . (string)$workerId);
            $this->handler->init($server, $workerId);
        };
    }

    protected function onServerStart()
    {
        return function () {
            cli_set_process_title($this->handler->getProcessName() . ':master');
        };
    }

    protected function onManagerStart()
    {
        return function () {
            cli_set_process_title($this->handler->getProcessName() . ':manager');
        };
    }
}