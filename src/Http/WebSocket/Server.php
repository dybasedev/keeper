<?php
/**
 * Server.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Keeper\Http\WebSocket;

use Dybasedev\Keeper\Http\Server as HttpServer;
use Swoole\WebSocket\Server as SwooleWebSocketServer;

class Server extends HttpServer
{
    /**
     * @return SwooleWebSocketServer
     */
    protected function makeSwooleInstance()
    {
        return new SwooleWebSocketServer($this->host, $this->port);
    }

}