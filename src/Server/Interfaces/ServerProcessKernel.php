<?php
/**
 * CommonProcessKernel.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Keeper\Server\Interfaces;

use Closure;

/**
 * Server Process Kernel
 *
 * @package Dybasedev\Keeper\Server\Interfaces
 */
interface ServerProcessKernel
{
    public function onStart(): Closure;
    public function onShutdown(): Closure;
    public function onWorkerStart(): Closure;
    public function onWorkerStop(): Closure;
    public function onWorkerError(): Closure;
    public function onManagerStart(): Closure;
    public function onManagerStop(): Closure;
}