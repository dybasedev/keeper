<?php
/**
 * ConnectionManager.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Keeper\Database\Memory\Redis;

use Dybasedev\Keeper\Database\ConnectionManager as BaseConnectionManager;
use RuntimeException;

class ConnectionManager extends BaseConnectionManager
{
    public function createConnection($name)
    {
        switch ($this->config['connections'][$name]['driver']) {
            case 'phpredis':
                return new PhpRedisConnection($this->config['connections'][$name]);
            default:
                throw new RuntimeException();
        }
    }

}