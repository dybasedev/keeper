<?php
/**
 * WebSocketService.php
 *
 * @copyright Chongyi <chongyi@xopns.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Keeper\Http\Interfaces;


use Swoole\Http\Request;
use Swoole\WebSocket\Frame;
use Swoole\WebSocket\Server;

/**
 * Interface WebSocketService
 *
 * @package Dybasedev\Keeper\Http\Interfaces
 */
interface WebSocketService extends HttpService
{
    /**
     * @param Server  $server
     * @param Request $request
     *
     * @return void
     */
    public function open(Server $server, Request $request);

    /**
     * @param Server $server
     * @param Frame  $frame
     *
     * @return void
     */
    public function received(Server $server, Frame $frame);

    /**
     * @param Server $server
     * @param int    $fd
     *
     * @return void
     */
    public function close(Server $server, int $fd);
}