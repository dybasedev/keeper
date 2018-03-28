<?php
/**
 * ConnectionManager.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Keeper\Database\SQL;

use Dybasedev\Keeper\Database\ConnectionManager as BaseConnectionManager;
use Dybasedev\Keeper\Database\Exceptions\DriverNotSupportException;
use Dybasedev\Keeper\Database\SQL\Connections\MySQLConnection;

class ConnectionManager extends BaseConnectionManager
{
    public function createConnection($name)
    {
        switch ($this->config['connections'][$name]['driver']) {
            case 'mysql':
                return new MySQLConnection($this->config['connections'][$name]);
            default:
                if ($this->container && $this->container->bound($abstract = 'db.sql.driver:' . $name)) {
                    return $this->container->make($abstract);
                }

                throw new DriverNotSupportException($name);
        }
    }
}