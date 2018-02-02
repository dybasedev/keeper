<?php
/**
 * Middleware.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Keeper\Routing;

use Closure;
use Illuminate\Contracts\Container\Container;

abstract class Middleware
{
    /**
     * @var Container
     */
    protected $container;

    /**
     * Middleware constructor.
     *
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    abstract public function handle($request, Closure $next);
}