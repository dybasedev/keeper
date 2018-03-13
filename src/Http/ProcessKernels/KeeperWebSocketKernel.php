<?php
/**
 * KeeperWebSocketKernel.php
 *
 * @copyright Chongyi <chongyi@xopns.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Keeper\Http\ProcessKernels;


use Closure;
use Dybasedev\Keeper\Http\Interfaces\WebSocketService;
use Dybasedev\Keeper\Server\Interfaces\WebsocketProcessKernel;
use Swoole\Http\Request;
use Swoole\WebSocket\Frame;
use Swoole\WebSocket\Server;

/**
 * Class KeeperWebSocketKernel
 *
 * @property WebSocketService $httpService
 *
 * @package Dybasedev\Keeper\Http\ProcessKernels
 */
class KeeperWebSocketKernel extends KeeperKernel implements WebsocketProcessKernel
{
    /**
     * @var Closure
     */
    protected $customHandShakeProcessor = null;

    /**
     * KeeperWebSocketKernel constructor.
     *
     * @param WebSocketService $httpService
     */
    public function __construct(WebSocketService $httpService)
    {
        parent::__construct($httpService);
    }

    public function customHandShake(): bool
    {
        return !is_null($this->customHandShakeProcessor);
    }

    public function onMessage(): Closure
    {
        return function (Server $server, Frame $frame) {
            $this->httpService->received($server, $frame);
        };
    }

    public function onClose(): Closure
    {
        return function (Server $server, int $fd) {
            $this->httpService->close($server, $fd);
        };
    }

    public function onHandShake(): Closure
    {
        if ($this->customHandShakeProcessor) {
            return $this->customHandShakeProcessor;
        }

        return null;
    }

    public function onOpen(): Closure
    {
        return function (Server $server, Request $request) {
            $this->httpService->open($server, $request);
        };
    }

}