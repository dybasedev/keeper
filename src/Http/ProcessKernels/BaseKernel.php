<?php
/**
 * BaseKernel.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Keeper\Http\ProcessKernels;

use Closure;
use Dybasedev\Keeper\Server\Interfaces\HttpServerProcessKernel;

class BaseKernel implements HttpServerProcessKernel
{
    protected $lifecycle;

    public function onRequest(): Closure
    {
        return function () {

        };
    }

    public function onStart(): Closure
    {
        return function () {

        };
    }

    public function onShutdown(): Closure
    {
        return function () {

        };
    }

    public function onWorkerStart(): Closure
    {
        return function () {

        };
    }

    public function onWorkerStop(): Closure
    {
        return function () {

        };
    }

    public function onWorkerError(): Closure
    {
        return function () {

        };
    }

    public function onManagerStart(): Closure
    {
        return function () {

        };
    }

    public function onManagerStop(): Closure
    {
        return function () {

        };
    }

}