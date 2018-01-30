<?php
/**
 * AbstractServer.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Keeper\Server;

use Dybasedev\Keeper\Server\Interfaces\ServerProcessKernel;
use Swoole\Server as SwooleServer;
use Swoole\Http\Server as SwooleHttpServer;
use Swoole\WebSocket\Server as SwooleWebsocketServer;

abstract class AbstractServer
{
    protected $host = '0.0.0.0';

    protected $port = 11780;

    /**
     * @var SwooleServer|SwooleHttpServer|SwooleWebsocketServer
     */
    protected $serverInstance;

    /**
     * @var ServerProcessKernel
     */
    protected $processKernel;

    /**
     * @return SwooleServer|SwooleHttpServer|SwooleWebsocketServer
     */
    abstract public function createSwooleServerInstance();

    /**
     * @param SwooleServer|SwooleHttpServer|SwooleWebsocketServer $server
     *
     * @return $this
     */
    abstract public function bindSwooleServerEvents($server);

    /**
     * Start server
     *
     * @return void
     */
    public function start()
    {
        // Create server instance
        $this->serverInstance = $this->createSwooleServerInstance();

        // Bind server events
        $this->bindSwooleServerEvents($this->serverInstance);

        // Start
        $this->serverInstance->start();
    }
}