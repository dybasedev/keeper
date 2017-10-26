<?php
/**
 * RedisManager.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Keeper\Data\Redis;

use Redis;
use RuntimeException;

class RedisManager
{
    /**
     * @var array
     */
    protected $options;

    /**
     * @var Redis[]
     */
    protected $connections;

    /**
     * @var int
     */
    protected $defaultDatabase = 0;

    /**
     * RedisManager constructor.
     *
     * @param array $options
     */
    public function __construct(array $options)
    {
        $this->options = $options;
    }

    /**
     * @param int $database
     *
     * @return $this
     */
    public function setDefaultDatabase(int $database)
    {
        $this->defaultDatabase = $database;

        return $this;
    }

    /**
     * @param int $database
     *
     * @return Redis
     */
    public function select(int $database)
    {
        if (isset($this->connections[$database])) {
            return $this->connections[$database];
        }

        return $this->connections[$database] = $this->createConnect($database);
    }

    /**
     * @param int $database
     *
     * @return Redis
     */
    public function createConnect(int $database)
    {
        $redisConnection = new Redis();
        if (!$redisConnection->connect($this->options['host'] ?? '127.0.0.1', $this->options['port'] ?? 6379)) {
            throw new RuntimeException('Redis connect failed.');
        }

        if (!empty($this->options['password'])) {
            $redisConnection->auth($this->options['password']);
        }

        if (!$redisConnection->select($database)) {
            throw new RuntimeException("Cannot select index[{$database}] on the redis server.");
        }

        return $redisConnection;
    }

    /**
     * @param int|null $database
     *
     * @return void
     */
    public function freeConnection(int $database = null)
    {
        if (is_null($database)) {
            if ($this->connections) {
                foreach ($this->connections as $connection) {
                    $connection->close();
                }
            }

            unset($this->connections);
        } elseif (isset($this->connections[$database])) {
            $this->connections[$database]->close();
            unset($this->connections[$database]);
        }
    }

    public function __call($method, $arguments)
    {
        return $this->select($this->defaultDatabase)->$method(...$arguments);
    }
}