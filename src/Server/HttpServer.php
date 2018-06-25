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

    public function getBinders(): array
    {
        return [
            'start'        => $this->processKernel->onStart(),
            'shutdown'     => $this->processKernel->onShutdown(),
            'workerStart'  => $this->processKernel->onWorkerStart(),
            'workerStop'   => $this->processKernel->onManagerStop(),
            'workerError'  => $this->processKernel->onWorkerError(),
            'managerStart' => $this->processKernel->onManagerStart(),
            'managerStop'  => $this->processKernel->onManagerStop(),
            'request'      => $this->processKernel->onRequest(),
        ];
    }
}