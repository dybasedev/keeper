<?php
/**
 * RouteCollector.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Keeper\Routing;


use Closure;
use FastRoute\RouteCollector as FastRouteCollector;
use Illuminate\Contracts\Container\Container;

class RouteCollector
{
    /**
     * @var Container
     */
    protected $container;

    /**
     * @var array
     */
    protected $groupStack = [];

    /**
     * @var array
     */
    protected $currentGroupAttribute;

    /**
     * @var FastRouteCollector
     */
    protected $routeCollector;

    /**
     * RouteCollector constructor.
     *
     * @param FastRouteCollector $routeCollector
     */
    public function __construct(FastRouteCollector $routeCollector)
    {
        $this->routeCollector = $routeCollector;
    }


    public function addRoute($method, $uri, $action)
    {
        $middlewares = [];
        $prefix      = '';

        if (count($this->groupStack)) {
            if ($this->currentGroupAttribute) {
                $middlewares = $this->currentGroupAttribute['middlewares'];
                $prefix      = $this->currentGroupAttribute['prefix'];
            } else {
                foreach ($this->groupStack as $item) {
                    $middlewares = array_merge($middlewares, $item['middlewares'] ?? []);
                    $prefix      .= isset($item['prefix']) ? '/' . trim($item['prefix'], '/') : '';
                }

                $this->currentGroupAttribute = [
                    'middlewares' => $middlewares,
                    'prefix'      => $prefix,
                ];
            }
        }

        if ($middlewares) {
            foreach ($middlewares as $middleware) {
                if (!$this->container->bound($middleware)) {
                    $this->container->instance($middleware, new $middleware($this->container));
                }
            }
        }

        $this->routeCollector->addRoute($method, $prefix . '/' . trim($uri, '/'), [
            'middlewares' => $middlewares,
            'action'      => $action,
        ]);
    }

    /**
     * @param Container $container
     *
     * @return RouteCollector
     */
    public function setContainer(Container $container): RouteCollector
    {
        $this->container = $container;

        return $this;
    }

    public function get($uri, $action)
    {
        $this->addRoute('GET', $uri, $action);
    }

    public function post($uri, $action)
    {
        $this->addRoute('POST', $uri, $action);
    }

    public function put($uri, $action)
    {
        $this->addRoute('PUT', $uri, $action);
    }

    public function delete($uri, $action)
    {
        $this->addRoute('DELETE', $uri, $action);
    }

    public function head($uri, $action)
    {
        $this->addRoute('HEAD', $uri, $action);
    }

    public function group($attributes, Closure $callback)
    {
        $this->groupStack[] = $attributes;

        ($callback)($this);

        array_pop($this->groupStack);
    }

    public function getData()
    {
        return $this->routeCollector->getData();
    }
}