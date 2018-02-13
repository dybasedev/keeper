<?php
/**
 * HttpServer.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Keeper\Server;

use Dybasedev\Keeper\Server\Interfaces\HttpServerProcessKernel;
use Swoole\Http\Server as SwooleHttpServer;
use Swoole\Server as SwooleServer;
use Swoole\WebSocket\Server as SwooleWebsocketServer;

/**
 * HTTP Service provider server
 *
 * @property HttpServerProcessKernel $processKernel
 *
 * @package Dybasedev\Keeper\Server
 */
class HttpServer extends AbstractServer
{
    protected $ssl = false;

    /**
     * @param bool $ssl
     *
     * @return $this
     */
    public function ssl(bool $ssl = true)
    {
        $this->ssl = $ssl;

        return $this;
    }

    /**
     * @return SwooleServer|SwooleHttpServer|SwooleWebsocketServer
     */
    public function createSwooleServerInstance()
    {
        if ($this->ssl) {
            $instance = new SwooleHttpServer($this->host, $this->port, SWOOLE_PROCESS, SWOOLE_SOCK_TCP | SWOOLE_SSL);
        } else {
            $instance = new SwooleHttpServer($this->host, $this->port);
        }

        if ($this->settings) {
            $instance->set($this->settings);
        }

        return $instance;
    }

    /**
     * @param SwooleServer|SwooleHttpServer|SwooleWebsocketServer $server
     *
     * @return $this
     */
    public function bindSwooleServerEvents($server)
    {
        $server->on('start', $this->processKernel->onStart());
        $server->on('shutdown', $this->processKernel->onShutdown());
        $server->on('workerStart', $this->processKernel->onWorkerStart());
        $server->on('workerStop', $this->processKernel->onWorkerStop());
        $server->on('workerError', $this->processKernel->onWorkerError());
        $server->on('managerStart', $this->processKernel->onManagerStart());
        $server->on('managerStop', $this->processKernel->onManagerStop());

        $this->bindRequestHandler($server);

        return $this;
    }

    /**
     * @param SwooleServer|SwooleHttpServer|SwooleWebsocketServer $server
     */
    protected function bindRequestHandler($server)
    {
        $server->on('request', $this->processKernel->onRequest());
    }
}