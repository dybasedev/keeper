<?php
/**
 * Connection.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Keeper\Database\Memory\Redis;

use Redis;
use Dybasedev\Keeper\Database\Connection as BaseConnection;

/**
 * Redis connection
 *
 * @package Dybasedev\Keeper\Database\Memory\Redis
 */
abstract class Connection extends BaseConnection
{
    /**
     * @var Redis
     */
    protected $redisInstance;

    abstract protected function createDriverInstance();

    public function connect()
    {
        if (!$this->redisInstance) {
            $this->redisInstance = $this->createDriverInstance();
        }
    }

    public function reconnect()
    {
        $this->disconnect();
        $this->connect();
    }

    public function disconnect()
    {
        $this->redisInstance = null;
    }

    public function __call($method, array $parameters)
    {
        return $this->redisInstance->{$method}(...$parameters);
    }
}