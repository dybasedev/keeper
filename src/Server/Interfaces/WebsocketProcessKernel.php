<?php
/**
 * WebsocketProcessKernel.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Keeper\Server\Interfaces;


use Closure;

interface WebsocketProcessKernel extends HttpServerProcessKernel
{
    public function customHandShake(): bool;
    public function onMessage(): Closure;
    public function onHandShake(): Closure;
    public function onOpen(): Closure;
    public function onClose(): Closure;
}