<?php
/**
 * Manager.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Keeper\Http\Session;

use Dybasedev\Keeper\Http\Interfaces\SessionDriver;

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

    protected function getCurrentSessionId()
    {
        if ($this->currentSessionId) {
            if ($this->driver->has($this->currentSessionId)) {
                return $this->currentSessionId;
            }
        }

        return $this->currentSessionId = null;
    }

    protected function setCurrentSessionId($sessionId)
    {
        $this->currentSessionId = $sessionId;
    }

    public function openSession($sessionId)
    {
        if ($this->getCurrentSessionId()) {
            $this->closeSession();
            return false;
        }

        $this->setCurrentSessionId($sessionId);
        return true;
    }

    public function closeSession()
    {
        $this->currentSessionId = null;
    }
}