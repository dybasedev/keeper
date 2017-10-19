<?php
/**
 * Manager.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Keeper\Http\Session;


use Dybasedev\Keeper\Http\Interfaces\SessionDriver;
use Illuminate\Contracts\Container\Container;
use Illuminate\Http\Request;

class Manager
{
    /**
     * @var array
     */
    protected $config;

    /**
     * @var SessionDriver
     */
    protected $driver;

    /**
     * @var Container
     */
    protected $container;

    /**
     * Manager constructor.
     *
     * @param array         $config
     * @param SessionDriver $driver
     * @param Container     $container
     */
    public function __construct(array $config, SessionDriver $driver, Container $container)
    {
        $this->config = $config;
        $this->driver = $driver;
        $this->container = $container;
    }

    public function getSessionData(Request $request = null)
    {
        if (is_null($request)) {
            $request = $this->container['request'];
        }
        
        return $this->driver->find($request->cookie($this->config['session.cookie']));
    }

    public function setSessionData($data, Request $request = null)
    {
        if (is_null($request)) {
            $request = $this->container['request'];
        }

        $this->driver->store($request->cookie($this->config['session.cookie']), $data, $this->config['session.expire']);
    }
}