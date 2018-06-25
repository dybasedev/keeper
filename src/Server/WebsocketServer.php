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

    public function getBinders(): array
    {
        $binders = parent::getBinders();

        if (!$this->enableHttpRequestProcess) {
            unset($binders['request']);
        }

        $binders['message'] = $this->processKernel->onMessage();
        $binders['close'] = $this->processKernel->onClose();

        if ($this->processKernel->customHandShake()) {
            $binders['handshake'] = $this->processKernel->onHandShake();
        } else {
            $binders['open'] = $this->processKernel->onOpen();
        }

        return $binders;
    }


}