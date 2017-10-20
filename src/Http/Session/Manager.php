<?php
/**
 * Manager.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Keeper\Http\Session;


use Closure;
use Dybasedev\Keeper\Http\Interfaces\SessionDriver;
use Illuminate\Contracts\Container\Container;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Monolog\Logger;
use SplObjectStorage;

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
     * @var
     */
    protected $currentSessionId;

    /**
     * Manager constructor.
     *
     * @param array         $config
     * @param SessionDriver $driver
     */
    public function __construct(array $config, SessionDriver $driver)
    {
        $this->config = $config;
        $this->driver = $driver;
    }

    public function get()
    {
        if ($this->has()) {
            return $this->driver->find($this->currentSessionId);
        }

        return null;
    }

    public function has()
    {
        return $this->driver->has($this->currentSessionId);
    }

    public function set($data)
    {
        $this->driver->store($this->currentSessionId, $data, $this->config['lifetime']);
    }

    public function openSession($sessionId)
    {
        if ($this->currentSessionId) {
            $this->currentSessionId = null;
            return false;
        }

        $this->currentSessionId = $sessionId;
        return true;
    }

    public function closeSession()
    {
        $this->currentSessionId = null;
    }
}