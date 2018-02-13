<?php
/**
 * WebsocketServer.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Keeper\Server;

use Swoole\Http\Server as SwooleHttpServer;
use Swoole\Server as SwooleServer;
use Swoole\WebSocket\Server as SwooleWebsocketServer;
use Dybasedev\Keeper\Server\Interfaces\WebsocketProcessKernel;

/**
 * HTTP & Websocket Service provider server
 *
 * @property WebsocketProcessKernel $processKernel
 *
 * @package Dybasedev\Keeper\Server
 */
class WebsocketServer extends HttpServer
{
    /**
     * @var bool
     */
    protected $enableHttpRequestProcess = false;

    /**
     * @param bool $enable
     *
     * @return $this
     */
    public function httpRequestProcess($enable = true)
    {
        $this->enableHttpRequestProcess = $enable;

        return $this;
    }

    /**
     * @return SwooleServer|SwooleHttpServer|SwooleWebsocketServer
     */
    public function createSwooleServerInstance()
    {
        if ($this->ssl) {
            $instance = new SwooleWebsocketServer($this->host, $this->port, SWOOLE_PROCESS, SWOOLE_SOCK_TCP | SWOOLE_SSL);
        } else {
            $instance = new SwooleWebsocketServer($this->host, $this->port);
        }

        if ($this->settings) {
            $instance->set($this->settings);
        }

        return $instance;
    }

    /**
     * @param SwooleServer|SwooleHttpServer|SwooleWebsocketServer $server
     */
    protected function bindRequestHandler($server)
    {
        if ($this->enableHttpRequestProcess) {
            $server->on('request', $this->processKernel->onRequest());
        }
    }

    /**
     * @param SwooleHttpServer|SwooleServer|SwooleWebsocketServer $server
     *
     * @return HttpServer|WebsocketServer
     */
    public function bindSwooleServerEvents($server)
    {
        $server->on('message', $this->processKernel->onMessage());

        if ($this->processKernel->customHandShake()) {
            $server->on('handshake', $this->processKernel->onHandShake());
        } else {
            $server->on('open', $this->processKernel->onOpen());
        }

        return parent::bindSwooleServerEvents($server);
    }


}