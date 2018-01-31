<?php
/**
 * Router.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Keeper\Routing;


use FastRoute\DataGenerator\GroupCountBased;
use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use FastRoute\RouteParser\Std;

class Router
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
     * Router constructor.
     *
     * @param array $registers
     */
    public function __construct(array $registers)
    {
        $this->registers = $registers;

        $this->routeCollector = new RouteCollector(new Std(), new GroupCountBased());
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

        return $this->routeCollector->getData();
    }

    public function mount(array $data = null)
    {
        if (is_null($data)) {
            $data = $this->load();
        }

        $this->dispatcher = new Dispatcher\GroupCountBased($data);

        return $this;
    }

    public function dispatch($method, $uri)
    {
        return $this->dispatcher->dispatch($method, $uri);
    }
}