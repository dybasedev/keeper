<?php
/**
 * Router.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Keeper\Routing;

use Dybasedev\Keeper\Routing\Interfaces\Router as RouterInterface;
use FastRoute\DataGenerator\GroupCountBased;
use FastRoute\Dispatcher;
use FastRoute\RouteCollector as FastRouteCollector;
use FastRoute\RouteParser\Std;
use Illuminate\Contracts\Container\Container;

class Router implements RouterInterface
{
    /**
     * @var array
     */
    protected $registers;

    /**
     * @var RouteCollector
     */
    protected $routeCollector;

    /**
     * @var Dispatcher
     */
    protected $dispatcher;

    /**
     * @var Container
     */
    protected $container;

    /**
     * Router constructor.
     *
     * @param array $registers
     */
    public function __construct(array $registers)
    {
        $this->registers = $registers;

        $this->routeCollector = new RouteCollector(new FastRouteCollector(new Std(), new GroupCountBased()));
    }

    /**
     * @param Container $container
     *
     * @return Router
     */
    public function setContainer(Container $container): Router
    {
        $this->container = $container;

        return $this;
    }

    /**
     * Load routes from route register
     *
     * @return array
     */
    public function load()
    {
        foreach ($this->registers as $register) {
            /** @var RouteRegister $registerInstance */
            $registerInstance = new $register;
            $registerInstance->register($this->routeCollector);
        }

        return $this->routeCollector->setContainer($this->container)->getData();
    }

    public function mount(array $data = null)
    {
        if (is_null($data)) {
            $data = $this->load();
        }

        $this->dispatcher = new Dispatcher\GroupCountBased($data);

        return $this;
    }

    /**
     * @param string $method
     * @param string $uri
     *
     * @return array
     */
    public function dispatch($method, $uri)
    {
        return $this->dispatcher->dispatch($method, $uri);
    }
}