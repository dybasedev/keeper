<?php
/**
 * ExceptionHandler.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Keeper\Http\Interfaces;

use Dybasedev\Keeper\Http\Request;
use Throwable;

interface ExceptionHandler
{
    /**
     * @param \Throwable                     $throwable
     * @param \Dybasedev\Keeper\Http\Request $request
     *
     * @return mixed
     */
    public function handle(Throwable $throwable, Request $request);
}