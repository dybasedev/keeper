<?php
/**
 * ExceptionHandler.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Keeper\Http\Interfaces;

use Throwable;

interface ExceptionHandler
{
    /**
     * @param Throwable $throwable
     *
     * @return mixed
     */
    public function handle(Throwable $throwable);
}