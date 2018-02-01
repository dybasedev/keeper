<?php
/**
 * WorkerHookDelegation.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Keeper\Http\Interfaces;


use Closure;

interface WorkerHookDelegation
{
    public function processBegin(Closure $callback);

    public function processEnd(Closure $callback);
}