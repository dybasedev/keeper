<?php
/**
 * ConnectionManager.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Keeper\Database\SQL;


use Dybasedev\Keeper\Database\SQL\Connections\MySQLConnection;
use RuntimeException;

class ConnectionManager
{
    /**
     * @var array
     */
    protected $config;

    /**
     * @var array
     */
    protected $connections;

    /**
     * ConnectionManager constructor.
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function connection($name = null)
    {
        if (isset($this->connections[$name])) {
            return $this->connections[$name];
        }

        if (is_null($name)) {
            $name = $this->getDefaultConnection();
        }

        return $this->connections[$name] = $this->createConnection($name);
    }

    public function createConnection($name)
    {
        switch ($this->config['connections'][$name]['driver']) {
            case 'mysql':
                return new MySQLConnection($this->config['connections'][$name]);
            default:
                throw new RuntimeException();
        }
    }

    public function getDefaultConnection()
    {
        return $this->config['default'];
    }
}