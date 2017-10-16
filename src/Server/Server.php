<?php
/**
 * Server.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Keeper\Server;

use Swoole\Http\Server as SwooleHttpServer;
use Swoole\Server as SwooleServer;

abstract class Server
{
    /**
     * @var string
     */
    protected $host;

    /**
     * @var int
     */
    protected $port;

    /**
     * @var array
     */
    protected $options = [];

    /**
     * @var SwooleServer|SwooleHttpServer
     */
    protected $instance;

    /**
     * Server constructor.
     *
     * @param string $host
     * @param int    $port
     */
    public function __construct($host, $port)
    {
        $this->host = $host;
        $this->port = $port;
    }

    /**
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @param array $options
     *
     * @return Server
     */
    public function setOptions(array $options): Server
    {
        $this->options = $options;

        return $this;
    }

    /**
     * 构造 Swoole Server 实例
     *
     * @return SwooleServer|SwooleHttpServer
     */
    abstract protected function makeSwooleInstance();

    /**
     * @return boolean
     */
    public function start()
    {
        $this->instance = $this->makeSwooleInstance();
        $this->instance->set($this->options);
        return $this->instance->start();
    }
}