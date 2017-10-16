<?php
/**
 * Server.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Keeper\Http;


use Dybasedev\Keeper\Server\Server as AbstractServer;
use Swoole\Http\Server as SwooleHttpServer;

class Server extends AbstractServer
{
    /**
     * @return SwooleHttpServer
     */
    protected function makeSwooleInstance()
    {
        return new SwooleHttpServer($this->host, $this->port);
    }
}