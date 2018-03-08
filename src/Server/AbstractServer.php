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

/**
 * Base server class
 *
 * @package Dybasedev\Keeper\Server
 */
abstract class AbstractServer
{
    /**
     * @var string
     */
    protected $host = '0.0.0.0';

    /**
     * @var int
     */
    protected $port = 11780;

    /**
     * @var array
     */
    protected $settings;

    /**
     * @var SwooleServer|SwooleHttpServer|SwooleWebsocketServer
     */
    protected $serverInstance;

    /**
     * @var ServerProcessKernel
     */
    protected $processKernel;

    /**
     * @param array $setting
     *
     * @return $this
     */
    public function setting(array $setting)
    {
        $this->settings = $setting;

        return $this;
    }

    /**
     * Set server host
     *
     * @param string $host
     *
     * @return $this
     */
    public function host(string $host)
    {
        $this->host = $host;

        return $this;
    }

    /**
     * Set server port
     *
     * @param int $port
     *
     * @return $this
     */
    public function port(int $port)
    {
        $this->port = $port;

        return $this;
    }

    /**
     * AbstractServer constructor.
     *
     * @param ServerProcessKernel $processKernel
     */
    public function __construct(ServerProcessKernel $processKernel)
    {
        $this->processKernel = $processKernel;
    }

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