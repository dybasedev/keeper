<?php
/**
 * RouteRegister.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Keeper\Routing;

abstract class RouteRegister
{
    abstract public function register(RouteCollector $collector);
}