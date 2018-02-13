<?php
/**
 * HttpServerProcessKernel.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Keeper\Server\Interfaces;


use Closure;

interface HttpServerProcessKernel extends ServerProcessKernel
{
    const WORKER_PROCESS  = 3;
    const MASTER_PROCESS  = 1;
    const MANAGER_PROCESS = 2;

    public function onRequest(): Closure;
}