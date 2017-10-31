<?php
/**
 * ConnectionManager.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Keeper\Data\SQLDatabase;


use Closure;

class ConnectionManager
{
    /**
     * @var array|Connection[]
     */
    protected $connections = [];

    protected $connectionCreator = [];

    /**
     * @var array
     */
    protected $config;

    /**
     * ConnectionManager constructor.
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function registerConnectionCreator($connection, Closure $callback)
    {
        $this->connectionCreator[$connection] = $callback;

        return $this;
    }

    /**
     * @param string|null $connection
     *
     * @return Connection
     */
    public function getConnection(string $connection = null)
    {
        if (is_null($connection)) {
            $connection = $this->getDefaultConnection();
        }

        if (!isset($this->connections[$connection])) {
            $this->connections[$connection]
                = ($this->connectionCreator[$connection])($this->config['connections'][$connection]);
        }

        return $this->connections[$connection];
    }

    public function getDefaultConnection()
    {
        return $this->config['default'];
    }

    public function __call($name, ...$arguments)
    {
        return $this->getConnection()->{$name}(...$arguments);
    }


}