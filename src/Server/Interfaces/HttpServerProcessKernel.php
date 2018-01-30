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
    public function onRequest(): Closure;
}